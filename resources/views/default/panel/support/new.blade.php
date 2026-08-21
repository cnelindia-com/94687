@php
    $categories = ['General Inquiry', 'Technical Issue', 'Improvement Idea', 'Feedback', 'Other'];
    $priorities = ['Low', 'Normal', 'High', 'Critical'];
@endphp

@extends('panel.layout.app')
@section('title', __('New Support Request'))
@section('titlebar_actions', '')
@section('titlebar_title', __('Create New Support Request'))
@section('titlebar_subtitle', __('Create new support request. We will answer as soon as possible.'))

@section('content')
    <div class="py-10">
        <form
            class="mx-auto flex w-full flex-wrap justify-between gap-y-5 lg:w-5/12"
            id="support_form"
            onsubmit="return sendSupportForm();"
            enctype="multipart/form-data"
        >
            @csrf
            <x-forms.input
                class:container="w-full md:w-[48%]"
                id="category"
                type="select"
                name="category"
                required
                label="{{ __('Support Category') }}"
                size="lg"
            >
                @foreach ($categories as $category)
                    <option
                        value="{{ $category }}"
                        @selected($loop->first)
                    >
                        {{ __($category) }}
                    </option>
                @endforeach
            </x-forms.input>
            <x-forms.input
                class:container="w-full md:w-[48%]"
                id="priority"
                name="priority"
                type="select"
                required
                label="{{ __('Support Priority') }}"
                size="lg"
            >
                @foreach ($priorities as $priority)
                    <option
                        value="{{ $priority }}"
                        @selected($loop->first)
                    >
                        {{ __($priority) }}
                    </option>
                @endforeach
            </x-forms.input>

            <x-forms.input
                class:container="w-full"
                id="subject"
                name="subject"
                placeholder="{{ __('Please enter subject of the support request') }}"
                required
                size="lg"
                label="{{ __('Subject') }}"
            />

            <x-forms.input
                class:container="w-full"
                id="message"
                name="message"
                rows="5"
                type="textarea"
                placeholder="{{ __('Please enter your message') }}"
                required
                size="lg"
                label="{{ __('Message') }}"
            />

            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('Attach Image (Optional)') }}
                </label>
                <div class="flex items-center gap-3">
                    <input
                        type="file"
                        id="attachment"
                        name="attachment"
                        accept="image/*"
                        class="hidden"
                        onchange="showFileName(this)"
                    />
                    <button
                        type="button"
                        onclick="document.getElementById('attachment').click()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-paperclip">
                            <path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                        </svg>
                        {{ __('Attachment') }}
                    </button>
                    <span id="file_name" class="text-sm text-gray-500"></span>
                </div>
            </div>

            <x-button
                class="w-full"
                id="support_button"
                size="lg"
                type="submit"
            >
                {{ __('Send') }}
            </x-button>
        </form>
    </div>
@endsection

@push('script')
    <script src="{{ custom_theme_url('/assets/js/panel/support.js') }}?v=2"></script>
    <script>
        function showFileName(input) {
            if (input.files && input.files[0]) {
                document.getElementById('file_name').textContent = input.files[0].name;
            } else {
                document.getElementById('file_name').textContent = '';
            }
        }
    </script>
@endpush
