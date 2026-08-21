@php
    $menu_items = app(App\Services\Common\FrontMenuService::class)->generate();
@endphp

<header
    @class([
        'site-header group/header absolute inset-x-0 top-0 z-50 transition-[background,shadow]',
    ])
    x-data="{
        windowScrollY: window.scrollY,
        navbarHeight: $refs.navbar.offsetHeight,
        get sections() {
            return [...document.querySelectorAll('.site-section')].map(section => {
                const rect = section.getBoundingClientRect();
                return {
                    el: section,
                    rect: {
                        top: rect.top + this.windowScrollY,
                        bottom: rect.bottom + this.windowScrollY,
                        height: rect.height,
                    },
                    isDark: section.getAttribute('data-color-scheme') === 'dark'
                }
            })
        },
        checkColorScheme: function() {
            const sectionBehindNavbar = this.sections.find(section => {
                return (
                    section.rect.top <= this.windowScrollY + this.navbarHeight &&
                    section.rect.bottom >= this.windowScrollY + this.navbarHeight
                );
            });
            if (sectionBehindNavbar) {
                $el.classList.toggle('is-dark', sectionBehindNavbar.isDark)
            }
        },
        checkScroll() {
            this.windowScrollY = window.scrollY;
        }
    }"
    x-init="checkColorScheme();"
    @scroll.window="checkScroll"
    @scroll.window.throttle.50ms="checkColorScheme"
    @scroll.window.debounce.100ms="checkColorScheme"
