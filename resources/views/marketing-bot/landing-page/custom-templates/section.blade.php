{!! adsense_templates_728x90() !!}
<section
    class="site-section border-b py-20 transition-all duration-700 md:translate-y-8 md:opacity-0 [&.lqd-is-in-view]:translate-y-0 [&.lqd-is-in-view]:opacity-100"
    id="templates"
>
    <div class="container">
        <div
            class="mb-14 [&_.dot.is-selected]:w-[18px] [&_.dot.is-selected]:opacity-100 [&_.dot]:mx-0 [&_.dot]:size-2.5 [&_.dot]:shrink-0 [&_.dot]:rounded-full [&_.dot]:bg-current [&_.dot]:opacity-25 [&_.dot]:transition-all max-sm:[&_.dot]:hidden [&_.flickity-page-dots]:-bottom-14 [&_.flickity-page-dots]:mx-0 [&_.flickity-page-dots]:flex [&_.flickity-page-dots]:items-center [&_.flickity-page-dots]:justify-center [&_.flickity-page-dots]:gap-3"
            x-data="templatesCarousel"
        >
            <div
                class="relative -mx-[calc(var(--gap)/2)] flex [--cols:1] [--gap:30px] md:[--cols:2] lg:[--cols:4] [&_.flickity-slider]:w-full [&_.flickity-viewport]:w-full"
                x-ref="carouselEl"
            >
                @foreach ($templates as $item)
                    @if ($item->active != 1)
                        @continue
                    @endif

                    <div class="mb-14 w-[calc((100%/var(--cols,1)))] shrink-0 grow-0 basis-auto px-[calc(var(--gap)/2)]">
                        <div class="mb-6 grid aspect-[1/0.77] place-items-center rounded-[18px] bg-black/[3%] p-4 [&_svg]:h-auto [&_svg]:w-[60px]">
                            {!! stripslashes($item->image) !!}
                        </div>
                        <h6 class="mb-3.5">
                            {{ __($item->title) }}
                        </h6>
                        <p class="mb-0 text-base">
                            {{ __($item->description) }}
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
</section>

@push('script')
    <script>
        (() => {
            document.addEventListener('alpine:init', () => {
                Alpine.data('templatesCarousel', () => ({
                    flktyData: null,
                    init() {
                        this.flktyData = new Flickity(this.$refs.carouselEl, {
                            autoplay: true,
                            cellAlign: "left",
                            contain: true,
                            groupCells: true,
                            wrapAround: true,
                            prevNextButtons: false,
                            pageDots: true,
                            adaptiveHeight: true
                        });

                        this.flktyData.pageDots.holder.insertAdjacentElement('afterbegin', this.$refs.prev);
                        this.flktyData.pageDots.holder.insertAdjacentElement('beforeend', this.$refs.next);
                    }
                }))
            })
        })();
    </script>
@endpush
