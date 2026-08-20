@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('Fashion Studio'))
@section('titlebar_pretitle', '')
@section('titlebar_actions')
<div class="flex items-center gap-4">
    <!-- Dark mode switch -->
    <x-light-dark-switch />
    
    <!-- User profile dropdown -->
    <x-user-dropdown />
</div>
@endsection
@section('titlebar_subtitle', __('Produce breathtakingly lifelike fashion photos and videos in just moments.'))

@section('content')
    <div
        class="py-10"
        x-data="{
            toolsSectionShowing: localStorage.getItem('fashionStudioAdvancedSettings') === null ?
                true : localStorage.getItem('fashionStudioAdvancedSettings') === 'true',

            toggleSettings() {
                this.toolsSectionShowing = !this.toolsSectionShowing;
                localStorage.setItem('fashionStudioAdvancedSettings', this.toolsSectionShowing);
            }
        }"
    >
        <div class="space-y-12">
            @include('fashion-studio::components.banner')

            <div>
                <x-button
                    class="w-full gap-9"
                    variant="link"
                    @click.prevent="toggleSettings()"
                >
                    <span class="h-px grow bg-foreground/10"></span>
                    <span class="flex items-center gap-2">
                        <span x-text="toolsSectionShowing ? '{{ __('Hide AI Tools') }}' : '{{ __('Show AI Tools') }}'"></span>
                        <x-tabler-chevron-up
                            class="size-4"
                            x-show="toolsSectionShowing"
                        />
                        <x-tabler-chevron-down
                            class="size-4"
                            x-show="!toolsSectionShowing"
                        />
                    </span>
                    <span class="h-px grow bg-foreground/10"></span>
                </x-button>

                <div
                    class="flex flex-col gap-6"
                    x-show="toolsSectionShowing"
                    x-collapse
                >
                    @include('fashion-studio::components.ai-tools', ['tools' => $tools])
                </div>
            </div>

            @include('fashion-studio::components.latest-photoshoots')
        </div>
    </div>
@endsection

@push('script')
@endpush

<style>
/* Titlebar styling - ensures all elements in one row */
.lqd-titlebar {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    width: 100%;
    flex-wrap: nowrap !important;
    gap: 1rem;
}

/* Left side - Fashion Studio text */
.lqd-titlebar-title {
    display: flex;
    align-items: center;
    flex-shrink: 0;
}

/* Title text styling */
.lqd-titlebar-title h1 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    white-space: nowrap;
}

/* Right side actions container - Dark mode + User profile */
.lqd-titlebar-actions {
    display: flex !important;
    align-items: center !important;
    gap: 1rem;
    flex-shrink: 0;
    margin-left: auto;
}

/* User dropdown styling */
.lqd-titlebar-actions .user-dropdown,
.lqd-titlebar-actions [class*="user-dropdown"] {
    flex-shrink: 0;
}

/* Dark mode switch styling */
.lqd-titlebar-actions x-light-dark-switch {
    display: flex;
    align-items: center;
    flex-shrink: 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .lqd-titlebar {
        flex-wrap: wrap !important;
        gap: 0.75rem;
    }
    
    .lqd-titlebar-actions {
        margin-left: 0;
    }
}

/* Ensure proper spacing between elements */
.lqd-titlebar-actions > * {
    margin-left: 0;
    margin-right: 0;
}

/* Optional: Add separator between dark mode and user profile */
.lqd-titlebar-actions x-light-dark-switch + x-user-dropdown {
    position: relative;
}

/* Optional: Add vertical separator */
.lqd-titlebar-actions x-light-dark-switch + x-user-dropdown::before {
    content: '';
    position: absolute;
    left: -0.5rem;
    top: 50%;
    transform: translateY(-50%);
    width: 1px;
    height: 24px;
    background-color: rgba(0, 0, 0, 0.1);
}

/* Dark mode separator color */
.dark .lqd-titlebar-actions x-light-dark-switch + x-user-dropdown::before {
    background-color: rgba(255, 255, 255, 0.1);
}
</style>