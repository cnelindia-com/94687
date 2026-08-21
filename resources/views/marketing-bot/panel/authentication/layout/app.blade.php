@extends('panel.layout.app', ['layout_wide' => true, 'wide_layout_px' => 'px-0'])

@push('css')
    <style>
        .lqd-auth-content h1 {
            text-align: center
        }

        .lqd-auth-content .lqd-auth-form-foot-text {
            margin-top: 42px;
            text-align: center;
        }
    </style>
@endpush

@section('content')
    <header class="absolute left-0 right-0 top-0 z-2 flex items-center justify-between px-5 pt-6 lg:px-8 lg:pt-8">
        <a
            class="navbar-brand"
            href="{{ route('index') }}"
        >
            @if (isset($setting->logo_dashboard))
                <img
                    class="dark:hidden"
                    src="{{ custom_theme_url($setting->logo_dashboard_path, true) }}"
                    @if (isset($setting->logo_dashboard_2x_path) && !empty($setting->logo_dashboard_2x_path)) srcset="/{{ $setting->logo_dashboard_2x_path }} 2x" @endif
                    alt="{{ $setting->site_name }}"
                >
                <img
                    class="hidden dark:block"
                    src="{{ custom_theme_url($setting->logo_dashboard_dark_path, true) }}"
                    @if (isset($setting->logo_dashboard_dark_2x_path) && !empty($setting->logo_dashboard_dark_2x_path)) srcset="/{{ $setting->logo_dashboard_dark_2x_path }} 2x" @endif
                    alt="{{ $setting->site_name }}"
                >
            @else
                <img
                    class="dark:hidden"
                    src="{{ custom_theme_url($setting->logo_path, true) }}"
                    @if (isset($setting->logo_2x_path) && !empty($setting->logo_2x_path)) srcset="/{{ $setting->logo_2x_path }} 2x" @endif
                    alt="{{ $setting->site_name }}"
                >
                <img
                    class="hidden dark:block"
                    src="{{ custom_theme_url($setting->logo_dark_path, true) }}"
                    @if (isset($setting->logo_dark_2x_path) && !empty($setting->logo_dark_2x_path)) srcset="/{{ $setting->logo_dark_2x_path }} 2x" @endif
                    alt="{{ $setting->site_name }}"
                >
            @endif
        </a>

        <a
            class="inline-flex items-center gap-1 text-foreground no-underline hover:underline"
            href="{{ route('index') }}"
        >
            <x-tabler-chevron-left class="w-4" />
            {{ __('Back to Home') }}
        </a>
    </header>

    <div class="relative grid h-screen w-screen grid-cols-1 place-items-start px-5 pb-10 pt-20 sm:place-items-center">
        @if (
            $setting->auth_view_options != null &&
                $setting->auth_view_options != 'undefined' &&
                json_decode($setting->auth_view_options)?->login_enabled == true &&
                json_decode($setting->auth_view_options)?->login_image != null &&
                json_decode($setting->auth_view_options)?->login_image != '')
            <div
                class="absolute inset-0 z-0 bg-cover bg-center"
                style="background-image: url({{ json_decode($setting->auth_view_options)->login_image }})"
            ></div>
        @else
            <div
                class="absolute inset-0 z-0 bg-cover bg-bottom bg-no-repeat"
                style="background-image: url({{ custom_theme_url('/assets/img/bg/bg-auth.png') }}); background-size: min(90%,1000px);"
            ></div>
        @endif

        <div class="lqd-auth-content relative z-2 max-h-[calc(100vh-120px)] w-[min(calc(100vw-40px),460px)] overflow-y-auto rounded-[20px] bg-card-background p-8 md:px-8 md:py-10">
            @yield('form')
        </div>
    </div>
@endsection
