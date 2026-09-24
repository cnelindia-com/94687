{!! adsense_faq_728x90() !!}
<section
    class="site-section py-20 transition-all duration-700 md:translate-y-8 md:opacity-0 lg:py-32 [&.lqd-is-in-view]:translate-y-0 [&.lqd-is-in-view]:opacity-100"
    id="faq"
>
    <div class="container">
        <header class="mx-auto mb-20 w-full text-center lg:w-1/2">
            <h2 class="mb-5">
                {!! __($fSectSettings->faq_title) !!}
            </h2>
            <p>
                {!! __($fSectSettings->faq_subtitle) !!}
            </p>
        </header>

        <div class="relative">
            {{-- blade-formatter-disable --}}
            <svg class="pointer-events-none absolute left-1/2 hidden md:block -translate-x-1/2 top-0 -z-1 max-w-full" width="530" height="462" viewBox="0 0 530 462" fill="none" xmlns="http://www.w3.org/2000/svg" > <path d="M172.518 348.601C106.114 296.222 57.4399 235.215 32.138 179.398C6.56569 122.985 6.08237 75.1311 29.6151 45.2967C53.148 15.4624 99.798 4.7871 160.616 16.5171C220.791 28.123 291.458 61.2483 357.862 113.627C424.266 166.006 472.94 227.014 498.242 282.831C523.814 339.245 524.297 387.098 500.765 416.932C477.232 446.767 430.582 457.442 369.764 445.712C309.589 434.106 238.922 400.98 172.518 348.601Z" stroke="url(#paint0_linear_1_1165)" stroke-width="23" /> <defs> <linearGradient id="paint0_linear_1_1165" x1="352.736" y1="66.1642" x2="180.85" y2="373.43" gradientUnits="userSpaceOnUse" > <stop stop-color="#C851FF" /> <stop offset="0.2332" stop-color="#F9F9F9" /> <stop offset="0.518135" stop-color="#F9F9F9" /> <stop offset="0.817308" stop-color="#F9F9F9" /> <stop offset="1" stop-color="#89A4FF" /> </linearGradient> </defs> </svg>
			{{-- blade-formatter-enable --}}

            <div
                class="lqd-accordion space-y-3.5"
                x-data="{ activeIndex: -1 }"
            >
                @foreach ($faq as $item)
                    <div class="rounded-lg border-b border-black/5 bg-white/60 backdrop-blur-xl">
                        <button
                            class="flex w-full items-center justify-between gap-2 px-6 py-9 text-start font-heading text-[18px] font-semibold leading-[1/15em]"
                            type="button"
                            @click.prevent="activeIndex = activeIndex === {{ $loop->index }} ? -1 : {{ $loop->index }}"
                        >
                            {!! __($item->question) !!}
                            <span class="inline-grid place-items-center">
                                <x-tabler-plus
                                    class="col-start-1 col-end-1 row-start-1 row-end-1 size-5"
                                    x-show="activeIndex !== {{ $loop->index }}"
                                />
                                <x-tabler-minus
                                    class="col-start-1 col-end-1 row-start-1 row-end-1 size-5"
                                    x-cloak
                                    x-show="activeIndex === {{ $loop->index }}"
                                />
                            </span>
                        </button>

                        <div
                            x-cloak
                            x-show="activeIndex === {{ $loop->index }}"
                            x-collapse
                        >
                            <p class="m-0 px-5 pb-7 lg:w-10/12">
                                {!! __($item->answer) !!}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
