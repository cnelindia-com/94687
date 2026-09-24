<footer class="site-footer relative border-t pb-16 pt-20">
    <div class="container">
        <div class="grid grid-cols-2 gap-8 lg:grid-cols-4">
            <div class="pt-0.5 max-sm:col-span-2 max-sm:mb-8">
                <ul class="flex flex-wrap gap-4">
                    @foreach (\App\Models\SocialMediaAccounts::where('is_active', true)->get() as $social)
                        <li>
                            <a
                                class="inline-flex items-center gap-2"
                                href="{{ $social['link'] }}"
                            >
                                <span class="w-6 [&_path]:fill-current [&_svg]:h-auto [&_svg]:w-full">
                                    {!! $social['icon'] !!}
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <p class="mb-7 text-xs font-semibold -tracking-wide">
                    {{ __('Integrations') }}
                </p>

                <ul class="flex flex-col gap-3 text-base font-normal">
                    @foreach (\App\Models\SocialMediaAccounts::where('is_active', true)->get() as $social)
                        <li>
                            <a
                                class="inline-flex items-center gap-2 underline underline-offset-[3px]"
                                href="{{ $social['link'] }}"
                            >
                                {!! $social['title'] !!}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

			<div>
				<p class="mb-7 text-xs font-semibold -tracking-wide">
					{{ __('Use Cases') }}
				</p>

				@php
					$defaultUseCaseLinks = [
						['link' => '#', 'title' => 'Collect Feedback'],
						['link' => '#', 'title' => 'Product Launches'],
						['link' => '#', 'title' => 'Promotions'],
						['link' => '#', 'title' => 'Limited Offers'],
						['link' => '#', 'title' => 'Reminders'],
						['link' => '#', 'title' => 'Events'],
						['link' => '#', 'title' => 'Bulk Messages'],
					];

					$useCaseLinks = json_decode(setting('footer_use_cases_links', ''), true);
					if (!is_array($useCaseLinks) || empty($useCaseLinks)) {
						$useCaseLinks = $defaultUseCaseLinks;
					}
				@endphp

				<ul class="flex flex-col gap-3 text-base font-normal">
					@foreach ($useCaseLinks as $link)
						<li>
							<a
								class="inline-flex items-center gap-2 underline underline-offset-[3px]"
								href="{{ $link['link'] ?? '#' }}"
							>
								{{ __($link['title'] ?? '') }}
							</a>
						</li>
					@endforeach
				</ul>
			</div>

			<div>
				<p class="mb-7 text-xs font-semibold -tracking-wide">
					{{ __('Resources') }}
				</p>

				@php
					$defaultResourceLinks = [
						['link' => '#', 'title' => 'Terms of Services'],
						['link' => '#', 'title' => 'Support'],
						['link' => '#', 'title' => 'Help Center'],
						['link' => '#', 'title' => 'Support'],
						['link' => '#', 'title' => 'Privacy Policy'],
						['link' => '#', 'title' => 'Blog'],
						['link' => '#', 'title' => 'Status'],
					];

					$resourceLinks = json_decode(setting('footer_resources_links', ''), true);
					if (!is_array($resourceLinks) || empty($resourceLinks)) {
						$resourceLinks = $defaultResourceLinks;
					}
				@endphp

				<ul class="flex flex-col gap-3 text-base font-normal">
					@foreach ($resourceLinks as $link)
						<li>
							<a
								class="inline-flex items-center gap-2 underline underline-offset-[3px]"
								href="{{ $link['link'] ?? '#' }}"
							>
								{{ __($link['title'] ?? '') }}
							</a>
						</li>
					@endforeach
				</ul>
			</div>
		</div>

        <div class="mt-16 text-center">
            @if (count(explode(',', $settings_two->languages)) > 1)
                <div class="group relative mx-auto mb-8 md:inline-flex">
                    <button
                        class="relative inline-flex items-center gap-3 underline underline-offset-[3px] transition-all before:absolute before:-top-5 before:bottom-full before:w-full group-hover:text-foreground"
                    >
                        <span
                            class="inline-flex size-10 items-center justify-center rounded-full border border-foreground/5 text-foreground/30 transition-all group-hover:text-foreground"
                        >
                            {{-- blade-formatter-disable --}}
							<svg class="relative z-1" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" > <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path> <path d="M3.6 9h16.8"></path> <path d="M3.6 15h16.8"></path> <path d="M11.5 3a17 17 0 0 0 0 18"></path> <path d="M12.5 3a17 17 0 0 1 0 18"></path> </svg>
							{{-- blade-formatter-enable --}}
                        </span>
                        {{ __('Languages') }}

                        <x-tabler-chevron-down class="size-4" />
                    </button>
                    <div
                        class="pointer-events-none absolute bottom-[calc(100%+1rem)] left-1/2 min-w-[145px] -translate-x-1/2 translate-y-2 rounded-md bg-white text-black opacity-0 shadow-lg transition-all group-hover:pointer-events-auto group-hover:translate-y-0 group-hover:opacity-100">
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

            <p class="mx-auto w-full text-center text-[12px] opacity-80 lg:w-9/12">
                {{ date('Y') . ' ' . $setting->site_name . '. ' . __($fSetting->footer_copyright) }}
            </p>
        </div>
    </div>
</footer>
