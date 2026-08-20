<div class="grid grid-cols-2 gap-4 pt-10 lg:grid-cols-4 lg:gap-10">
    @foreach ($tools as $tool)
        <x-card
            class="hover:-translate-y-1"
            variant="none"
            size="none"
        >
            @if ($tool['image'])
                <img
                    class="mb-2 h-44 w-full rounded-lg object-cover object-center"
                    aria-hidden="true"
                    alt=""
                    src="{{ $tool['image'] }}"
                >
            @endif
            <h6 class="mb-0.5 text-4xs opacity-70">
                {{ $tool['description'] }}
            </h6>
            <h3 class="m-0 text-xs font-semibold opacity-90">
                {{ $tool['name'] }}
            </h3>

            <a
                class="absolute inset-0 z-1"
                href="{{ $tool['route'] }}"
            ></a>
        </x-card>
    @endforeach
</div>
