@extends('panel.layout.app', ['disable_tblr' => true])

@section('title', __('Tutorial Settings'))
@section('titlebar_actions')
    <x-button
        variant="primary"
        href="{{ route('dashboard.tutorial') }}"
    >
        {{ __('Back to Tutorial') }}
    </x-button>
@endsection

@section('content')
    <div class="pt-6">
        <x-card class="shadow-lg mb-8" size="lg">
            <div class="mb-4">
                <h4 class="text-2xl font-bold text-gray-800 mb-2">{{ __('Tutorial Settings') }}</h4>
                <p class="text-gray-600">{{ __('Super admin can manage unlimited tutorial videos with title, description, instructions, and YouTube/Vimeo embed code.') }}</p>
            </div>

            <form action="{{ route('dashboard.tutorial.upload') }}" method="POST" class="grid gap-4">
                @csrf
                <div class="grid gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">{{ __('Tutorial Page Title') }}</label>
                        <input
                            type="text"
                            name="tutorial_title"
                            value="{{ old('tutorial_title', $tutorialSettings->tutorial_title ?? '') }}"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3"
                            placeholder="{{ __('Enter tutorial page title') }}"
                        >
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">{{ __('Tutorial Page Subtitle') }}</label>
                        <input
                            type="text"
                            name="tutorial_subtitle"
                            value="{{ old('tutorial_subtitle', $tutorialSettings->tutorial_subtitle ?? '') }}"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3"
                            placeholder="{{ __('Enter tutorial page subtitle') }}"
                        >
                    </div>
                </div>

                <div id="tutorial-repeater" class="grid gap-4">
                    @foreach ($tutorials as $index => $tutorial)
                        <div class="tutorial-item rounded-2xl border border-gray-200 bg-white p-4 shadow-sm" data-index="{{ $index }}">
                            <div class="mb-4 flex items-center justify-between gap-3">
                                <h5 class="text-lg font-semibold text-gray-800">{{ __('Tutorial') }} {{ $tutorial['slot'] ?? $index + 1 }}</h5>
                                <button type="button" class="remove-tutorial rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                                    {{ __('Remove') }}
                                </button>
                            </div>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">{{ __('Slot') }}</label>
                                    <select name="tutorials[{{ $index }}][slot]" class="w-full rounded-lg border-gray-300 py-4 px-4">
                                        @for ($slot = 1; $slot <= 10; $slot++)
                                            <option value="{{ $slot }}" @selected((int) ($tutorial['slot'] ?? $index + 1) === $slot)>
                                                {{ __('Tutorial') }} {{ $slot }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">{{ __('Embed Code') }}</label>
                                    <textarea name="tutorials[{{ $index }}][embed_code]" rows="6" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3" placeholder="{{ __('Paste YouTube or Vimeo embed code') }}">{{ $tutorial['embed_code'] ?? '' }}</textarea>
                                </div>
                            </div>
                            <div class="mt-4">
                                <label class="mb-2 block text-sm font-medium text-gray-700">{{ __('Title') }}</label>
                                <input type="text" name="tutorials[{{ $index }}][title]" value="{{ $tutorial['title'] ?? '' }}" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-4 text-base shadow-sm focus:border-primary focus:ring-primary" placeholder="{{ __('Enter title') }}">
                            </div>
                            <div class="mt-4">
                                <label class="mb-2 block text-sm font-medium text-gray-700">{{ __('Short Description') }}</label>
                                <input type="text" name="tutorials[{{ $index }}][description]" value="{{ $tutorial['description'] ?? '' }}" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3" placeholder="{{ __('Enter short description') }}">
                            </div>
                            <div class="mt-4">
                                <label class="mb-2 block text-sm font-medium text-gray-700">{{ __('Enter Prompt ') }}</label>
                                <textarea name="tutorials[{{ $index }}][instructions]" rows="5" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3" placeholder="{{ __('Enter instructions') }}">{{ $tutorial['instructions'] ?? '' }}</textarea>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex gap-3">
                    <button type="button" id="add-tutorial" class="rounded-lg border border-gray-300 px-5 py-3 font-medium text-gray-700 hover:bg-gray-50">
                        {{ __('Add Another Video') }}
                    </button>
                    <button type="submit" class="rounded-lg bg-primary px-5 py-3 font-medium text-white">
                        {{ __('Save Tutorial') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>
@endsection

@push('script')
<script>
(() => {
    const repeater = document.getElementById('tutorial-repeater');
    const addButton = document.getElementById('add-tutorial');

    if (!repeater || !addButton) return;

    const createItem = (index) => `
        <div class="tutorial-item rounded-2xl border border-gray-200 bg-white p-4 shadow-sm" data-index="${index}">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h5 class="text-lg font-semibold text-gray-800">{{ __('Tutorial') }} ${index + 1}</h5>
                <button type="button" class="remove-tutorial rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                    {{ __('Remove') }}
                </button>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">{{ __('Slot') }}</label>
                    <select name="tutorials[${index}][slot]" class="w-full rounded-lg border-gray-300 py-4 px-4">
                        ${Array.from({ length: 10 }, (_, slotIndex) => {
                            const slot = slotIndex + 1;
                            return `<option value="${slot}">{{ __('Tutorial') }} ${slot}</option>`;
                        }).join('')}
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">{{ __('Video Type') }}</label>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">{{ __('Embed Code') }}</label>
                                    <textarea name="tutorials[${index}][embed_code]" rows="6" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3" placeholder="{{ __('Paste YouTube or Vimeo embed code') }}"></textarea>
                </div>
            </div>
            <div class="mt-4">
                <label class="mb-2 block text-sm font-medium text-gray-700">{{ __('Title') }}</label>
                <input type="text" name="tutorials[${index}][title]" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-4 text-base shadow-sm focus:border-primary focus:ring-primary" placeholder="{{ __('Enter title') }}">
            </div>
            <div class="mt-4">
                <label class="mb-2 block text-sm font-medium text-gray-700">{{ __('Short Description') }}</label>
                <input type="text" name="tutorials[${index}][description]" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3" placeholder="{{ __('Enter short description') }}">
            </div>
            <div class="mt-4">
                <label class="mb-2 block text-sm font-medium text-gray-700">{{ __('Instructions') }}</label>
                <textarea name="tutorials[${index}][instructions]" rows="5" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3" placeholder="{{ __('Enter instructions') }}"></textarea>
            </div>
        </div>
    `;

    addButton.addEventListener('click', () => {
        const index = repeater.querySelectorAll('.tutorial-item').length;
        repeater.insertAdjacentHTML('beforeend', createItem(index));
    });

    repeater.addEventListener('click', (event) => {
        const removeButton = event.target.closest('.remove-tutorial');
        if (!removeButton) return;

        const item = removeButton.closest('.tutorial-item');
        if (item) item.remove();
    });

})();
</script>
@endpush
