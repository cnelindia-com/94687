{!! adsense_how_it_works_728x90() !!}
<section
    class="site-section py-32 transition-all duration-700 max-sm:pb-20 md:translate-y-8 md:opacity-0 [&.lqd-is-in-view]:translate-y-0 [&.lqd-is-in-view]:opacity-100"
    id="how-it-works"
>
    <div class="container">
        <div
            class="flex flex-col gap-[--gap] pb-[--gap] [--gap:120px]"
            x-data="{}"
        >
            @foreach ($howitWorks as $item)
                <div class="flex gap-10 even:flex-row-reverse max-sm:flex-wrap">
                    <div class="basis-full place-self-center max-sm:order-1 md:basis-5/12">
                        <h3 class="mb-5">
                            {!! __($item->title) !!}
                        </h3>
                        <p>
                            {!! __($item->description) !!}
                        </p>
                    </div>
                    <div class="max-sm:order-0 flex flex-col items-center gap-5 max-sm:hidden max-sm:w-12 md:basis-2/12">
                        <span class="inline-grid size-10 place-items-center rounded-full border text-xs font-semibold text-heading-foreground">
                            {{ __($item->order) }}
                        </span>

                        <span
                            class="relative flex grow before:absolute before:-bottom-[calc(var(--gap)-1.25rem)] before:start-px before:top-0 before:w-px before:bg-current before:opacity-10"
                        >
                            <span
                                class="absolute -bottom-[calc(var(--gap)-1.25rem)] top-0 w-[3px] origin-top scale-y-0 rounded-full"
                                x-init="ScrollTrigger.create({ trigger: $el, animation: gsap.to($el, { scale: 1 }), scrub: true, start: 'top+=100 bottom', end: 'bottom center' })"
                                style="background: linear-gradient(to bottom, var(--gradient-1), var(--gradient-2), var(--gradient-3), var(--gradient-4), var(--gradient-5))"
                            ></span>
                        </span>
                    </div>
                    <div class="basis-full place-self-center max-sm:-order-1 md:basis-5/12">
                        <figure
                            class="overflow-hidden rounded-2xl"
                            aria-hidden="true"
                        >
                            <img src="{{ url('howitWorks/' . $item->image) }}">
                        </figure>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($howitWorksDefaults['option'] == 1)
            <div class="relative mx-auto mt-5 flex w-[min(512px,calc(100%-30px))] justify-center rounded-xl bg-background px-5 py-8 text-base font-normal">
                <x-outline-glow
                    class="lqd-outline-glow-custom [--gradient-1:transparent] [--gradient-2:#BB3398] [--gradient-3:#43CC3E] [--gradient-4:transparent] [--gradient-5:transparent] [--outline-glow-w:3px]"
                />
                {!! $howitWorksDefaults['html'] !!}
            </div>
        @endif
    </div>
</section>
