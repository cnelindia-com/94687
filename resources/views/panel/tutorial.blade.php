@extends('panel.layout.app', ['disable_tblr' => true])

@section('title', __('Tutorial'))
@section('titlebar_actions')
    @if (auth()->user()?->isSuperAdmin())
        <x-button
            variant="primary"
            href="{{ route('dashboard.tutorial.settings') }}"
        >
            {{ __('Setting') }}
        </x-button>
    @endif
    <x-button
        variant="primary"
        href="{{ route('dashboard.index') }}"
    >
        {{ __('Back to Dashboard') }}
    </x-button>
@endsection

@section('content')
    <div class="pt-6">
        <!-- Information Section - Top -->
        <x-card class="lqd-tutorial-info shadow-lg mb-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">
    {{ !empty($tutorialSettings->tutorial_title) ? $tutorialSettings->tutorial_title : __('How to Use Platform Features') }}
</h2>
<p class="text-gray-600">
    {{ !empty($tutorialSettings->tutorial_subtitle) ? $tutorialSettings->tutorial_subtitle : __('Complete guide to all platform features') }}
</p>
            </div>

            <!-- <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200 hover:shadow-lg transition-shadow">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white font-bold text-lg">
                                1
                            </span>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                {{ __('Registration & Login') }}
                            </h3>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-600 mt-1">•</span>
                                    <span>{{ __('User fills registration form with details') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-600 mt-1">•</span>
                                    <span>{{ __('Clicks register button to submit') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-600 mt-1">•</span>
                                    <span>{{ __('Receives verification email') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-600 mt-1">•</span>
                                    <span>{{ __('Verifies email by clicking link') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-600 mt-1">•</span>
                                    <span>{{ __('Redirected to login page') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-600 mt-1">•</span>
                                    <span>{{ __('Logs in with credentials') }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                
                <div class="p-6 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl border border-purple-200 hover:shadow-lg transition-shadow">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-purple-600 text-white font-bold text-lg">
                                2
                            </span>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                {{ __('Photoshoot & Image Creation') }}
                            </h3>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex items-start gap-2">
                                    <span class="text-purple-600 mt-1">•</span>
                                    <span>{{ __('Select pose, background, model, and product') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-purple-600 mt-1">•</span>
                                    <span>{{ __('Create images with selected parameters') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-purple-600 mt-1">•</span>
                                    <span>{{ __('Provide prompt to create videos from images') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-purple-600 mt-1">•</span>
                                    <span>{{ __('Recreate images using new prompts') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-purple-600 mt-1">•</span>
                                    <span>{{ __('Combine two images together') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-purple-600 mt-1">•</span>
                                    <span>{{ __('View all created images in My Photoshoot section') }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                
                <div class="p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-xl border border-green-200 hover:shadow-lg transition-shadow">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-green-600 text-white font-bold text-lg">
                                3
                            </span>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ __('Subscription Plans') }}
                            </h3>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex items-start gap-2">
                                    <span class="text-green-600 mt-1">•</span>
                                    <span>{{ __('Access features based on your plan') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-600 mt-1">•</span>
                                    <span>{{ __('Configure image creation settings per plan') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-600 mt-1">•</span>
                                    <span>{{ __('Select number of images to generate') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-600 mt-1">•</span>
                                    <span>{{ __('Choose resolution for image creation') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-600 mt-1">•</span>
                                    <span>{{ __('All settings depend on your active plan') }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                
                <div class="p-6 bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl border border-orange-200 hover:shadow-lg transition-shadow">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-orange-600 text-white font-bold text-lg">
                                4
                            </span>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                {{ __('Team Management') }}
                            </h3>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex items-start gap-2">
                                    <span class="text-orange-600 mt-1">•</span>
                                    <span>{{ __('Send team invitations to other users') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-orange-600 mt-1">•</span>
                                    <span>{{ __('Select role: Creator or Viewer') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-orange-600 mt-1">•</span>
                                    <span>{{ __('Creator role can manage and create content') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-orange-600 mt-1">•</span>
                                    <span>{{ __('Viewer role can only view content') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-orange-600 mt-1">•</span>
                                    <span>{{ __('Team owner can assign credits to team members') }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                
                <div class="p-6 bg-gradient-to-br from-red-50 to-red-100 rounded-xl border border-red-200 hover:shadow-lg transition-shadow">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-red-600 text-white font-bold text-lg">
                                5
                            </span>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                {{ __('Support System') }}
                            </h3>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex items-start gap-2">
                                    <span class="text-red-600 mt-1">•</span>
                                    <span>{{ __('Send messages to support team') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-red-600 mt-1">•</span>
                                    <span>{{ __('Chat with support in real-time') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-red-600 mt-1">•</span>
                                    <span>{{ __('Attach images with support messages') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-red-600 mt-1">•</span>
                                    <span>{{ __('Get help for any platform issues') }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                
                <div class="p-6 bg-gradient-to-br from-pink-50 to-pink-100 rounded-xl border border-pink-200 hover:shadow-lg transition-shadow">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-pink-600 text-white font-bold text-lg">
                                6
                            </span>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                </svg>
                                {{ __('My Wardrobe') }}
                            </h3>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex items-start gap-2">
                                    <span class="text-pink-600 mt-1">•</span>
                                    <span>{{ __('Upload your own images to wardrobe') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-pink-600 mt-1">•</span>
                                    <span>{{ __('Use uploaded images for creation') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-pink-600 mt-1">•</span>
                                    <span>{{ __('Access all wardrobe images anytime') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-pink-600 mt-1">•</span>
                                    <span>{{ __('Create new content from wardrobe images') }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div> -->

            <!-- Help Section -->
            <!-- <div class="mt-8 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h5 class="text-lg font-bold text-blue-900 mb-2">{{ __('Need more help?') }}</h5>
                        <p class="text-sm text-blue-700 mb-3">
                            {{ __('If you have questions or need assistance, please contact our support team.') }}
                        </p>
                        <a href="{{ route('dashboard.support.new') }}" class="inline-flex items-center gap-2 text-sm text-white bg-blue-600 hover:bg-blue-700 font-medium px-4 py-2 rounded-lg transition-colors">
                            {{ __('Contact Support') }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div> -->
        </x-card>

        

        <div class="space-y-6">
            @forelse ($tutorials as $tutorial)
                <x-card class="lqd-tutorial-video bg-gradient-to-br from-blue-50 to-indigo-50 shadow-lg" size="lg">
                    <div class="grid gap-6 lg:grid-cols-2">
                        <div class="aspect-video overflow-hidden rounded-xl bg-black shadow-2xl">
                            @if (!empty($tutorial['embed_html']))
                                <div class="h-full w-full">
                                    {!! $tutorial['embed_html'] !!}
                                </div>
                            @elseif (!empty($tutorial['video_url']))
                                <video
                                    id="tutorial-video-{{ $tutorial['slot'] }}"
                                    class="h-full w-full"
                                    controls
                                    muted
                                    playsinline
                                    preload="auto"
                                    poster="{{ asset('themes/default/assets/images/video-poster.jpg') }}"
                                >
                                    <source src="{{ $tutorial['video_url'] }}">
                                </video>
                            @else
                                <div class="flex h-full items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 p-8">
                                    <div class="text-center">
                                        <p class="text-lg font-medium text-gray-600">{{ __('No tutorial video uploaded yet') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col justify-between rounded-xl border border-blue-100 bg-white/70 p-5 shadow-sm">
                            <div>
                                <!-- <div class="mb-2 inline-flex rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                                    {{ __('Tutorial') }} {{ $tutorial['slot'] }}
                                </div> -->
                                <h4 class="text-2xl font-bold text-gray-800">{{ $tutorial['title'] }}</h4>
                                <p class="mt-3 text-gray-600">{{ $tutorial['description'] }}</p>
                            </div>

                            <details class="mt-6 rounded-lg border border-indigo-100 bg-indigo-50 px-4 py-3">
                                <summary class="cursor-pointer list-none font-medium text-indigo-700">{{ __(' Prompt +') }}</summary>
                                <div class="mt-3 text-sm leading-6 text-gray-700">
                                    {!! nl2br(e($tutorial['instructions'])) !!}
                                </div>
                            </details>
                        </div>
                    </div>
                </x-card>
            @empty
                <x-card class="shadow-lg" size="lg">
                    <div class="rounded-xl bg-gray-50 p-8 text-center text-gray-600">
                        {{ __('No tutorial videos uploaded yet.') }}
                    </div>
                </x-card>
            @endforelse
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function jumpToTime(seconds) {
        const video = document.querySelector('[id^="tutorial-video-"]');
        if (video && video.tagName === 'VIDEO') {
            video.currentTime = seconds;
        }
    }

    function jumpToYoutubeTime(seconds) {
        const iframe = document.querySelector('[id^="tutorial-video-"]');
        if (iframe && iframe.tagName === 'IFRAME') {
            const currentSrc = iframe.src;
            const baseUrl = currentSrc.split('?')[0];
            iframe.src = baseUrl + '?autoplay=1&mute=1&rel=0&start=' + seconds;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('video[id^="tutorial-video-"]').forEach(function(video) {
            const storageKey = 'tutorial-video-position-' + video.id;

            video.addEventListener('timeupdate', function() {
                localStorage.setItem(storageKey, video.currentTime);
            });

            const lastPosition = localStorage.getItem(storageKey);
            if (lastPosition) {
                video.currentTime = parseFloat(lastPosition);
            }

        });
    });
</script>
@endpush
