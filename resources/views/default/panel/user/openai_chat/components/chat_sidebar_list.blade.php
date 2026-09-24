@php
    use App\Enums\AiImageStatusEnum;
    use App\Helpers\Classes\MarketplaceHelper;
    use Illuminate\Support\Facades\DB;

    $disable_actions = $app_is_demo;
    $is_search = $is_search ?? false;
    $showFolders = false;
    $isChatProImage = ($website_url ?? null) === 'chatpro-image' && MarketplaceHelper::isRegistered('ai-chat-pro-image-chat');
    $chatImageThumbs = [];
    $currentUrl = url()->current();
    $currentPath = trim(parse_url($currentUrl, PHP_URL_PATH) ?: '');
    $chatProEnabled = MarketplaceHelper::isRegistered('ai-chat-pro');
    $chatProFoldersEnabled = MarketplaceHelper::isRegistered('ai-chat-pro-folders');
    $website_url = $website_url ?? null;

    if (
        ($chatProEnabled &&
            $chatProFoldersEnabled &&
            (str_starts_with($currentPath, '/chat') || str_starts_with($currentPath, '/dashboard/user/openai/chat/pro/') || !auth()->check())) ||
        ($chatProEnabled && str_starts_with($currentPath, '/dashboard/user/openai/chat/start-new-chat') && $website_url === 'chatpro')
    ) {
        $showFolders = true;
    }

    if ($isChatProImage && !empty($list)) {
        $chatIds = collect($list)->pluck('id')->filter()->values();

        if ($chatIds->isNotEmpty()) {
            // Fetch only the latest completed image row per chat to avoid scanning all historical records.
            $latestImageIdsByChat = DB::table('ai_chat_pro_image as ai')
                ->join('user_openai_chat_messages as msg', 'msg.id', '=', 'ai.message_id')
                ->whereIn('msg.user_openai_chat_id', $chatIds)
                ->where('ai.status', AiImageStatusEnum::COMPLETED->value)
                ->whereNotNull('ai.generated_images')
                ->selectRaw('msg.user_openai_chat_id as chat_id, MAX(ai.id) as latest_ai_id')
                ->groupBy('msg.user_openai_chat_id');

            $latestRecords = DB::table('ai_chat_pro_image as ai')
                ->joinSub($latestImageIdsByChat, 'latest_ai', function ($join) {
                    $join->on('ai.id', '=', 'latest_ai.latest_ai_id');
                })
                ->select('latest_ai.chat_id', 'ai.generated_images')
                ->get();

            foreach ($latestRecords as $record) {
                $chatId = (int) ($record->chat_id ?? 0);
                if ($chatId <= 0) {
                    continue;
                }

                $images = json_decode((string) ($record->generated_images ?? '[]'), true);
                if (!is_array($images)) {
                    continue;
                }

                $firstImage = reset($images);
                if (!is_string($firstImage) || $firstImage === '') {
                    continue;
                }

                $firstImage = trim($firstImage);
                if ($firstImage === '') {
                    continue;
                }

                if (str_starts_with($firstImage, '//')) {
                    $firstImage = request()->getScheme() . ':' . $firstImage;
                } elseif (
                    !str_starts_with($firstImage, 'http://') &&
                    !str_starts_with($firstImage, 'https://') &&
                    !str_starts_with($firstImage, 'data:') &&
                    !str_starts_with($firstImage, 'blob:')
                ) {
                    $thumbnailSource = '/' . ltrim($firstImage, '/');
                    $firstImage = ThumbImage($thumbnailSource, 160, 160);

                    if (
                        !str_starts_with($firstImage, 'http://') &&
                        !str_starts_with($firstImage, 'https://') &&
                        !str_starts_with($firstImage, '//') &&
                        !str_starts_with($firstImage, 'data:') &&
                        !str_starts_with($firstImage, 'blob:')
                    ) {
                        $firstImage = url(ltrim($firstImage, '/'));
                    }
                }

                $chatImageThumbs[$chatId] = $firstImage;
            }
        }
    }
@endphp

