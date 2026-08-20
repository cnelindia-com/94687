<?php

declare(strict_types=1);

namespace App\Extensions\FashionStudio\System\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class FashionStudioUserSetting extends Model
{
    protected $table = 'fashion_studio_user_settings';

    protected $fillable = [
        'user_id',
        'num_images',
        'resolution',
        'ratio',
    ];

    protected $casts = [
        'num_images' => 'integer',
    ];

    public const DEFAULT_NUM_IMAGES = 1;

    public const MAX_NUM_IMAGES = 4;

    public const DEFAULT_RESOLUTION = '1K';

    public const DEFAULT_RATIO = '16:9';

    public const RESOLUTIONS = ['1K', '2K', '4K'];

    public const RATIOS = ['auto', '21:9', '16:9', '3:2', '4:3', '5:4', '1:1', '4:5', '3:4', '2:3', '9:16'];

    /**
     * Resolution to pixel dimensions mapping
     */
    public const RESOLUTION_DIMENSIONS = [
        '1K' => ['width' => 1024, 'height' => 1024],
        '2K' => ['width' => 2048, 'height' => 2048],
        '4K' => ['width' => 4096, 'height' => 4096],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getSettingsOwnerUser(User $user): User
    {
        $teamMember = DB::table('team_members')
            ->where('user_id', $user->id)
            ->first();

        if (! $teamMember?->team_id) {
            return $user;
        }

        $ownerId = DB::table('teams')
            ->where('id', $teamMember->team_id)
            ->value('user_id');

        if (! $ownerId) {
            return $user;
        }

        return User::query()->find($ownerId) ?: $user;
    }

    public static function getForUser(int $userId): self
    {
        // First check if user is a team owner
        $ownerTeam = \Illuminate\Support\Facades\DB::table('teams')
            ->where('user_id', $userId)
            ->first();
        
        \Log::info('getForUser called', [
            'user_id' => $userId,
            'is_owner' => $ownerTeam ? true : false
        ]);
        
        // If user is team owner, use their own settings
        if ($ownerTeam) {
            \Log::info('User is team owner, using own settings', ['user_id' => $userId]);
            return self::firstOrCreate(
                ['user_id' => $userId],
                [
                    'num_images'  => self::DEFAULT_NUM_IMAGES,
                    'resolution'  => self::DEFAULT_RESOLUTION,
                    'ratio'       => self::DEFAULT_RATIO,
                ]
            );
        }
        
        // Check if user is a team member (not owner)
        $teamMember = \Illuminate\Support\Facades\DB::table('team_members')
            ->where('user_id', $userId)
            ->first();
        
        \Log::info('Checking team member status', [
            'user_id' => $userId,
            'is_team_member' => $teamMember ? true : false,
            'team_id' => $teamMember->team_id ?? null
        ]);
        
        // If user is a team member, use team owner's settings
        if ($teamMember) {
            // Get team owner ID
            $teamOwnerId = \Illuminate\Support\Facades\DB::table('teams')
                ->where('id', $teamMember->team_id)
                ->value('user_id');
            
            \Log::info('Team member detected', [
                'user_id' => $userId,
                'team_id' => $teamMember->team_id,
                'owner_id' => $teamOwnerId
            ]);
            
            // If team owner found, use their settings
            if ($teamOwnerId) {
                $settings = self::firstOrCreate(
                    ['user_id' => $teamOwnerId],
                    [
                        'num_images'  => self::DEFAULT_NUM_IMAGES,
                        'resolution'  => self::DEFAULT_RESOLUTION,
                        'ratio'       => self::DEFAULT_RATIO,
                    ]
                );
                
                \Log::info('Returning team owner settings', [
                    'requested_user_id' => $userId,
                    'owner_id' => $teamOwnerId,
                    'settings_user_id' => $settings->user_id,
                    'num_images' => $settings->num_images,
                    'resolution' => $settings->resolution
                ]);
                
                return $settings;
            }
        }
        
        // Otherwise, use user's own settings (individual user)
        \Log::info('Individual user, using own settings', ['user_id' => $userId]);
        return self::firstOrCreate(
            ['user_id' => $userId],
            [
                'num_images'  => self::DEFAULT_NUM_IMAGES,
                'resolution'  => self::DEFAULT_RESOLUTION,
                'ratio'       => self::DEFAULT_RATIO,
            ]
        );
    }

    public static function getAvailableResolutionsForUser(User $user): array
    {
        $settingsOwnerUser = self::getSettingsOwnerUser($user);
        $plan = $settingsOwnerUser->effectiveRelationPlan();

        $resolutions = ['1K'];

        if ($plan?->image_2k) {
            $resolutions[] = '2K';
        }

        if ($plan?->image_4k) {
            $resolutions[] = '4K';
        }

        return $resolutions;
    }

    /**
     * Get the image size based on resolution and ratio
     */
    public function getImageSize(): array
    {
        $baseDimensions = self::RESOLUTION_DIMENSIONS[$this->resolution] ?? self::RESOLUTION_DIMENSIONS[self::DEFAULT_RESOLUTION];
        $ratioParts = explode(':', $this->ratio);

        if (count($ratioParts) !== 2) {
            return $baseDimensions;
        }

        $ratioWidth = (int) $ratioParts[0];
        $ratioHeight = (int) $ratioParts[1];

        if ($ratioWidth >= $ratioHeight) {
            $width = $baseDimensions['width'];
            $height = (int) round($width * $ratioHeight / $ratioWidth);
        } else {
            $height = $baseDimensions['height'];
            $width = (int) round($height * $ratioWidth / $ratioHeight);
        }

        return ['width' => $width, 'height' => $height];
    }
}
