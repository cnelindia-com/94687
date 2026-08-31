<head>
    @if (!empty($setting->google_analytics_code))
        <script
            async
            src="https://www.googletagmanager.com/gtag/js?id={{ $setting->google_analytics_code }}"
        ></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag("js", new Date());
            gtag("config", '{{ $setting->google_analytics_code }}');
        </script>
    @endif
    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, viewport-fit=cover"
    />
    <meta
        http-equiv="X-UA-Compatible"
        content="ie=edge"
    />
    <meta
        http-equiv="Content-Security-Policy"
        content="upgrade-insecure-requests"
    >
    <meta
        name="description"
        content="{{ getMetaDesc($setting, $settings_two) }}"
    >
    @if (isset($setting->meta_keywords))
        <meta
            name="keywords"
            content="{{ $setting->meta_keywords }}"
        >
    @endif
    <link
        rel="icon"
        href="{{ custom_theme_url($setting->favicon_path ?? 'assets/favicon.ico', true) }}"
    >
    <title>{{ getMetaTitle($setting, $settings_two, ' ') ?? $setting->site_name }} | @yield('title')</title>

    @if (filled($google_fonts_string = \App\Helpers\Classes\ThemeHelper::googleFontsString('dashboard')))
        <link
            rel="preconnect"
            href="https://fonts.googleapis.com"
        >
        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
            crossorigin
        >
        <link
            href="https://fonts.googleapis.com/css2?{{ $google_fonts_string }}&display=swap"
            rel="stylesheet"
        >
    @endif

    <script>
        window.isDemo = "{{ $app_is_demo }}";
        window.liquid = {
            assetsPath: '{{ url(custom_theme_url('assets')) }}'
        };

        // Suppress Alpine.js SecurityError when it walks cross-origin iframes
        window.addEventListener('error', function(e) {
            if (e.error instanceof DOMException && e.error.name === 'SecurityError' && e.message && e.message.indexOf('cross-origin') > -1) {
                e.preventDefault();
            }
        });
    </script>

    {{-- CSS files --}}
    @if (!isset($disable_tblr) && empty($disable_tblr))
        <link
            href="{{ custom_theme_url('/assets/css/tabler.css') }}"
            rel="stylesheet"
        />
        <link
            href="{{ custom_theme_url('/assets/css/tabler-vendors.css') }}"
            rel="stylesheet"
        />
    @endif
    <link
        href="{{ custom_theme_url('/assets/libs/toastr/toastr.min.css') }}"
        rel="stylesheet"
    />
    <link
        href="{{ custom_theme_url('/assets/libs/introjs/introjs.min.css') }}"
        rel="stylesheet"
    >

    @yield('additional_css')

    @stack('css')

    @vite(\App\Helpers\Classes\ThemeHelper::dashboardScssPath())

    @if ($setting->dashboard_code_before_head != null)
        {!! $setting->dashboard_code_before_head !!}
    @endif

    @include('components.google-tag-manager', ['placement' => 'head'])
    @include('components.gtm-events')
    {!! setting('google_tag_manager', '') !!}

    <script>
        window.pusherConfig = @json(\Illuminate\Support\Arr::except(config('broadcasting.connections.pusher'), ['secret', 'app_id']));
    </script>

    @vite(\App\Helpers\Classes\ThemeHelper::appJsPath())

    @if (setting('additional_custom_css') != null)
        {!! setting('additional_custom_css') !!}
    @endif

    @livewireStyles

<!-- Pritam -->
    <style>
#dashboard{
display:none !important;
}

#documents{
    display:none !important; 
}


/* Direct IDs से hide */
/* #admin_dashboard,
#dashboard,
#documents,
#favorites,
#workbook {
    display: none !important;
} */

/* Safety: parent li भी hide */
/* li#admin_dashboard,
li#dashboard,
li#documents,
li#favorites,
li#workbook {
    display: none !important;
} */

.lqd-navbar-nav li#favorites,
.lqd-navbar-nav li#workbook,
.lqd-navbar-nav li#admin_dashboard,
.lqd-navbar-nav li#dashboard{
    display: none !important;
}
/* .lqd-navbar-nav a[href="#"] {
    display: none !important;
} */
/* 
.lqd-navbar-label {
    display: none !important;
}

/* View Your Credits button hide (FINAL) */
/* .lqd-remaining-credit {
    display: none !important;
}  */


</style>

    @stack('before-head-close')

    @includeIf('live-customizer::particles.lqd-customizer-style-head')
</head>
