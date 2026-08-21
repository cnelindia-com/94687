@php
    $items = \App\Models\Frontend\Curtain::query()->select('title', 'title_icon', 'sliders')->get()->toArray();
@endphp

@php
    $settings = \App\Models\Setting::getCache();
@endphp

<style>
    
    #banner {
        position: relative;
        z-index: 1;
    }

    
    #vertical-slider {
        position: relative;
        z-index: 10;
        background: #ffffff;  
        isolation: isolate;
    }

    #vertical-slider-wrapper {
        position: relative;
        z-index: 10;
        background: #ffffff;
        isolation: isolate;
        border-radius: 24px;
    }

    
    #vertical-slider .lqd-curtain {
        position: relative;
        z-index: 3;
        background: transparent;
    }

    #vertical-slider .lqd-curtain-item {
        position: relative;
        z-index: 2;
    }

    #vertical-slider .lqd-curtain-item-active {
        z-index: 5 !important;
    }

    #vertical-slider .pin-spacer {
        z-index: 2 !important;
        background: #ffffff !important;
    }

    #vertical-slider .lqd-curtain-item-content {
        position: relative;
        z-index: 2;
    }
</style>

<section
    class="site-section pt-32 transition-all duration-700 md:translate-y-8 md:pt-10 md:opacity-0 [&.lqd-is-in-view]:translate-y-0 [&.lqd-is-in-view]:opacity-100"
    id="vertical-slider"
>
    <div class="container">
        <div class="relative mx-auto mb-10 w-full text-center md:mb-20 lg:w-1/2">
            <h2>
               {{ $settings->slider_heading ?? 'Intelligent AI tools to boost your productivity' }}
           </h2>
        </div>

        <div id="vertical-slider-wrapper" class="relative w-full sm:overflow-hidden sm:rounded-3xl sm:bg-black/[1%] sm:p-9">
            <div
                class="absolute bottom-0 start-0 z-0 hidden h-full w-full sm:block"
                style="background: linear-gradient(90deg, #5AAAF6 21.72%, #D52FCA 36.08%, #FF4754 52.07%, #FFB33B 66.16%, #43CC3E 78.08%); mask-image: linear-gradient(180deg, rgba(249, 249, 249, 0) 70%, #F9F9F9 100%);"
            ></div>

            <x-curtain :$items />
        </div>
    </div>
</section>