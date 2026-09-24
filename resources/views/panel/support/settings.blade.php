@extends('panel.layout.app')

@section('title', __('Support Settings'))
@section('titlebar_pretitle', '')
@section('titlebar_actions')
    <a href="{{ route('dashboard.support.list') }}" class="btn btn-secondary">
        {{ __('Back to Support') }}
    </a>
@endsection
@section('titlebar_subtitle', __('Configure support team name and email address'))

@section('content')
    <div class="max-w-2xl">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('Support Team Settings') }}</h3>
                <!-- <p class="card-description">
                    {{ __('Set the name and email address that will be used when sending support messages to users.') }}
                </p> -->
            </div>

            <div class="card-body">
                <form action="{{ route('dashboard.support.settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('Support Team Name') }}
                            </label>
                            <input 
                                type="text" 
                                name="name" 
                                id="name" 
                                value="{{ old('name', $settings->name) }}"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="e.g., Support Team"
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">
                                {{ __('This name will be displayed when sending messages to users.') }}
                            </p>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('Support Email Address') }}
                            </label>
                            <input 
                                type="email" 
                                name="email" 
                                id="email" 
                                value="{{ old('email', $settings->email) }}"
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="e.g., support@example.com"
                                required
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">
                                {{ __('This email will be used as the sender address for support notifications.') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="btn btn-primary">
                            {{ __('Save Settings') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-6">
            <div class="card-header">
                <h3 class="card-title">{{ __('Current Settings') }}</h3>
            </div>
            <div class="card-body">
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('Name') }}:</span>
                        <span class="font-medium">{{ $settings->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('Email') }}:</span>
                        <span class="font-medium">{{ $settings->email }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection