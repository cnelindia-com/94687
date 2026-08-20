<?php

declare(strict_types=1);

namespace App\Extensions\FashionStudio\System\Http\Controllers;

use App\Domains\Entity\Enums\EntityEnum;
use App\Helpers\Classes\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FashionStudioSettingController extends Controller
{
    /**
     * Get available image-to-video models for Fashion Studio
     */
    private function getVideoModels(): array
    {
        return [
            EntityEnum::VEO_3_1_IMAGE_TO_VIDEO->value      => 'Veo 3.1 Image to Video',
            EntityEnum::VEO_3_1_IMAGE_TO_VIDEO_FAST->value => 'Veo 3.1 Image to Video (Fast)',
        ];
    }

    public function index(Request $request): RedirectResponse|View
    {
        return view('fashion-studio::admin.settings', [
            'videoModels'        => $this->getVideoModels(),
            'currentVideoModel'  => setting('fashion-studio-video-default-model', EntityEnum::VEO_3_1_IMAGE_TO_VIDEO->value),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (Helper::appIsDemo()) {
            return to_route('dashboard.user.index')->with([
                'status'  => 'error',
                'message' => trans('This feature is disabled in demo mode.'),
            ]);
        }

        $data = $request->validate([
            'fashion-studio-video-default-model' => 'required|string',
        ]);

        setting($data)->save();

        return back()->with([
            'type'    => 'success',
            'message' => trans('Settings updated successfully.'),
        ]);
    }
}
