@extends('layout.app')

@section('content')
    @include('landing-page.banner.section')

    @includeWhen($fSectSettings->testimonials_active == 1, 'landing-page.clients.section')

    @includeWhen($fSectSettings->features_active == 1, 'landing-page.features.section')

    @includeWhen($fSectSettings->generators_active == 1, 'landing-page.generators.section')

    @includeWhen(setting('marketing_tools', '1') === '1', 'landing-page.marketing-tools.section')

    @includeWhen($fSectSettings->tools_active == 1, 'landing-page.tools.section')

    @includeWhen(setting('marquee_active', '1') === '1', 'landing-page.marquee.section')

    @includeWhen(setting('vertical_slider_active', '1') === '1', 'landing-page.vertical-slider.section')

    @includeWhen($fSectSettings->custom_templates_active == 1, 'landing-page.custom-templates.section')

    @includeWhen($fSectSettings->how_it_works_active == 1, 'landing-page.how-it-works.section')

    @includeWhen($fSectSettings->testimonials_active == 1, 'landing-page.testimonials.section')

    @includeWhen($fSectSettings->pricing_active == 1, 'landing-page.pricing.section')

    @includeWhen($fSectSettings->faq_active == 1, 'landing-page.faq.section')

    @includeWhen($fSectSettings->who_is_for_active == 1, 'landing-page.who-is-for.section')

    @includeWhen($fSectSettings->blog_active == 1, 'landing-page.blog.section')

    @includeWhen($setting->gdpr_status == 1, 'landing-page.gdpr')
@endsection

@push('script')
    <script src="{{ custom_theme_url('/assets/libs/gsap/minified/gsap.min.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/libs/gsap/minified/Observer.min.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/libs/gsap/minified/ScrollTrigger.min.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/libs/gsap/minified/ScrollToPlugin.min.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/libs/gsap/minified/SplitText.min.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/libs/flickity.pkgd.min.js') }}"></script>
@endpush
