<div
    class="lqd-tabs-content {{ !$loop->first ? 'hidden' : '' }}"
    id="{{ \Illuminate\Support\Str::slug($item->menu_title) }}"
>
    <div class="grid grid-cols-1 place-items-center gap-7 text-white lg:grid-cols-2 lg:gap-12">
        <figure class="w-full rounded-[20px] bg-white/5 p-3 shadow-xl shadow-black/10">
            <img
                class="w-full rounded-lg"
                width="878"
                height="748"
                src="{{ custom_theme_url($item->image, true) }}"
                alt="{{ __($item->image_title) }}"
            >
        </figure>

        <div class="lg:pe-24">
            <h2 class="mb-6 text-current">
                {!! __($item->title) !!}
            </h2>

            <p class="text-lg/6 opacity-60">
                {!! __($item->text) !!}
            </p>
        </div>
    </div>
</div>
