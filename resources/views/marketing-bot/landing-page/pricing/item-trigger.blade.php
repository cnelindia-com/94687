<button
    class="{{ isset($active) ? 'lqd-is-active' : '' }} rounded-[10px] px-8 py-2.5 text-xs font-semibold md:rounded-full [&.lqd-is-active]:bg-white [&.lqd-is-active]:text-black"
    data-target="{{ isset($target) ? $target : '' }}"
>
    {{ isset($label) ? $label : '' }}
</button>
