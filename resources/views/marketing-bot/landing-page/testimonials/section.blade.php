{!! adsense_testimonials_728x90() !!}
<section
    class="site-section relative py-32 transition-all duration-700 max-sm:pt-24 md:translate-y-8 md:opacity-0 [&.lqd-is-in-view]:translate-y-0 [&.lqd-is-in-view]:opacity-100"
    id="testimonials"
>
    <div
        class="container relative"
        x-data="{}"
    >
        <svg
            class="pointer-events-none absolute start-0 top-20 -z-1 hidden w-full max-w-full lg:block"
            width="1012"
            height="663"
            viewBox="0 0 1012 663"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            x-init="ScrollTrigger.create({ trigger: '#testimonials', animation: gsap.to($el, { filter: 'hue-rotate(360deg)' }), scrub: true, start: 'top bottom', end: 'bottom top' })"
        >
            <path
                d="M425.669 41.6694C561.713 3.99184 693.554 3.2945 796.537 32.0534C899.769 60.8815 971.996 118.554 993.494 196.178C1014.99 273.801 982.725 360.413 909.032 438.241C835.516 515.882 722.101 583.109 586.058 620.787C450.014 658.464 318.172 659.162 215.189 630.403C111.957 601.575 39.7303 543.903 18.2322 466.28C-3.26588 388.656 29.0007 302.044 102.694 224.216C176.21 146.574 289.626 79.347 425.669 41.6694Z"
                stroke="url(#paint0_linear_298_32)"
                stroke-width="23"
            />
            <defs>
                <linearGradient
                    id="paint0_linear_298_32"
                    x1="768.908"
                    y1="423.05"
                    x2="262.885"
                    y2="0.437848"
                    gradientUnits="userSpaceOnUse"
                >
                    <stop stop-color="#E385FF" />
                    <stop
                        offset="0.115385"
                        stop-color="#435FFF"
                    />
                    <stop
                        offset="0.2332"
                        stop-color="#F9F9F9"
                    />
                    <stop
                        offset="0.518135"
                        stop-color="#F9F9F9"
                    />
                    <stop
                        offset="0.817308"
                        stop-color="#F9F9F9"
                    />
                    <stop
                        offset="1"
                        stop-color="#65D572"
                    />
                </linearGradient>
            </defs>
        </svg>

        <header class="mx-auto mb-28 text-center lg:w-1/2">
            <h2 class="mb-6">
                {!! $fSectSettings->testimonials_title !!}
            </h2>
            <p class="mb-6 text-base/[1.4em]">
                {!! $fSectSettings->testimonials_subtitle_one !!}
            </p>

            <div class="relative mx-auto flex w-[min(480px,100%)] items-center rounded-full bg-[#323232] p-[5px] text-xs text-white">
                <x-outline-glow class="lqd-outline-glow-custom [--outline-glow-w:3px]" />
                <div class="flex items-center gap-4">
                    {{-- blade-formatter-disable --}}
					<svg width="33" height="34" viewBox="0 0 33 34" fill="none" xmlns="http://www.w3.org/2000/svg" > <path d="M16.5 33.7832C25.6127 33.7832 33 26.3959 33 17.2832C33 8.17051 25.6127 0.783203 16.5 0.783203C7.3873 0.783203 0 8.17051 0 17.2832C0 26.3959 7.3873 33.7832 16.5 33.7832Z" fill="#FF492C" /> <path d="M23.6412 13.4222H20.823C20.8989 12.98 21.1728 12.7325 21.7272 12.452L22.2453 12.188C23.1726 11.7128 23.6676 11.1749 23.6676 10.2971C23.6676 9.746 23.4531 9.3104 23.0274 8.9969C22.6017 8.6834 22.1001 8.5283 21.5127 8.5283C21.0583 8.52296 20.6122 8.65026 20.229 8.8946C19.8429 9.1322 19.5558 9.4391 19.3776 9.8219L20.1927 10.6403C20.5095 10.0001 20.9682 9.6866 21.5721 9.6866C22.0836 9.6866 22.3971 9.9506 22.3971 10.3169C22.3971 10.6238 22.2453 10.8779 21.6579 11.1749L21.3246 11.3366C20.6019 11.7029 20.1003 12.122 19.8099 12.5972C19.5195 13.0724 19.3776 13.6697 19.3776 14.3924V14.5904H23.6412V13.4222Z" fill="white" /> <path d="M23.265 15.9368H18.5988L16.2657 19.976H20.9319L23.265 24.0185L25.5981 19.976L23.265 15.9368Z" fill="white" /> <path d="M16.6683 22.6721C13.6983 22.6721 11.2794 20.2532 11.2794 17.2832C11.2794 14.3132 13.6983 11.8943 16.6683 11.8943L18.513 8.0366C17.9055 7.91581 17.2877 7.85501 16.6683 7.8551C11.4609 7.8551 7.2402 12.0758 7.2402 17.2832C7.2402 22.4906 11.4609 26.7113 16.6683 26.7113C18.6639 26.715 20.6085 26.0815 22.2189 24.9029L20.1795 21.3653C19.2038 22.2081 17.9576 22.6719 16.6683 22.6721Z" fill="white" /> </svg>
					{{-- blade-formatter-enable --}}

                    <span class="inline-flex h-5 w-px bg-current opacity-25"></span>
                    <span>
                        {{ setting('testimonials_stars', '4.9 stars') }}
                    </span>
                    <span class="opacity-50 max-sm:hidden">
                        {{ setting('testimonials_count', 'Over 5000 testimonials') }}
                    </span>
                </div>

                <a
                    class="ms-auto rounded-full bg-white px-4 py-2.5 text-[12px] font-semibold text-black transition-all hover:scale-105 hover:shadow-xl hover:shadow-white/50"
                    href="#"
                >
                    {{ __('View All') }}
                </a>
            </div>
        </header>

        <h5 class="mb-7 text-center">
            {{ __('Join over 5,000 businesses that trust MagicAI') }}
        </h5>

        <div class="mx-auto lg:w-1/2">
            <div
                class="mb-14 [&_.dot.is-selected]:w-[18px] [&_.dot.is-selected]:opacity-100 [&_.dot]:mx-0 [&_.dot]:size-2.5 [&_.dot]:shrink-0 [&_.dot]:rounded-full [&_.dot]:bg-current [&_.dot]:opacity-25 [&_.dot]:transition-all max-sm:[&_.dot]:hidden [&_.flickity-page-dots]:-bottom-14 [&_.flickity-page-dots]:mx-0 [&_.flickity-page-dots]:flex [&_.flickity-page-dots]:items-center [&_.flickity-page-dots]:justify-center [&_.flickity-page-dots]:gap-3"
                x-data="templatesCarousel"
            >
                <div
                    class="relative flex [&_.flickity-slider]:w-full [&_.flickity-viewport]:w-full"
                    x-ref="carouselEl"
                >
                    @foreach ($testimonials as $item)
                        <div class="w-full shrink-0 grow-0 basis-auto text-center text-[#1E1E1E]">
                            <p class="mb-8 text-base/[1.7em] font-normal underline">
                                {!! __($item->words) !!}
                            </p>
                            <p class="mb-9 text-base/[1.7em] font-normal underline">
                                {!! __($item->full_name) !!}, {!! __($item->job_title) !!}
                            </p>
                        </div>
                    @endforeach
                </div>

                <button
                    class="inline-grid size-10 shrink-0 cursor-pointer place-items-center rounded-full bg-black/5 transition-colors hover:bg-black hover:text-white"
                    x-ref="prev"
                    type="button"
                    @click.prevent="flktyData?.previous()"
                >
                    <span class="sr-only">{{ __('Prev') }}</span>
                    <x-tabler-chevron-left class="size-4" />
                </button>
                <button
                    class="inline-grid size-10 shrink-0 cursor-pointer place-items-center rounded-full bg-black/5 transition-colors hover:bg-black hover:text-white"
                    x-ref="next"
                    type="button"
                    @click.prevent="flktyData?.next()"
                >
                    <span class="sr-only">{{ __('Next') }}</span>
                    <x-tabler-chevron-right class="size-4" />
                </button>
            </div>
        </div>

        <div class="mt-20 rounded-lg border p-5 text-center backdrop-blur-3xl backdrop-saturate-150 lg:mt-56">
            <p class="mx-auto mb-6 text-[12px] lg:w-1/3">
                {{ __('Collaborating with leading technology providers to deliver the best solutions.') }}
            </p>

            <div class="mx-auto flex items-center justify-center gap-20 max-lg:gap-12 max-md:flex-wrap max-sm:gap-4">
                @foreach ($clients->take(2) as $entry)
                    <img
                        class="transition-transform hover:scale-110"
                        src="{{ url('') . isset($entry->avatar) ? (str_starts_with($entry->avatar, 'asset') ? custom_theme_url($entry->avatar) : '/clientAvatar/' . $entry->avatar) : custom_theme_url('assets/img/auth/default-avatar.png') }}"
                        alt="{{ __($entry->alt) }}"
                        title="{{ __($entry->title) }}"
                    >
                    @if (!$loop->last)
                        <span class="inline-block h-9 w-px shrink-0 bg-black/10"></span>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</section>
