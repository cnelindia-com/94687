@php
    $items = [
        [
            'title' => __('Social Media Campaigns'),
            'description' => __('Create and launch stunning social media campaigns effortlessly with automated workflows.'),
            'image' => custom_theme_url('/assets/landing-page/img/tool-1.png'),
            'bg_color' => '#F6DDEF',
        ],
        [
            'title' => __('Trained on your Campaigns'),
            'description' => __('Train your chatbot using website, PDFs, and more to deliver information relevant to each campaign.'),
            'image' => custom_theme_url('/assets/landing-page/img/tool-2.png'),
            'bg_color' => '#1B1B1B',
        ],
        [
            'title' => __('Pmnichannel'),
            'description' => __('Send bulk messages across WhatsApp and Telegram from a single dashboard with unified inbox.'),
            'image' => custom_theme_url('/assets/landing-page/img/tool-3.png'),
            'bg_color' => '#CBF0FF',
        ],
    ];
@endphp

<section
    class="site-section py-36 transition-all duration-700 max-md:overflow-hidden max-sm:pb-24 max-sm:pt-32 md:opacity-0 [&.lqd-is-in-view]:opacity-100"
    id="marketing-tools"
>
    <div class="container">
        <div class="mx-auto w-full lg:w-8/12">
            <header class="relative mb-16 text-center">
                <h2 class="text-[45px] sm:text-[64px] xl:text-[76px]">
                    <x-text-reveal
                        :animate-from="['opacity' => 0, 'x' => '4']"
                        :animate-to="['opacity' => 1, 'x' => '0']"
                    >
                        {{ __('Supercharge your marketing strategy') }}
                    </x-text-reveal>
                </h2>

                <figure
                    class="motion-preset-oscillate absolute -start-28 bottom-0 hidden motion-duration-1500 lg:block"
                    aria-hidden="true"
                >
                    <img
                        data-lag="0.2"
                        src="{{ custom_theme_url('/assets/landing-page/img/whatsapp.png') }}"
                        width="105"
                        height="105"
                    >
                </figure>

                <figure
                    class="motion-preset-oscillate absolute -end-28 bottom-0 hidden motion-duration-1500 motion-delay-200 lg:block"
                    aria-hidden="true"
                >
                    <img
                        data-lag="0.3"
                        src="{{ custom_theme_url('/assets/landing-page/img/telegram.png') }}"
                        width="105"
                        height="105"
                    >
                </figure>
            </header>
        </div>

        <div class="mb-14 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3 lg:gap-0">
            @foreach ($items as $item)
                @php
                    $color = $item['bg_color'];
                    // Remove the # if it exists
                    $hex = ltrim($color, '#');

                    // Convert hex to RGB
                    $r = hexdec(substr($hex, 0, 2));
                    $g = hexdec(substr($hex, 2, 2));
                    $b = hexdec(substr($hex, 4, 2));

                    // Calculate perceived brightness (using the formula for relative luminance)
                    $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;

                    // Determine if the color is dark or light
                    $isDark = $brightness < 128;

                    // Get the image dimensions
                    $imagePath = public_path(str_replace(url('/'), '', $item['image']));
                    $imageSize = [0, 0];
                    if (file_exists($imagePath)) {
                        $imageSize = getimagesize($imagePath);
                    }
                    $width = $imageSize[0] ?? 'auto';
                    $height = $imageSize[1] ?? 'auto';
                @endphp
                <div
                    @class([
                        'group flex flex-col rounded-[36px] pt-12 pb-10 text-center relative lg:rotate-[--rotate]',
                        'text-white is-dark' => $isDark,
                        'is-light' => !$isDark,
                        'z-1' => $loop->index % 3 == 1,
                    ])
                    style="--rotate: {{ $loop->index % 3 == 0 ? '-4deg' : ($loop->index % 3 == 2 ? '4deg' : '0deg') }}; background-color: {{ $item['bg_color'] }}; transform-origin: {{ $loop->index % 3 == 0 ? 'right' : ($loop->index % 3 == 2 ? 'left' : 'center') }}"
                    x-data="{}"
                    x-init="ScrollTrigger.create({ trigger: $el, animation: gsap.from($el, { opacity: 0, rotate: {{ $loop->index % 3 == 0 ? '-10' : ($loop->index % 3 == 2 ? '10' : '0') }}, x: {{ $loop->index % 3 == 0 ? '-100' : ($loop->index % 3 == 2 ? '100' : '0') }}, y: {{ $loop->index % 3 == 1 ? '100' : '0' }} }), scrub: true, start: 'top bottom', 'end': 'center 65%' })"
                >
                    <h5 class="mb-5 border-b px-8 pb-5 text-current group-[&.is-dark]:border-white/15 group-[&.is-light]:border-black/10">
                        {{ $item['title'] }}
                    </h5>
                    <p class="mb-10 px-8 text-base leading-[1.4em]">
                        {{ $item['description'] }}
                    </p>
                    <figure
                        class="text-center"
                        aria-hidden="true"
                    >
                        <img
                            class="mx-auto"
                            src="{{ $item['image'] }}"
                            width="{{ $width / 2 }}"
                            height="{{ $height / 2 }}"
                        >
                    </figure>
                </div>
            @endforeach
        </div>

        <a
            class="flex items-center justify-center gap-2 text-center text-[12px] opacity-70 transition-all hover:opacity-100"
            href="#"
        >
            <x-tabler-info-circle class="size-5" />
            {{ __('Explore Entire MagicAI AI Suite ') }}
        </a>
    </div>
</section>
