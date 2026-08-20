<?php

declare(strict_types=1);

namespace App\Extensions\FashionStudio\System;

use App\Domains\Marketplace\Contracts\ExtensionRegisterKeyProviderInterface;
use App\Domains\Marketplace\Contracts\UninstallExtensionServiceProviderInterface;
use App\Extensions\FashionStudio\System\Http\Controllers\BackgroundController;
use App\Extensions\FashionStudio\System\Http\Controllers\ChangeModelController;
use App\Extensions\FashionStudio\System\Http\Controllers\CreateVideoController;
use App\Extensions\FashionStudio\System\Http\Controllers\EditImageController;
use App\Extensions\FashionStudio\System\Http\Controllers\FashionModelController;
use App\Extensions\FashionStudio\System\Http\Controllers\FashionStudioController;
use App\Extensions\FashionStudio\System\Http\Controllers\FashionStudioSettingController;
use App\Extensions\FashionStudio\System\Http\Controllers\PhotoShootController;
use App\Extensions\FashionStudio\System\Http\Controllers\PoseController;
use App\Extensions\FashionStudio\System\Http\Controllers\UserSettingController;
use App\Extensions\FashionStudio\System\Http\Controllers\VirtualTryOnController;
use App\Extensions\FashionStudio\System\Http\Controllers\WardrobeController;
use App\Http\Middleware\CheckTemplateTypeAndPlan;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Author: MagicAI Team <info@liquid-themes.com>
 *
 * @note When you create a new service provider, make sure to add it to the "MarketplaceServiceProvider". Otherwise, your Laravel application won’t recognize this provider, and the related functions won’t work properly.
 * @note If you want to perform a specific action when an extension is uninstalled, you can use the UninstallExtensionServiceProviderInterface. By implementing this interface, you can define custom operations that will be triggered during the uninstallation of the extension.
 * @note The registerKey() method is used to provide a unique identifier for the extension, which is essential for the healthy check and other functionalities.
 */
class FashionStudioServiceProvider extends ServiceProvider implements ExtensionRegisterKeyProviderInterface, UninstallExtensionServiceProviderInterface
{
    public function register(): void
    {
        $this->registerConfig();
    }

    public function boot(Kernel $kernel): void
    {
        $this->registerTranslations()
            ->registerViews()
            ->registerRoutes()
            ->registerMigrations()
            ->publishAssets()
            ->registerComponents();

    }

    public function registerComponents(): static
    {
        //        $this->loadViewComponentsAs('fashion-studio', []);

        return $this;
    }

    public function publishAssets(): static
    {
        $this->publishes([
            // __DIR__ . '/../resources/assets/js'     => public_path('vendor/fashion-studio/js'),
            __DIR__ . '/../resources/assets/images' => public_path('vendor/fashion-studio/images'),
        ], 'extension');

        return $this;
    }

    public function registerConfig(): static
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/fashion-studio.php', 'fashion-studio');

