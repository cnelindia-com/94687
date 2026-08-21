@extends('panel.authentication.layout.app')
@section('title', __('Reset Password'))

@section('form')
    <h1 class="mb-[25px]">{{ __('Reset Password') }}</h1>
    <form
        class="flex flex-col gap-6"
        method="POST"
        action="{{ route('password.store') }}"
        novalidate="novalidate"
    >
        @csrf
        <input
            type="hidden"
            name="token"
            value="{{ $request->route('token') }}"
        >
        <input
            type="hidden"
            name="email"
            value="{{ $request->email }}"
        >
        <x-forms.input
            id="password"
            type="password"
            placeholder="{{ __('Your password') }}"
            label="{{ __('Password') }}"
            size="lg"
            required
            name="password"
        />
        <x-forms.input
            id="password_confirmation"
            type="password"
            placeholder="{{ __('Confirm your password') }}"
            label="{{ __('Confirm Password') }}"
            size="lg"
            required
            name="password_confirmation"
        />
        <x-button
            class="text-sm"
            id="PasswordResetFormButton"
            type="submit"
            tag="button"
        >
            {{ __('Reset Password') }}
        </x-button>
    </form>
@endsection
