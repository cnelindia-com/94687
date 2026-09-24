<section
    class="site-section relative pb-20 transition-all duration-700 sm:pb-16 md:translate-y-8 md:opacity-0 [&.lqd-is-in-view]:translate-y-0 [&.lqd-is-in-view]:opacity-100"
    id="clients"
>
    <div class="container">
        <p class="mx-auto mb-8 text-center text-base lg:w-1/2 lg:text-balance">
            {{ __('Join over 5,000 businesses that trust our AI messaging platform to connect with their customer.') }}
        </p>
        <div
            class="mx-auto flex items-center justify-center gap-20 max-lg:gap-12 max-md:flex-wrap max-sm:flex-wrap max-sm:gap-4 max-sm:gap-x-0 max-sm:gap-y-7 sm:justify-between lg:w-10/12">
            @foreach ($clients as $entry)
                <figure class="flex items-center justify-center max-sm:w-1/3 max-sm:text-center">
                    <img
                        class="transition-transform hover:scale-110"
                        src="{{ url('') . isset($entry->avatar) ? (str_starts_with($entry->avatar, 'asset') ? custom_theme_url($entry->avatar) : '/clientAvatar/' . $entry->avatar) : custom_theme_url('assets/img/auth/default-avatar.png') }}"
                        alt="{{ __($entry->alt) }}"
                        title="{{ __($entry->title) }}"
                    >
                </figure>
            @endforeach
        </div>
    </div>
</section>
