<div
    class="group relative flex min-h-[230px] flex-wrap items-center gap-y-7 overflow-hidden rounded-xl bg-gradient-to-t from-secondary/40 from-60% to-[hsl(from_hsl(var(--secondary))_calc(h-30)_calc(s+30)_calc(l-5))] px-7 py-10 dark:from-secondary/10 dark:to-[hsl(from_hsl(var(--secondary))_calc(h-30)_calc(s+30)_calc(l-10)/15%)] max-md:pt-5 md:bg-gradient-to-r lg:px-20">
    <div class="w-full gap-x-7 gap-y-3 md:w-2/3 md:flex-nowrap md:justify-start lg:w-1/2">
        <h3 class="grow text-heading-foreground xl:pe-24">
            {{ trans('Upload product images and produce stunningly realistic photoshoots in seconds.') }}
        </h3>

        <div class="mt-6 flex flex-wrap gap-2">
            <x-button
                href="{{ route('dashboard.user.fashion-studio.photo_shoots.index') }}"
                variant="primary"
            >
                <x-tabler-plus class="size-4" />
                {{ trans('Create New Photoshoot') }}
            </x-button>

            <x-button
                href="{{ route('dashboard.user.fashion-studio.photo_shoots.my') }}"
                variant="ghost-shadow"
            >
                {{ trans('Browse Photoshoot') }}
            </x-button>
        </div>
    </div>

    <div class="max-md:-order-1 max-md:-scale-x-100 max-md:[mask-image:linear-gradient(to_top,transparent,black_40%)] md:ms-auto">
        <img
            class="h-full w-60 object-cover max-md:h-60 max-md:object-top md:absolute md:end-12 md:top-0 lg:end-24 lg:min-w-96 lg:object-[center_30%]"
            aria-hidden="true"
            src="{{ asset('vendor/fashion-studio/images/banner.png') }}"
            alt="{{ trans('Professional fashion model photoshoot') }}"
        />
    </div>
</div>
