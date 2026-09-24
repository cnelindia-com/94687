@extends('panel.layout.app', [
    'disable_tblr' => true,
    'has_sidebar' => true,
    'disable_titlebar' => true,
    'disable_suspended_banner' => true,
])
@section('title', __('Account Suspended'))
@section('titlebar_pretitle', '')
@section('titlebar_subtitle', '')
@section('titlebar_actions')
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <x-button type="submit" variant="danger">
            {{ __('Logout') }}
        </x-button>
    </form>
@endsection

@section('content')
    <div class="py-16">
        <div class="mx-auto w-full max-w-xl text-center">
            <div class="mb-6 inline-flex size-16 items-center justify-center rounded-full bg-amber-500/15 text-amber-600">
                <x-tabler-user-off class="size-8" />
            </div>
            <h2 class="mb-3 text-2xl font-semibold">{{ __('Account Suspended') }}</h2>
            <p class="mb-8 text-sm text-foreground/70">
                {{ __('Your account has been suspended. You can only access Support and Settings.') }}
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                <x-button href="{{ route('dashboard.support.list') }}" variant="primary">
                    {{ __('Go to Support') }}
                </x-button>
                <x-button href="{{ route('dashboard.user.fashion-studio.user_settings.index') }}" variant="secondary">
                    {{ __('Go to Settings') }}
                </x-button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-button type="submit" variant="danger">
                        {{ __('Logout') }}
                    </x-button>
                </form>
            </div>
        </div>
    </div>
@endsection

