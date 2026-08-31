<?php

namespace App\Services\Analytics;

use App\Enums\Plan\TypeEnum;
use App\Models\Currency;
use App\Models\Finance\Subscription;
use App\Models\Gateways;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoogleTagManager
{
    public const SESSION_KEY = 'gtm_events';

    public const CACHE_PREFIX = 'gtm_events:';

    public static function enabled(): bool
    {
        try {
            $id = trim((string) setting('google_container_id', ''));
            if ($id !== '') {
                return true;
            }

            // Queue workers may not have the settings package cache warmed.
            return \Illuminate\Support\Facades\DB::table('app_settings')
                ->where('key', 'google_container_id')
                ->whereNotNull('value')
                ->where('value', '!=', '')
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function queue(string $event, array $data = [], ?int $userId = null): void
    {
        if (! self::enabled()) {
            return;
        }

        $payload = array_filter(
            array_merge(['event' => $event], $data),
            static fn ($value) => $value !== null && $value !== ''
        );

        $userId = $userId ?? auth()->id();

        try {
            // Cache: works across requests when driver is file/redis/database.
            if ($userId) {
                $key = self::CACHE_PREFIX . $userId;
                $queued = Cache::get($key, []);
                $queued[] = $payload;
                Cache::put($key, $queued, now()->addDay());

                // Database fallback: survives CACHE_DRIVER=array and queue workers.
                self::persistToDatabase((int) $userId, $payload);
            }

            // Session: survives page redirects even when cache is array.
            if (session()->isStarted()) {
                $sessionEvents = session(self::SESSION_KEY, []);
                $sessionEvents[] = $payload;
                session([self::SESSION_KEY => $sessionEvents]);
            }
        } catch (\Throwable $e) {
            Log::warning('GTM event queue failed: ' . $e->getMessage(), [
                'event' => $event,
            ]);
        }
    }

    public static function pull(?int $userId = null): array
    {
        $events = [];

        try {
            $userId = $userId ?? auth()->id();

            if ($userId) {
                $events = array_merge($events, Cache::pull(self::CACHE_PREFIX . $userId, []));
                $events = array_merge($events, self::pullFromDatabase((int) $userId));
            }

            if (session()->isStarted()) {
                $events = array_merge($events, session(self::SESSION_KEY, []));
                session()->forget(self::SESSION_KEY);
            }
        } catch (\Throwable $e) {
            Log::warning('GTM event pull failed: ' . $e->getMessage());
        }

        return self::uniqueEvents($events);
    }

    protected static function databaseKey(int $userId): string
    {
        return 'gtm_pending_' . $userId;
    }

    protected static function persistToDatabase(int $userId, array $payload): void
    {
        $key = self::databaseKey($userId);
        $existing = [];

        $value = \Illuminate\Support\Facades\DB::table('app_settings')->where('key', $key)->value('value');
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $existing = $decoded;
            }
        }

        $existing[] = $payload;

        $encoded = json_encode(array_values($existing));

        $updated = \Illuminate\Support\Facades\DB::table('app_settings')
            ->where('key', $key)
            ->update(['value' => $encoded]);

        if (! $updated) {
            \Illuminate\Support\Facades\DB::table('app_settings')->insert([
                'key'   => $key,
                'value' => $encoded,
            ]);
        }
    }

    protected static function pullFromDatabase(int $userId): array
    {
        $key = self::databaseKey($userId);
        $value = \Illuminate\Support\Facades\DB::table('app_settings')->where('key', $key)->value('value');

        \Illuminate\Support\Facades\DB::table('app_settings')->where('key', $key)->delete();

        if (! is_string($value) || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    public static function uniqueEvents(array $events): array
    {
        $unique = [];
        $seen = [];

        foreach ($events as $event) {
            $hash = md5((string) json_encode($event));
            if (isset($seen[$hash])) {
                continue;
            }
            $seen[$hash] = true;
            $unique[] = $event;
        }

        return array_values($unique);
    }

    public static function signupCompleted(User $user): void
    {
        self::queue('signup_completed', self::userPayload($user), $user->id);
    }

    public static function trialStarted(User $user, Plan $plan, ?Subscription $subscription = null): void
    {
        self::queue('trial_started', array_merge(self::userPayload($user), [
            'plan_id'        => $plan->id,
            'plan_name'      => $plan->name,
            'transaction_id' => $subscription?->stripe_id,
            'credits'        => $plan->total_credits,
            'trial_days'     => $plan->trial_days,
        ]), $user->id);
    }

    public static function creditsUsed(User $user, int $credits, array $extra = []): void
    {
        $item = (string) ($extra['item'] ?? 'Image');

        // Event name has no spaces — GTM left timeline shows this exact string.
        self::queue('creditsused', array_merge(self::userPayload($user), [
            'credits'      => $credits,
            'summary'      => 'creditsused',
            'item'         => $item,
            'source_label' => $extra['source_label'] ?? ('Credits used for ' . $item),
            'action'       => $extra['action'] ?? ('Credits used for ' . $item),
        ], $extra), $user->id);
    }

    /**
     * Fire creditsused only when the user's balance is fully used up (<= 0).
     * Reads balance from DB so in-memory Auth user is never stale.
     */
    public static function creditsUsedIfExhausted(User $user, int $credits, array $extra = []): void
    {
        $balance = (int) \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $user->id)
            ->value('total_credit');

        if ($balance > 0) {
            return;
        }

        self::creditsUsed($user, $credits, $extra);
    }

    public static function creditsUsedById(int $userId, int $credits, array $extra = []): void
    {
        $user = User::query()->find($userId);
        if (! $user) {
            return;
        }

        self::creditsUsedIfExhausted($user, $credits, $extra);
    }

    public static function imageGenerated(Model $record, bool $isFirst = false, ?string $modelType = null): void
    {
        $user = self::resolveUserFromRecord($record);
        if (! $user) {
            return;
        }

        $source = self::resolveImageSource($record, $modelType);
        $item = $source['item'] ?? 'Image';
        // e.g. imagegeneratebyphotoshoot / imagegeneratebyvirtualtryon (no spaces)
        $eventName = $source['event_name'] ?? self::imageEventNameFromItem($item);

        $payload = array_merge(self::userPayload($user), [
            'record_id'    => $record->id,
            'source'       => $source['source'],
            'source_label' => $source['source_label'],
            'action'       => $source['source_label'],
            'summary'      => $eventName,
            'item'         => $item,
        ]);

        if ($isFirst) {
            self::queue('first' . $eventName, $payload, $user->id);
        }

        self::queue($eventName, $payload, $user->id);
    }

    public static function videoGenerated(Model $record): void
    {
        $user = self::resolveUserFromRecord($record);
        if (! $user) {
            return;
        }

        $source = self::resolveImageSource($record);
        $isKnown = $source['source'] !== 'unknown';
        $item = $isKnown ? ($source['item'] ?? 'Create Video') : 'Create Video';
        $sourceLabel = $isKnown
            ? $source['source_label']
            : 'Video created from Create Video';
        $eventName = $isKnown
            ? ($source['event_name'] ?? 'videogeneratebycreatevideo')
            : 'videogeneratebycreatevideo';

        self::queue($eventName, array_merge(self::userPayload($user), [
            'record_id'    => $record->id,
            'source'       => $isKnown ? $source['source'] : 'create_video',
            'source_label' => $sourceLabel,
            'action'       => $sourceLabel,
            'summary'      => $eventName,
            'item'         => $item,
        ]), $user->id);
    }

    /**
     * Build GTM event name with no spaces: imagegeneratebyphotoshoot
     */
    public static function imageEventNameFromItem(string $item): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '', $item) ?? '');

        return 'imagegenerateby' . ($slug !== '' ? $slug : 'image');
    }

    /**
     * @return array{source: string, source_label: string, credits_label: string, item: string, event_name: string}
     */
    public static function sourceFromAssetType(string $type): array
    {
        return match ($type) {
            'photoshoot', 'photo-shoot' => [
                'source'        => 'photoshoot',
                'source_label'  => 'Image created from Photoshoot',
                'credits_label' => 'Credits used for Photoshoot',
                'item'          => 'Photoshoot',
                'event_name'    => 'imagegeneratebyphotoshoot',
            ],
            'virtual_try_on', 'tryon' => [
                'source'        => 'virtual_try_on',
                'source_label'  => 'Image created from Virtual Try On',
                'credits_label' => 'Credits used for Virtual Try On',
                'item'          => 'Virtual Try On',
                'event_name'    => 'imagegeneratebyvirtualtryon',
            ],
            'change_model', 'change-model' => [
                'source'        => 'change_model',
                'source_label'  => 'Image created from Change Model',
                'credits_label' => 'Credits used for Change Model',
                'item'          => 'Change Model',
                'event_name'    => 'imagegeneratebychangemodel',
            ],
            'edit_image', 'edit-image', 'fashion-playground' => [
                'source'        => 'edit_image',
                'source_label'  => 'Image created from Fashion PlayGround',
                'credits_label' => 'Credits used for Fashion PlayGround',
                'item'          => 'Fashion PlayGround',
                'event_name'    => 'imagegeneratebyfashionplayground',
            ],
            'create_video', 'create-video' => [
                'source'        => 'create_video',
                'source_label'  => 'Video created from Create Video',
                'credits_label' => 'Credits used for Create Video',
                'item'          => 'Create Video',
                'event_name'    => 'videogeneratebycreatevideo',
            ],
            'pose' => [
                'source'        => 'pose',
                'source_label'  => 'Image created from Pose',
                'credits_label' => 'Credits used for Pose creation',
                'item'          => 'Pose',
                'event_name'    => 'imagegeneratebypose',
            ],
            'background' => [
                'source'        => 'background',
                'source_label'  => 'Image created from Background',
                'credits_label' => 'Credits used for Background creation',
                'item'          => 'Background',
                'event_name'    => 'imagegeneratebybackground',
            ],
            'fashion_model', 'model' => [
                'source'        => 'model',
                'source_label'  => 'Image created from Model',
                'credits_label' => 'Credits used for Model creation',
                'item'          => 'Model',
                'event_name'    => 'imagegeneratebymodel',
            ],
            'wardrobe', 'product' => [
                'source'        => 'product',
                'source_label'  => 'Image created from Product',
                'credits_label' => 'Credits used for Product creation',
                'item'          => 'Product',
                'event_name'    => 'imagegeneratebyproduct',
            ],
            default => [
                'source'        => $type,
                'source_label'  => 'Image created from ' . $type,
                'credits_label' => 'Credits used for ' . $type,
                'item'          => $type,
                'event_name'    => self::imageEventNameFromItem($type),
            ],
        };
    }

    /**
     * @return array{source: string, source_label: string, credits_label: string, item: string, event_name: string}
     */
    protected static function resolveImageSource(Model $record, ?string $modelType = null): array
    {
        // Photoshoot / Virtual Try On / etc. store exact source on the record payload.
        $payload = $record->payload ?? null;
        if (is_string($payload)) {
            $payload = json_decode($payload, true);
        }

        if (is_array($payload)) {
            if (! empty($payload['gtm_source']) && ! empty($payload['gtm_source_label'])) {
                $source = (string) $payload['gtm_source'];
                $fromType = self::sourceFromAssetType($source);
                $item = (string) ($payload['gtm_item'] ?? $fromType['item']);

                return [
                    'source'        => $source,
                    'source_label'  => (string) $payload['gtm_source_label'],
                    'credits_label' => (string) ($payload['gtm_credits_label'] ?? $payload['gtm_source_label']),
                    'item'          => $item,
                    'event_name'    => (string) ($payload['gtm_event_name'] ?? $fromType['event_name'] ?? self::imageEventNameFromItem($item)),
                ];
            }

            if (! empty($payload['slug_suffix'])) {
                return self::sourceFromAssetType((string) $payload['slug_suffix']);
            }
        }

        // Pose / Background / Model / Product jobs pass a specific modelType.
        // Ignore generic "user_openai" so Photoshoot vs Virtual Try On is not overwritten.
        if ($modelType && ! in_array($modelType, ['user_openai', 'image'], true)) {
            return self::sourceFromAssetType($modelType);
        }

        $class = class_basename($record);
        $classMap = [
            'Pose'         => 'pose',
            'Background'   => 'background',
            'FashionModel' => 'fashion_model',
            'Wardrobe'     => 'wardrobe',
        ];
        if (isset($classMap[$class])) {
            return self::sourceFromAssetType($classMap[$class]);
        }

        $slug = (string) ($record->slug ?? '');
        foreach (['photo-shoot', 'tryon', 'edit_image', 'edit-image', 'fashion-playground', 'change-model', 'create-video', 'pose', 'background', 'model', 'product'] as $needle) {
            if (str_contains($slug, $needle)) {
                return self::sourceFromAssetType($needle);
            }
        }

        $title = strtolower((string) ($record->title ?? ''));
        if (str_contains($title, 'photo shoot') || str_contains($title, 'photoshoot')) {
            return self::sourceFromAssetType('photo-shoot');
        }
        if (str_contains($title, 'virtual try') || str_contains($title, 'try-on') || str_contains($title, 'try on')) {
            return self::sourceFromAssetType('tryon');
        }
        if (str_contains($title, 'fashion playground') || str_contains($title, 'edit image')) {
            return self::sourceFromAssetType('edit_image');
        }

        return [
            'source'        => 'unknown',
            'source_label'  => 'Image created',
            'credits_label' => 'Credits used',
            'item'          => 'Image',
            'event_name'    => 'imagegeneratebyimage',
        ];
    }

    /**
     * @return array{source: string, source_label: string, credits_label: string}
     */
    protected static function sourceFromSlugSuffix(string $suffix): array
    {
        return self::sourceFromAssetType($suffix);
    }

    public static function purchaseFromOrder(UserOrder $order): void
    {
        $order->loadMissing('plan', 'user');

        $plan = $order->plan;
        $user = $order->user;
        if (! $plan || ! $user) {
            return;
        }

        $isPrepaid = ($order->type ?? null) === TypeEnum::TOKEN_PACK->value
            || $plan->type === TypeEnum::TOKEN_PACK->value;

        $payload = array_merge(self::userPayload($user), [
            'amount'         => (float) $order->price,
            'currency'       => self::resolveCurrency($order),
            'plan_name'      => $plan->name,
            'plan_id'        => $plan->id,
            'transaction_id' => $order->order_id,
            'payment_type'   => $order->payment_type,
        ]);

        if ($isPrepaid) {
            self::queue('credit_pack_purchased', $payload, $user->id);

            return;
        }

        $priorSuccessCount = UserOrder::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['Success', 'Approved'])
            ->where('id', '!=', $order->id)
            ->where(function ($query) {
                $query->whereNull('type')
                    ->orWhere('type', '!=', TypeEnum::TOKEN_PACK->value);
            })
            ->whereHas('plan', function ($query) {
                $query->where('type', TypeEnum::SUBSCRIPTION->value);
            })
            ->count();

        $event = $priorSuccessCount > 0 ? 'subscription_upgraded' : 'subscription_purchased';
        self::queue($event, $payload, $user->id);
    }

    public static function subscriptionCancelled(Subscription $subscription): void
    {
        // Ignore automatic local trial expiry records.
        if (str_starts_with((string) $subscription->stripe_id, 'TRL-')) {
            return;
        }

        $subscription->loadMissing('plan');
        $user = User::query()->find($subscription->user_id);

        self::subscriptionCancelledForUser(
            $user,
            $subscription->plan,
            $subscription->stripe_id,
            $subscription->plan_id
        );
    }

    public static function subscriptionCancelledForUser(
        ?User $user,
        ?Plan $plan = null,
        ?string $transactionId = null,
        ?int $planId = null
    ): void {
        if (! $user) {
            return;
        }

        self::queue('subscription_cancelled', array_merge(self::userPayload($user), [
            'plan_id'        => $planId ?? $plan?->id,
            'plan_name'      => $plan?->name,
            'transaction_id' => $transactionId,
        ]), $user->id);
    }

    protected static function userPayload(User $user): array
    {
        $name = method_exists($user, 'fullName')
            ? trim((string) $user->fullName())
            : trim(trim((string) $user->name) . ' ' . trim((string) ($user->surname ?? '')));

        return [
            'user_id'    => $user->id,
            'user_name'  => $name !== '' ? $name : null,
            'email'      => $user->email,
        ];
    }

    protected static function resolveUserFromRecord(Model $record): ?User
    {
        $userId = (int) ($record->user_id ?? 0);
        if (! $userId) {
            return null;
        }

        if (isset($record->user) && $record->user instanceof User) {
            return $record->user;
        }

        return User::query()->find($userId);
    }

    protected static function resolveCurrency(UserOrder $order): string
    {
        try {
            $gateway = Gateways::query()
                ->where('code', $order->payment_type)
                ->first();

            if ($gateway?->currency) {
                $currency = Currency::query()->find($gateway->currency);

                if ($currency?->code) {
                    return strtoupper((string) $currency->code);
                }
            }
        } catch (\Throwable $e) {
            // Fall through to default.
        }

        return 'USD';
    }
}
