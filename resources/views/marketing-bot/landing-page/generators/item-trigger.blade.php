<button
    class="{{ $loop->first ? 'lqd-is-active' : '' }} group relative flex items-center gap-4 rounded-2xl bg-white/5 px-7 py-5 text-start text-xs font-semibold leading-none -tracking-wide text-white/60 transition-all lg:grow [&.lqd-is-active]:bg-black [&.lqd-is-active]:text-white"
    data-target="#{{ \Illuminate\Support\Str::slug($item->menu_title) }}"
    href="#{{ \Illuminate\Support\Str::slug($item->menu_title) }}"
>
    <x-outline-glow class="lqd-outline-glow-custom opacity-0 [--outline-glow-w:3px] group-[&.lqd-is-active]:opacity-100" />
    @if (filled($item->icon))
        <span class="[&_svg]:h-auto [&_svg]:w-6">
            {!! $item->icon !!}
        </span>
    @endif
    <span class="flex flex-col gap-1 tracking-normal">
        {!! __($item->menu_title) !!}
        <span class="text-[12px] font-normal opacity-50">
            {!! __($item->subtitle_one) !!}
        </span>
    </span>
</button>
