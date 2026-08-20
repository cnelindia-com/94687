<?php

declare(strict_types=1);

namespace App\Extensions\FashionStudio\System\Http\Controllers;

use App\Extensions\FashionStudio\System\Models\FashionStudioUserSetting;
use App\Helpers\Classes\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserSettingController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $settingsOwnerUser = $user ? FashionStudioUserSetting::getSettingsOwnerUser($user) : null;
        $settings = FashionStudioUserSetting::getForUser(Auth::id());
        $resolutions = $settingsOwnerUser ? FashionStudioUserSetting::getAvailableResolutionsForUser($settingsOwnerUser) : FashionStudioUserSetting::RESOLUTIONS;

        return view('fashion-studio::user-settings.index', [
            'settings'    => $settings,
            'resolutions' => $resolutions,
            'ratios'      => FashionStudioUserSetting::RATIOS,
            'maxImages'   => FashionStudioUserSetting::MAX_NUM_IMAGES,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (Helper::appIsDemo()) {
            return back()->with([
                'type'    => 'error',
                'message' => trans('This feature is disabled in demo mode.'),
            ]);
        }

        $user = Auth::user();
        $settingsOwnerUser = $user ? FashionStudioUserSetting::getSettingsOwnerUser($user) : null;
        $allowedResolutions = $settingsOwnerUser ? FashionStudioUserSetting::getAvailableResolutionsForUser($settingsOwnerUser) : FashionStudioUserSetting::RESOLUTIONS;

        $validated = $request->validate([
            'num_images'  => 'required|integer|min:1|max:' . FashionStudioUserSetting::MAX_NUM_IMAGES,
            'resolution'  => 'required|string|in:' . implode(',', $allowedResolutions),
            'ratio'       => 'required|string|in:' . implode(',', FashionStudioUserSetting::RATIOS),
        ]);

        // Get the correct settings record (team owner's settings if user is team member)
        $settings = FashionStudioUserSetting::getForUser(Auth::id());
        $settings->update($validated);

        return back()->with([
            'type'    => 'success',
            'message' => trans('Settings updated successfully.'),
        ]);
    }
}
