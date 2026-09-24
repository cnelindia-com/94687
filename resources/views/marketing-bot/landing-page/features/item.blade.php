<div class="lqd-feature-item group relative flex flex-col rounded-[36px] bg-black/5 py-10 backdrop-blur-lg transition-all hover:scale-[1.025] hover:bg-[hsl(0_0%_11%/80%)]">
    <x-outline-glow
        class="lqd-outline-glow-custom rounded-[39px] opacity-0 [--gradient-1:#993DFC] [--gradient-2:#10111A] [--gradient-3:#10111A] [--gradient-4:#EE554A] [--gradient-5:#70B4AF] [--outline-glow-w:3px] group-hover:opacity-100"
        class:inner="![animation-play-state:paused] group-hover:![animation-play-state:running]"
    />
    <div class="mb-24 px-10">
        <span class="lqd-feature-item-icon inline-grid size-12 place-items-center rounded-full bg-white/10 transition-all group-hover:scale-110 [&_svg]:h-auto [&_svg]:w-full">
            <span class="inline-flex size-5">
                {!! $item->image !!}
            </span>
        </span>
    </div>
    <h6 class="mb-3 border-b border-white/10 px-10 pb-3 text-current">
        {!! __($item->title) !!}
    </h6>
    <p class="mb-0 px-10 text-sm font-normal leading-[1.4em]">
        {!! __($item->description) !!}
    </p>
</div>

@pushOnce('css')
    <style>
        .lqd-feature-item-icon {
            box-shadow: inset 1px 1px 0 hsl(0 0% 100%/40%), inset 2px 2px 4px -1px hsl(0 0% 100%/20%), inset -1px -1px 3px -2px hsl(0 0% 100%/40%), inset -1px -1px 2px -2px hsl(0 0% 100%/40%), inset -3px -3px 4px -4px hsl(0 0% 100%/40%);
        }

        .lqd-feature-item:hover .lqd-feature-item-icon {
            box-shadow: inset -1px 1px 0 hsl(0 0% 100%/60%), inset -2px 2px 5px -1px hsl(0 0% 100%/40%), inset -1px -1px 3px -1px hsl(0 0% 100%/10%), 3px -3px 6px -2px hsl(0 0% 100%/20%);
        }
    </style>
@endpushOnce