        return $this;
    }

    protected function registerTranslations(): static
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'fashion-studio');

        return $this;
    }

    public function registerViews(): static
    {
        $this->loadViewsFrom([__DIR__ . '/../resources/views'], 'fashion-studio');

        return $this;
    }

    public function registerMigrations(): static
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        return $this;
    }

    private function registerRoutes(): static
    {
        $this->router()
            ->group([
                'middleware' => ['web', 'auth'],
                'prefix'     => 'dashboard/user/fashion-studio',
                'as'         => 'dashboard.user.fashion-studio.',
            ], function (Router $router) {
                $router->get('/', FashionStudioController::class)->name('index')->middleware(['check.viewer.role', CheckTemplateTypeAndPlan::class]);

                $router->group([
                    'prefix'     => 'photo_shoots',
                    'as'         => 'photo_shoots.',
                    'controller' => PhotoShootController::class,
                ], function (Router $router) {
                    $router->get('/', 'index')->name('index')->middleware(['check.viewer.role', CheckTemplateTypeAndPlan::class]);
                    $router->get('/my-photoshoots', 'myPhotoshoots')->name('my');
                    $router->post('/generate', 'generate')->name('generate')->middleware('check.viewer.role');
                    $router->get('/status/{id?}', 'status')->name('status')->middleware('check.viewer.role');
                    $router->get('/images', 'loadImages')->name('images.load');
                    $router->post('/images/remove', 'removeImage')->name('images.remove');
                    $router->post('/images/crop', 'cropImage')->name('images.crop');
                    $router->get('/images/download/{id}', 'downloadImage')->name('images.download');
                    $router->delete('/{id}', 'destroy')->name('destroy');
                });

                $router->group([
                    'prefix'     => 'virtual_try_on',
                    'as'         => 'virtual_try_on.',
                    'controller' => VirtualTryOnController::class,
                ], function (Router $router) {
                    $router->get('/', 'index')->name('index')->middleware(['check.viewer.role', CheckTemplateTypeAndPlan::class]);
                    $router->post('/generate', 'generate')->name('generate')->middleware('check.viewer.role');
                    $router->post('/preview', 'preview')->name('preview')->middleware('check.viewer.role');
                    $router->get('/status/{id?}', 'status')->name('status')->middleware('check.viewer.role');
                });

                $router->group([
                    'prefix'     => 'change_model',
                    'as'         => 'change_model.',
                    'controller' => ChangeModelController::class,
                ], function (Router $router) {
                    $router->get('/', 'index')->name('index')->middleware(['check.viewer.role', CheckTemplateTypeAndPlan::class]);
                    $router->post('/generate', 'generate')->name('generate')->middleware('check.viewer.role');
                    $router->get('/status/{id?}', 'status')->name('status')->middleware('check.viewer.role');
                });

                $router->group([
                    'prefix'     => 'edit_image',
                    'as'         => 'edit_image.',
                    'controller' => EditImageController::class,
                ], function (Router $router) {
                    $router->get('/', 'index')->name('index')->middleware(['check.viewer.role', CheckTemplateTypeAndPlan::class]);
                    $router->post('/generate', 'generate')->name('generate')->middleware('check.viewer.role');
                    $router->get('/status/{id?}', 'status')->name('status')->middleware('check.viewer.role');
                });

                // Proxy route for loading images from DigitalOcean Spaces (avoids CORS)
                $router->get('/proxy-image', [PhotoShootController::class, 'proxyImage'])->name('proxy.image');

                $router->group([
                    'prefix'     => 'create_video',
                    'as'         => 'create_video.',
                    'controller' => CreateVideoController::class,
                ], function (Router $router) {
                    $router->get('/{image_id?}', 'createVideo')->name('index')->middleware(['check.viewer.role', CheckTemplateTypeAndPlan::class]);
                    $router->post('/generate', 'generateVideo')->name('generate')->middleware('check.viewer.role');
                    $router->get('/status/{id?}', 'videoStatus')->name('status')->middleware('check.viewer.role');
                });

                $router->group([
                    'prefix'     => 'wardrobe',
                    'as'         => 'wardrobe.',
                    'controller' => WardrobeController::class,
                ], function (Router $router) {
                    $router->get('/', 'index')->name('index')->middleware(['check.viewer.role', CheckTemplateTypeAndPlan::class]);

                    $router->get('/load', 'loadWardrobe')->name('load')->middleware('check.viewer.role');
                    $router->post('/upload', 'uploadProduct')->name('upload')->middleware('check.viewer.role');
                    $router->post('/create', 'createProduct')->name('create')->middleware('check.viewer.role');
                    $router->delete('/delete/{productId}', 'deleteProduct')->name('delete')->middleware('check.viewer.role');
                    $router->get('/load/status/{id}', 'checkStatus')->name('status')->middleware('check.viewer.role');
                });

                $router->group([
                    'prefix'     => 'pose',
                    'as'         => 'pose.',
                    'controller' => PoseController::class,
                ], function (Router $router) {
                    $router->get('/load', 'loadPoses')->name('load')->middleware('check.viewer.role');
                    $router->post('/upload', 'uploadPose')->name('upload')->middleware('check.viewer.role');
                    $router->post('/create', 'createPose')->name('create')->middleware('check.viewer.role');
                    $router->delete('/delete/{poseId}', 'deletePose')->name('delete')->middleware('check.viewer.role');
                    $router->get('/load/status/{id}', 'checkStatus')->name('status')->middleware('check.viewer.role');
                });

                $router->group([
                    'prefix'     => 'background',
                    'as'         => 'background.',
                    'controller' => BackgroundController::class,
                ], function (Router $router) {
                    $router->get('/load', 'loadBackgrounds')->name('load')->middleware('check.viewer.role');
                    $router->post('/upload', 'uploadBackground')->name('upload')->middleware('check.viewer.role');
                    $router->post('/create', 'createBackground')->name('create')->middleware('check.viewer.role');
                    $router->delete('/delete/{backgroundId}', 'deleteBackground')->name('delete')->middleware('check.viewer.role');
                    $router->get('/load/status/{id}', 'checkStatus')->name('status')->middleware('check.viewer.role');
                });

                $router->group([
                    'prefix'     => 'model',
                    'as'         => 'model.',
                    'controller' => FashionModelController::class,
                ], function (Router $router) {
                    $router->get('/load', 'loadModels')->name('load')->middleware('check.viewer.role');
                    $router->post('/upload', 'uploadModel')->name('upload')->middleware('check.viewer.role');
                    $router->post('/create', 'createModel')->name('create')->middleware('check.viewer.role');
                    $router->delete('/delete/{modelId}', 'deleteModel')->name('delete')->middleware('check.viewer.role');
                    $router->get('/load/status/{id}', 'checkStatus')->name('status')->middleware('check.viewer.role');
                });

                $router->group([
                    'prefix'     => 'user_settings',
                    'as'         => 'user_settings.',
                    'controller' => UserSettingController::class,
                ], function (Router $router) {
                    $router->get('/', 'index')->name('index')->middleware('check.viewer.role');
                    $router->post('/', 'update')->name('update')->middleware('check.viewer.role');
                });
            });

        // Admin routes for Fashion Studio settings
        $this->router()
            ->group([
                'middleware' => ['web', 'auth', 'admin'],
                'prefix'     => 'dashboard/admin/fashion-studio',
                'as'         => 'dashboard.admin.fashion-studio.',
            ], function (Router $router) {
                $router->get('/settings', [FashionStudioSettingController::class, 'index'])->name('settings');
                $router->post('/settings', [FashionStudioSettingController::class, 'update'])->name('settings.update');
            });

        return $this;
    }

    private function router(): Router|Route
    {
        return $this->app['router'];
    }

    public static function uninstall(): void
    {
        // TODO: Implement uninstall() method.
    }

    public function registerKey(): string
    {
        return 'fashion-studio';
    }
}