<ul class="chat-list-ul flex h-full flex-col overflow-y-auto text-xs">
    @if ($showFolders)
        @includeIf('ai-chat-pro-folders::components.chat_sidebar_folders', ['list' => $list, 'is_search' => $is_search])
    @else
        @foreach ($list as $entry)
            @php
                $thumbnailUrl = $isChatProImage ? $chatImageThumbs[$entry->id] ?? null : null;
            @endphp
            <li
                id="chat_{{ $entry->id }}"
                @class([
                    'chat-list-item shrink-0 group relative border-b overflow-hidden [word-break:break-word] [&.active]:before:absolute [&.active]:before:left-0 [&.active]:before:top-[25%] [&.active]:before:h-[50%] [&.active]:before:w-[3px] [&.active]:before:bg-gradient-to-b [&.active]:before:from-primary [&.active]:before:to-transparent',
                    'pin-mode' => $entry->is_pinned,
                    'active' => isset($chat) && $chat->id == $entry->id,
                ])
            >
                <div
                    class="chat-list-item-trigger flex cursor-pointer gap-3 p-5 text-start text-heading-foreground hover:text-primary group-[&.edit-mode]:pointer-events-none dark:hover:text-heading-foreground"
                    onclick="return openChatAreaContainer({{ $entry->id }}, '{{ $website_url ?? null }}');"
                    @click="mobileSidebarShow = false"
                >
                    @if ($isChatProImage)
                        <div @class([
                            'lqd-chat-item-thumb-wrap relative size-6 shrink-0 overflow-hidden rounded bg-foreground/5',
                            'hidden' => !$thumbnailUrl,
                        ])>
                            @if ($thumbnailUrl)
                                <img
                                    class="lqd-chat-item-thumb size-full object-cover"
                                    src="{{ $thumbnailUrl }}"
                                    alt="{{ __('Generated image preview') }}"
                                    loading="lazy"
                                    onerror="this.closest('.lqd-chat-item-thumb-wrap')?.classList.add('hidden'); this.closest('.chat-list-item-trigger')?.querySelector('.lqd-chat-item-trigger-icons')?.classList.remove('hidden');"
                                >
                            @endif
                        </div>
                        <div @class([
                            'lqd-chat-item-trigger-icons flex flex-col gap-y-2',
                            'hidden' => $thumbnailUrl,
                        ])>
                            <x-tabler-pinned
                                class="lqd-chat-item-trigger-icon-pin hidden size-6 group-[&.pin-mode]:block"
                                stroke-width="1.5"
                            />
                            <x-tabler-message
                                class="lqd-chat-item-trigger-icon-message size-6 shrink-0 group-[&.pin-mode]:hidden"
                                stroke-width="1.5"
                            />
                        </div>
                    @else
                        <div class="lqd-chat-item-trigger-icons flex flex-col gap-y-2">
                            <x-tabler-pinned
                                class="lqd-chat-item-trigger-icon-pin hidden size-6 group-[&.pin-mode]:block"
                                stroke-width="1.5"
                            />
                            <x-tabler-message
                                class="lqd-chat-item-trigger-icon-message size-6 shrink-0 group-[&.pin-mode]:hidden"
                                stroke-width="1.5"
                            />
                        </div>
                    @endif
                    <span class="lqd-chat-item-trigger-info flex flex-col">
                        <span class="chat-item-title text-xs font-medium group-[&.edit-mode]:pointer-events-auto">
                            {{ __($entry->title) }}
                        </span>
                        <span class="chat-item-date text-3xs opacity-40">{{ $entry->updated_at->diffForHumans() }}</span>
                        @if ($entry->reference_url != '')
                            <a
                                class="flex underline opacity-90"
                                target="_blank"
                                title="{{ $entry->reference_url }}"
                                onclick="event.stopPropagation();"
                                href="{{ $entry->reference_url }}"
                            >
                                {{ __($entry->doc_name) }}
                            </a>
                        @endif
                        @if ($entry->website_url != '')
                            <a
                                class="flex underline opacity-90"
                                target="_blank"
                                title="{{ $entry->website_url }}"
                                onclick="event.stopPropagation();"
                                href="{{ $entry->website_url }}"
                            >
                                {{ __($entry->website_url) }}
                            </a>
                        @endif
                    </span>
                </div>
                <span
                    class="chat-list-item-actions absolute end-4 top-1/2 flex -translate-y-1/2 gap-1 opacity-0 transition-opacity before:pointer-events-none before:absolute before:-inset-9 before:z-0 before:bg-[radial-gradient(closest-side,hsl(var(--background))_50%,transparent)] before:opacity-0 before:transition-all focus-within:opacity-100 focus-within:before:opacity-100 group-hover:opacity-100 group-hover:before:opacity-90 group-[&.edit-mode]:opacity-100 max-md:opacity-100"
                >
                    <button
                        @class([
                            'chat-item-pin' => !$disable_actions,
                            'flex size-7 items-center relative z-1 justify-center rounded-button border bg-background transition-all dark:bg-primary dark:text-primary-foreground dark:border-primary hover:scale-110  group-[&.edit-mode]:hidden',
                        ])
                        @if ($disable_actions) onclick="return toastr.info('{{ __('This feature is disabled in Demo version.') }}')" @endif
                    >
                        <x-tabler-pin class="size-4 group-[&.pin-mode]:hidden" />
                        <x-tabler-pinned class="hidden size-4 group-[&.pin-mode]:block" />
                    </button>

                    <button
                        @class([
                            'chat-item-update-title' => !(
                                $disable_actions &&
                                (isset($category) && $category->slug !== 'ai_realtime_voice_chat')
                            ),
                            'flex size-7 items-center relative z-1 justify-center rounded-button border bg-background transition-all dark:bg-primary dark:text-primary-foreground dark:border-primary hover:scale-110 group-[&.edit-mode]:bg-emerald-500 group-[&.edit-mode]:border-emerald-500 group-[&.edit-mode]:text-white',
                        ])
                        @if ($disable_actions && (isset($category) && $category->slug !== 'ai_realtime_voice_chat')) onclick="return toastr.info('{{ __('This feature is disabled in Demo version.') }}')" @endif
                    >
                        <x-tabler-pencil class="size-4 group-[&.edit-mode]:hidden" />
                        <x-tabler-check class="hidden size-4 group-[&.edit-mode]:block" />
                    </button>
                    <button
                        @class([
                            'chat-item-delete' => !$disable_actions,
                            'flex size-7 items-center relative z-1 justify-center rounded-button border border-red-600 bg-red-600 text-white transition-all hover:scale-110 group-[&.edit-mode]:hidden',
                        ])
                        @if ($disable_actions) onclick="return toastr.info('{{ __('This feature is disabled in Demo version.') }}')" @endif
                    >
                        <x-tabler-x class="size-4" />
                    </button>
                </span>
            </li>
        @endforeach
    @endif
</ul>
