@extends('layout.app')

@push('script')
    <script src="{{ custom_theme_url('/assets/libs/gsap/minified/gsap.min.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/libs/gsap/minified/Observer.min.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/libs/gsap/minified/ScrollTrigger.min.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/libs/gsap/minified/ScrollToPlugin.min.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/libs/gsap/minified/SplitText.min.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/libs/flickity.pkgd.min.js') }}"></script>
@endpush
