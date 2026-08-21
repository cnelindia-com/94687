<section
    class="site-section group/section relative min-h-screen pb-32 pt-20 lg:pt-10"
    id="generators"
    data-color-scheme="dark"
    x-data="{}"
>
    <div
        class="absolute inset-x-0 -top-px bottom-0 origin-top rounded-b-[50px] bg-[#090508] bg-cover bg-bottom bg-blend-screen"
        style="background-image: url({{ custom_theme_url('/assets/landing-page/img/bg-1.jpg') }})"
        x-init="ScrollTrigger.create({ trigger: '#generators', animation: gsap.to($el, { scale: 0.96 }), scrub: true, start: 'bottom bottom', end: 'bottom center' })"
    ></div>

    <div class="container relative transition-all duration-700 group-[&.lqd-is-in-view]/section:opacity-100 max-lg:px-8 max-sm:px-5 md:opacity-0">
        <div class="rounded-6xl bg-[#1c1c1c] px-6 pb-20 pt-8 md:px-16">
            <div
                class="lqd-tabs"
                data-lqd-tabs-style="1"
            >
                <div class="lqd-tabs-triggers mb-24 flex flex-col justify-between gap-3 max-lg:flex-wrap sm:flex-row md:gap-5 lg:gap-8">
                    @foreach ($generatorsList as $item)
                        @include('landing-page.generators.item-trigger')
                    @endforeach
                </div>

                <div class="lqd-tabs-content-wrap">
                    @foreach ($generatorsList as $item)
                        @include('landing-page.generators.item-content')
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
