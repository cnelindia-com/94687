<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportSetting extends Model
{
    protected $table = 'support_settings';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'email',
        'tutorial_title',
        'tutorial_subtitle',
    ];

    /**
     * Get the current support settings (singleton)
     */
    public static function getSettings(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'name'             => 'Support Team',
                'email'            => 'support@example.com',
                'tutorial_title'   => __('How to Use Platform Features'),
                'tutorial_subtitle'=> __('Complete guide to all platform features'),
            ]
        );
    }
}
