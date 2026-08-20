<?php

namespace App\Extensions\FashionStudio\System\Http\Controllers;

use App\Helpers\Classes\MarketplaceHelper;
use App\Http\Controllers\Controller;

class FashionStudioController extends Controller
{
    public function __invoke()
    {
        $tools = $this->getTools();

        return view('fashion-studio::index', compact('tools'));
    }

    public function getTools(): array
    {
        $data = [
            // pritam
            [
                'name'        => __('Create PhotoShoot'),
                'description' => __('Studio Grade Images'),
                'route'       => route('dashboard.user.fashion-studio.photo_shoots.index'),
                'image'       => asset('vendor/fashion-studio/images/tools/change-model.jpg'),
            ],
            [
                'name'        => __('Fashion PlayGround'),
                'description' => __('Create Variations'),
                'route'       => route('dashboard.user.fashion-studio.edit_image.index'),
                'image'       => asset('vendor/fashion-studio/images/tools/create-variations.png'),
            ],
        ];

        if (MarketplaceHelper::isRegistered('ai-video-pro')) {
            $data[] = [
                'name'        => __('Create Video'),
                'description' => __('Bring Your Images to Life'),
                'route'       => route('dashboard.user.ai-video-pro.index'),
                'image'       => asset('vendor/fashion-studio/images/tools/create-video.png'),
            ];
        }

        $data[] = [
            'name'        => __('Virtual Try-On'),
            'description' => __('Change Clothes'),
            'route'       => route('dashboard.user.fashion-studio.virtual_try_on.index'),
            'image'       => asset('vendor/fashion-studio/images/tools/virtual-try-on.png'),
        ];

        return $data;
    }
}