>
    @includeWhen($fSectSettings->preheader_active, 'landing-page.header.preheader')

    <nav
        class="site-header-nav relative z-10 py-[18px] transition-colors duration-500 group-[.lqd-is-sticky]/header:fixed group-[.lqd-is-sticky]/header:top-0 group-[.lqd-is-sticky]/header:w-full max-sm:py-3"
        id="frontend-local-navbar"
        x-ref="navbar"
        x-init="ScrollTrigger.create({ trigger: $el, pin: true, start: 'top top', end: 'max', pinSpacing: false })"
    >
        <div class="container">
            <div
                class="relative flex items-center justify-between rounded-xl p-4 shadow-[0_44px_44px_hsl(0_0%_0%/5%),inset_0_0_33px_hsl(0_0%_100%/30%)] transition-all duration-300 before:pointer-events-none before:absolute before:-inset-px before:rounded-[13px] before:bg-gradient-to-b before:from-white before:to-transparent before:p-px before:opacity-40 before:![mask-composite:exclude] before:[mask:linear-gradient(#fff_0_0)_content-box,linear-gradient(#fff_0_0)] group-[&.is-dark]/header:bg-black/10 group-[&.is-dark]/header:shadow-[0_44px_44px_hsl(0_0%_0%/5%),inset_0_0_33px_hsl(0_0%_0%/30%)] group-[&.is-dark]/header:before:from-white/30 max-xl:gap-10 max-lg:gap-5 lg:bg-white/10 lg:px-5 lg:py-4">

                <x-progressive-blur class="h-full rounded-xl group-[&.is-dark]/header:[--background:315_29%_3%]" />

                <a
                    class="site-logo relative shrink-0 grow"
                    href="{{ route('index') }}"
                >
                    @if (isset($setting->logo_dark))
                        <img
                            class="peer absolute start-0 top-1/2 -translate-y-1/2 opacity-0 transition-all duration-300 group-[.is-dark]/header:opacity-100"
                            src="{{ custom_theme_url($setting->logo_dark_path, true) }}"
                            @if (isset($setting->logo_dark_2x_path)) srcset="/{{ $setting->logo_dark_2x_path }} 2x" @endif
                            alt="{{ custom_theme_url($setting->site_name) }} logo"
                        >
                    @endif
                    <img
                        class="transition-all duration-300 group-[.is-dark]/header:peer-first:opacity-0"
                        src="{{ custom_theme_url($setting->logo_path, true) }}"
                        @if (isset($setting->logo_2x_path)) srcset="/{{ $setting->logo_2x_path }} 2x" @endif
                        alt="{{ $setting->site_name }} logo"
                    >
                </a>

                <div
                    class="site-nav-container grow text-sm font-semibold transition-all max-lg:absolute max-lg:start-0 max-lg:top-full max-lg:mt-2 max-lg:max-h-0 max-lg:w-full max-lg:overflow-hidden max-lg:rounded-xl max-lg:bg-white/5 max-lg:backdrop-blur-2xl max-lg:group-[&.is-dark]/header:hover:bg-black/5 lg:max-w-[60%] [&.lqd-is-active]:max-lg:max-h-[calc(100vh-150px)]">
                    <div class="max-lg:max-h-[inherit] max-lg:overflow-y-scroll max-lg:overscroll-contain max-lg:p-5">
                        <ul class="flex flex-col items-center justify-between gap-3 whitespace-nowrap text-center lg:flex-row lg:gap-5 xl:gap-10">
                            @php
                                $setting->menu_options = $setting->menu_options
                                    ? $setting->menu_options
                                    : '[{"title": "Home","url": "#banner","target": false},{"title": "Features","url": "#features","target": false},{"title": "How it Works","url": "#how-it-works","target": false},{"title": "Testimonials","url": "#testimonials","target": false},{"title": "Pricing","url": "#pricing","target": false},{"title": "FAQ","url": "#faq","target": false}]';
                                $menu_options = json_decode($setting->menu_options, true);
                            @endphp
                            @foreach ($menu_items as $menu_item)
                                @php
                                    $has_children = !empty($menu_item['mega_menu_id']);
                                @endphp
                                <li
                                    @class([
                                        'group/li relative flex w-full items-center justify-between gap-2 after:pointer-events-none after:absolute after:-inset-x-4 after:bottom-[calc(var(--sub-offset,0)*-1)] after:top-full max-lg:flex-wrap lg:justify-center [&.is-hover]:after:pointer-events-auto',
                                        'has-children' => $has_children,
                                        'has-mega-menu' => !empty($menu_item['mega_menu_id']),
                                    ])
                                    x-data="{ hover: false }"
                                    x-on:mouseover="if(window.innerWidth < 992 ) return; hover = true"
                                    x-on:mouseleave="if(window.innerWidth < 992 ) return; hover = false"
                                    :class="{ 'is-hover': hover }"
                                >
                                    <a
                                        class="group/link relative flex items-center justify-center gap-1.5 text-black/40 transition-colors hover:text-black group-[&.is-dark]/header:text-white/40 group-[&.is-dark]/header:hover:text-white max-lg:ps-5 [&.active]:text-black group-[&.is-dark]/header:[&.active]:text-white"
                                        href="{{ $menu_item['url'] }}"
                                        @if ($menu_item['target']) target="_blank" @endif
                                    >
                                        <svg
                                            class="pointer-events-none invisible absolute start-0 top-1/2 shrink-0 -translate-y-1/2 translate-x-[3px] rotate-45 opacity-0 transition-all group-hover/link:visible group-hover/link:translate-x-[-15px] group-hover/link:rotate-0 group-hover/link:opacity-100 group-[&.active]/link:visible group-[&.active]/link:translate-x-[-15px] group-[&.active]/link:rotate-0 group-[&.active]/link:opacity-100 max-lg:start-4"
                                            width="12"
                                            height="12"
                                            viewBox="0 0 13 12"
                                        >
                                            <use href="#menu-active-ind" />
                                        </svg>
                                        {{ __($menu_item['title']) }}
                                    </a>
                                    @if ($has_children)
                                        <span
                                            class="relative inline-grid size-8 shrink-0 place-content-center align-middle text-black before:absolute before:inset-0 before:rounded-xl before:bg-current before:opacity-5 group-[&.is-dark]/header:text-white lg:hidden"
                                            @click="hover = !hover"
                                        >
                                            <x-tabler-chevron-down class="size-4" />
                                        </span>
                                    @endif
                                    @if (!empty($menu_item['mega_menu_id']))
                                        @includeFirst(['mega-menu::partials.frontend-megamenu', 'vendor.empty'], ['menu_item' => $menu_item])
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        @if (count(explode(',', $settings_two->languages)) > 1)
                            <div class="group relative mt-4 block border-t border-black/5 py-5 group-[&.is-dark]/header:border-white/5 md:hidden">
                                <p class="mb-3 flex items-center gap-2 group-[&.is-dark]/header:text-white/50">
                                    {{-- blade-formatter-disable --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" > <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path> <path d="M3.6 9h16.8"></path> <path d="M3.6 15h16.8"></path> <path d="M11.5 3a17 17 0 0 0 0 18"></path> <path d="M12.5 3a17 17 0 0 1 0 18"></path> </svg>
									{{-- blade-formatter-enable --}}
                                    {{ __('Languages') }}
                                </p>
                                @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                    @if (in_array($localeCode, explode(',', $settings_two->languages)))
                                        <a
                                            class="block py-3 transition-colors group-[&.is-dark]/header:text-white/50"
                                            href="{{ route('language.change', $localeCode) }}"
                                            rel="alternate"
                                            hreflang="{{ $localeCode }}"
                                        >{{ country2flag(substr($properties['regional'], strrpos($properties['regional'], '_') + 1)) }}
                                            {{ $properties['native'] }}</a>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex grow justify-end gap-3">
                    @if (count(explode(',', $settings_two->languages)) > 1)
                        <div class="group relative hidden md:block">
                            <button
                                class="relative inline-flex size-11 items-center justify-center rounded-full border-[3px] border-foreground/5 text-foreground/30 transition-all before:absolute before:-bottom-5 before:top-full before:w-full hover:scale-105 hover:border-background hover:bg-background hover:text-foreground hover:shadow-lg hover:shadow-black/5 group-[&.is-dark]/header:border-white/5 group-[&.is-dark]/header:text-white/50 group-[&.is-dark]/header:hover:text-foreground"
                            >
                                {{-- blade-formatter-disable --}}
								<svg class="relative z-1" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" > <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path> <path d="M3.6 9h16.8"></path> <path d="M3.6 15h16.8"></path> <path d="M11.5 3a17 17 0 0 0 0 18"></path> <path d="M12.5 3a17 17 0 0 1 0 18"></path> </svg>
								{{-- blade-formatter-enable --}}
                            </button>
                            <div
                                class="pointer-events-none absolute end-0 top-[calc(100%+1rem)] min-w-[145px] translate-y-2 rounded-md bg-white text-black opacity-0 shadow-lg transition-all group-hover:pointer-events-auto group-hover:translate-y-0 group-hover:opacity-100">
                                @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                    @if (in_array($localeCode, explode(',', $settings_two->languages)))
                                        <a
                                            class="block border-b border-black border-opacity-5 px-3 py-3 transition-colors last:border-none hover:bg-black hover:bg-opacity-5"
                                            href="{{ route('language.change', $localeCode) }}"
                                            rel="alternate"
                                            hreflang="{{ $localeCode }}"
                                        >{{ country2flag(substr($properties['regional'], strrpos($properties['regional'], '_') + 1)) }}
                                            {{ $properties['native'] }}</a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @auth
                        <a
                            class="group relative inline-flex whitespace-nowrap rounded-full border-[3px] border-foreground/5 px-3.5 py-3 text-xs/none font-semibold text-foreground transition-all hover:scale-105 hover:border-background hover:bg-background hover:text-foreground hover:shadow-lg hover:shadow-black/5 group-[&.is-dark]/header:text-white group-[&.is-dark]/header:hover:text-foreground"
                            href="{{ route('dashboard.index') }}"
                        >
                            <x-outline-glow class="lqd-outline-glow-custom [--outline-glow-w:3px] group-hover:opacity-0" />
                            <span class="relative z-1">
                                {!! __('Dashboard') !!}
                            </span>
                        </a>
                    @else
                        <a
                            class="group relative hidden whitespace-nowrap rounded-full border-[3px] border-foreground/5 px-3.5 py-3 text-xs/none font-semibold text-foreground/40 transition-all hover:scale-105 hover:border-background hover:bg-background hover:text-foreground hover:shadow-lg hover:shadow-black/5 group-[&.is-dark]/header:border-white/5 group-[&.is-dark]/header:text-white group-[&.is-dark]/header:hover:text-foreground sm:inline-flex"
                            href="{{ route('login') }}"
                        >
                            <span class="relative z-1">
                                {!! __($fSetting->sign_in) !!}
                            </span>
                        </a>
                        <a
                            class="group relative inline-flex whitespace-nowrap rounded-full border-[3px] border-foreground/5 px-3.5 py-3 text-xs/none font-semibold text-foreground transition-all hover:scale-105 hover:border-background hover:bg-background hover:text-foreground hover:shadow-lg hover:shadow-black/5 group-[&.is-dark]/header:text-white group-[&.is-dark]/header:hover:text-foreground"
                            href="{{ route('register') }}"
                        >
                            <x-outline-glow class="lqd-outline-glow-custom [--outline-glow-w:3px] group-hover:opacity-0" />
                            <span class="relative z-1">
                                {!! __($fSetting->join_hub) !!}
                            </span>
                        </a>
                    @endauth

                    <button
                        class="mobile-nav-trigger group relative z-2 flex size-10 shrink-0 items-center justify-center rounded-full bg-black/10 group-[&.is-dark]/header:bg-white/10 lg:hidden"
                    >
                        <span class="flex w-4 flex-col gap-1">
                            @for ($i = 0; $i <= 1; $i++)
                                <span
                                    class="inline-flex h-[2px] w-full bg-heading-foreground transition-transform first:origin-left last:origin-right group-[.is-dark]/header:bg-white group-[.lqd-is-sticky]/header:bg-black group-[&.lqd-is-active]:first:-translate-y-[2px] group-[&.lqd-is-active]:first:translate-x-[3px] group-[&.lqd-is-active]:first:rotate-45 group-[&.lqd-is-active]:last:-translate-x-[2px] group-[&.lqd-is-active]:last:-translate-y-[8px] group-[&.lqd-is-active]:last:-rotate-45"
                                ></span>
                            @endfor
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    @includeWhen($fSetting->floating_button_active, 'landing-page.header.floating-button')
</header>

@includeWhen($app_is_demo, 'landing-page.header.envato-link')

@includeWhen(in_array($settings_two->chatbot_status, ['frontend', 'both']) &&
        ($settings_two->chatbot_login_require == false || ($settings_two->chatbot_login_require == true && auth()->check())),
    'panel.chatbot.widget',
    ['page' => 'landing-page']
)

<svg
    class="hidden"
    width="13"
    height="12"
    viewBox="0 0 13 12"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
>
    <path
        id="menu-active-ind"
        fill="currentColor"
        d="M6.19009 11.95C5.95767 10.6174 5.01249 8.94401 3.21509 7.59596C2.33189 6.92969 1.43319 6.49583 0.549988 6.30989V5.65911C2.3009 5.24075 4.02082 4.06315 5.12095 2.46719C5.67876 1.66146 6.03514 0.871223 6.19009 0.0499992H6.84087C7.10429 1.61497 8.31288 3.35039 9.95533 4.5125C10.7611 5.08581 11.5978 5.47318 12.45 5.65911V6.30989C10.7301 6.66628 8.73124 8.20026 7.73957 9.76523C7.24374 10.5555 6.94934 11.2837 6.84087 11.95H6.19009Z"
    />
</svg>
