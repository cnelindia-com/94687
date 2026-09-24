@php
    $marquee_items_1 = [__('offers'), __('promotions')];
@endphp

<section
    class="site-section relative overflow-hidden pt-10 transition-all duration-700 md:translate-y-8 md:opacity-0 [&.lqd-is-in-view]:translate-y-0 [&.lqd-is-in-view]:opacity-100"
    id="marquee"
>
    <div
        class="pointer-events-none absolute bottom-0 start-0 z-0 h-[500px] w-screen"
        style="background: linear-gradient(90deg, #5AAAF6 21.72%, #D52FCA 36.08%, #FF4754 52.07%, #FFB33B 66.16%, #43CC3E 78.08%); mask-image: linear-gradient(180deg, rgba(249, 249, 249, 0) 64.21%, #F9F9F9 93.07%);"
    ></div>
    <div class="relative z-1 grid grid-cols-1 place-items-center md:place-items-end">
        <div class="col-start-1 col-end-1 row-start-1 row-end-1 flex w-screen flex-col justify-center overflow-hidden md:min-h-[70vh]">
            <div
                class="flex gap-4"
                x-data="marqueev2"
            >
                @for ($i = 0; $i < 3; $i++)
                    @foreach ($marquee_items_1 as $item)
                        <div
                            class="lqd-marquee-cell whitespace-nowrap font-heading text-[100px] font-bold leading-none -tracking-wider opacity-15 md:text-[min(220px,20vw)] lg:text-[min(330px,20vw)]">
                            {!! $item !!}
                        </div>
                    @endforeach
                @endfor
            </div>
        </div>
        <div class="container relative z-1 col-start-1 col-end-1 row-start-1 row-end-1">
            <figure
                class="mb-14 flex justify-center"
                aria-hidden="true"
            >
                <img
                    class="mx-auto inline-block"
                    data-speed="0.9"
                    src="{{ custom_theme_url('/assets/landing-page/img/portrait-char.png') }}"
                    width="552.5"
                    height="666.5"
                >
            </figure>
            <figure
                class="absolute bottom-10 left-1/2 flex -translate-x-1/2 justify-center"
                aria-hidden="true"
            >
                <img
                    class="mx-auto inline-block rounded-xl bg-white/50 backdrop-blur-3xl backdrop-brightness-105 backdrop-saturate-150"
                    data-speed="0.8"
                    src="{{ custom_theme_url('/assets/landing-page/img/whatsapp-screenshot.png') }}"
                    width="452"
                    height="127.5"
                >
            </figure>
        </div>
    </div>

    <div class="pointer-events-none absolute -bottom-px start-0 z-2 w-screen">
        <svg
            class="absolute -bottom-px start-0 h-24 w-full"
            width="1440"
            height="91"
            viewBox="0 0 1440 91"
            fill="#fff"
            xmlns="http://www.w3.org/2000/svg"
            preserveAspectRatio="none"
        >
            <path d="M720 0.00100708C960 0.00100708 1200 30.087 1440 90.259H0C240 30.087 480 0.00100708 720 0.00100708Z" />
        </svg>
        <svg
            class="absolute -bottom-5 start-0 h-24 w-full"
            width="1440"
            height="91"
            viewBox="0 0 1440 91"
            fill="hsl(var(--background))"
            xmlns="http://www.w3.org/2000/svg"
            preserveAspectRatio="none"
        >
            <path d="M720 0.00100708C960 0.00100708 1200 30.087 1440 90.259H0C240 30.087 480 0.00100708 720 0.00100708Z" />
        </svg>
    </div>
</section>
