@php
    $settings = \App\Models\Setting::getCache();
@endphp
<section
    class="site-section py-20 transition-all duration-700 md:translate-y-8 md:opacity-0 [&.lqd-is-in-view]:translate-y-0 [&.lqd-is-in-view]:opacity-100"
    id="who-is-for"
    data-color-scheme="dark"
>
    <div class="container">
        <div
            class="relative overflow-hidden rounded-[34px] bg-[#080407] px-7 py-10 md:px-16 md:py-20 lg:px-20 lg:py-24"
            x-data="{}"
        >
            <div
                class="pointer-events-none absolute end-0 top-0 translate-x-1/4 translate-y-1/3"
                x-init="ScrollTrigger.create({ trigger: '#who-is-for', animation: gsap.to($el, { rotate: 360 }), scrub: true, start: 'top bottom', end: 'bottom top' })"
            >
                {{-- blade-formatter-disable --}}
				<svg width="729" height="725" viewBox="0 0 729 725" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M303.384 17.3251C495.041 -16.8077 677.862 110.18 711.815 300.831C745.768 491.481 618.03 673.776 426.373 707.909C234.716 742.042 51.8958 615.055 17.942 424.405C-16.0115 233.754 111.727 51.4579 303.384 17.3251Z" stroke="url(#paint0_linear_300_6)" stroke-width="23"/> <defs> <linearGradient id="paint0_linear_300_6" x1="586.572" y1="108.951" x2="23.0812" y2="132.807" gradientUnits="userSpaceOnUse"> <stop stop-color="#A957D8"/> <stop offset="0.2332" stop-color="#000002"/> <stop offset="0.518135" stop-color="#000002"/> <stop offset="0.817308" stop-color="#000002"/> <stop offset="1" stop-color="#3FCC69"/> </linearGradient> </defs> </svg>
				{{-- blade-formatter-enable --}}
            </div>
            <div
                class="pointer-events-none absolute bottom-0 start-0 z-0 h-[500px] w-screen opacity-40"
                style="background: linear-gradient(90deg, #5AAAF6 21.72%, #D52FCA 36.08%, #FF4754 52.07%, #FFB33B 66.16%, #43CC3E 78.08%); mask-image: linear-gradient(180deg, rgba(249, 249, 249, 0) 65%, #F9F9F9 100%);"
            ></div>

            <div class="relative z-1 flex flex-wrap items-center justify-between gap-y-10">
                <div class="w-full lg:w-6/12">
                    <h2 class="mb-8 text-white">
                      {{ $settings->transform_marketing ?? 'Ready to transform your marketing?' }}
                    </h2>
                    <p class="mb-8 text-lg/[1.4em] text-white/80">
                   
                   {{ $settings->ai_powered_text ?? 'Join thousands of businesses using AI-powered messaging to boost engagement and drive growth.' }} 
                      
                   </p>

                    <a
                        class="group relative inline-flex items-center gap-4 rounded-xl bg-foreground px-7 py-5 text-base font-semibold text-background transition-all after:pointer-events-none after:absolute after:-bottom-[5px] after:start-0 after:-z-1 after:h-full after:w-full after:rounded-xl after:transition-transform after:[background:linear-gradient(to_right,var(--gradient-stops))] hover:bg-background hover:text-foreground hover:shadow-lg hover:shadow-black/10 hover:after:-translate-y-[5px]"
                        href="{{ !empty($fSetting->hero_button_url) ? $fSetting->hero_button_url : '#' }}"
                    >
                        {{ $settings->get_started_text ?? 'Get Started' }}
                        {{-- blade-formatter-disable --}}
						<svg class="transition-transform group-hover:translate-x-1" fill-rule="evenodd" clip-rule="evenodd" width="17" height="12" viewBox="0 0 17 12" fill="currentColor" xmlns="http://www.w3.org/2000/svg" > <path d="M15.2643 5.10208C13.0752 5.10208 11.0801 3.10784 11.0801 0.917858V0.0199585H9.28428V0.917858C9.28428 2.51073 9.98285 4.00484 11.0792 5.10208H0V6.89788H11.0792C9.98285 7.99511 9.28428 9.48922 9.28428 11.0821V11.98H11.0801V11.0821C11.0801 8.89211 13.0752 6.89788 15.2643 6.89788H16.1622V5.10208H15.2643Z" /> </svg>
						{{-- blade-formatter-enable --}}
                    </a>
                </div>

                <div class="w-full lg:w-5/12">
                    <div class="flex flex-col gap-6">
                        @foreach ($who_is_for as $item)
                            <h4 class="group m-0 flex items-center gap-4 text-white/50 transition-colors hover:text-white">
                                <span
                                    class="inline-grid size-11 place-items-center rounded-md bg-white/20 transition-all group-hover:bg-white group-hover:text-black group-hover:!opacity-100 group-hover:shadow-lg group-hover:shadow-white/30"
                                    style="opacity: {{ 0.3 + 0.5 * (1 - abs(($loop->index - ($loop->count - 1) / 2) / (($loop->count - 1) / 2))) }}"
                                >
                                    <x-tabler-check class="size-6" />
                                </span>
                                {!! __($item->title) !!}
                            </h4>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
