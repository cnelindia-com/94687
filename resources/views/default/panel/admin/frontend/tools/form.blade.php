@extends('panel.layout.settings', ['disable_tblr' => true])
@section('title', $item != null ? __('Edit Tool') : __('Add New Tool'))
@section('titlebar_actions', '')

@section('settings')
    <form
        class="flex flex-col gap-5"
        id="item_form"
        onsubmit="return toolsCreateOrUpdate({{ $item != null ? $item->id : null }});"
        enctype="multipart/form-data"
    >
        <x-forms.input
            id="image"
            type="file"
            name="image"
            accept="image/*"
            label="{{ __('Image') }}"
            size="lg"
        />

        <x-forms.input
            id="image_url"
            label="{{ __('Image URL') }}"
            name="image_url"
            size="lg"
            value="{{ $item != null ? $item->image : null }}"
        />

        <x-forms.input
            id="title"
            label="{{ __('Title') }}"
            name="title"
            size="lg"
            required
            value="{{ $item != null ? $item->title : null }}"
        />

        <x-forms.input
            id="description"
            name="description"
            type="textarea"
            label="{{ __('Description') }}"
            rows="10"
            required
        >{{ $item != null ? $item->description : null }}</x-forms.input>

        <x-forms.input
            id="buy_link"
            label="{{ __('Buy Link Text') }}"
            name="buy_link"
            size="lg"
            value="{{ $item != null ? $item->buy_link : null }}"
        />

        <x-forms.input
            id="buy_link_url"
            label="{{ __('Buy Link URL') }}"
            name="buy_link_url"
            size="lg"
            value="{{ $item != null ? $item->buy_link_url : null }}"
        />

        <x-forms.input
    id="video_url"
    label="{{ __('Video URL') }}"
    name="video_url"
    size="lg"
    placeholder="https://youtube.com/... or https://vimeo.com/..."
    value="{{ $item != null ? $item->video_url : null }}"
/>

        <x-forms.input
            id="learn_more_link"
            label="{{ __('Learn More Link Text') }}"
            name="learn_more_link"
            size="lg"
            value="{{ $item != null ? $item->learn_more_link : null }}"
        />

        <x-forms.input
            id="learn_more_link_url"
            label="{{ __('Learn More Link URL') }}"
            name="learn_more_link_url"
            size="lg"
            value="{{ $item != null ? $item->learn_more_link_url : null }}"
        />

        <x-button
            id="item_button"
            size="lg"
            type="submit"
        >
            {{ __('Save') }}
        </x-button>
    </form>

    {{-- Video Modal --}}
    <div id="video_modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center;">
        <div style="position:relative; width:90%; max-width:900px; background:#000; border-radius:12px; overflow:hidden;">
            <button
                onclick="closeVideoModal()"
                style="position:absolute; top:10px; right:14px; background:none; border:none; color:#fff; font-size:28px; cursor:pointer; z-index:10;"
            >&times;</button>
            <div id="video_container" style="width:100%; aspect-ratio:16/9;">
                {{-- Video ya iframe yahan dynamically aayega --}}
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script src="{{ custom_theme_url('/assets/js/panel/settings.js') }}"></script>
    <script>
        // "See it in action" button se call hoga — frontend (tools listing) mein yeh function call karo
        function openVideoModal(url) {
            if (!url) return;

            const container = document.getElementById('video_container');
            const modal = document.getElementById('video_modal');

            // YouTube URL detect karo
            const youtubeMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
            // Vimeo URL detect karo
            const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);

            if (youtubeMatch) {
                container.innerHTML = `<iframe
                    src="https://www.youtube.com/embed/${youtubeMatch[1]}?autoplay=1"
                    style="width:100%;height:100%;border:none;"
                    allow="autoplay; fullscreen"
                    allowfullscreen
                ></iframe>`;
            } else if (vimeoMatch) {
                container.innerHTML = `<iframe
                    src="https://player.vimeo.com/video/${vimeoMatch[1]}?autoplay=1"
                    style="width:100%;height:100%;border:none;"
                    allow="autoplay; fullscreen"
                    allowfullscreen
                ></iframe>`;
            } else {
                // Direct video file (.mp4 etc.)
                container.innerHTML = `<video
                    src="${url}"
                    style="width:100%;height:100%;"
                    controls
                    autoplay
                ></video>`;
            }

            modal.style.display = 'flex';
        }

        function closeVideoModal() {
            document.getElementById('video_modal').style.display = 'none';
            document.getElementById('video_container').innerHTML = '';
        }

        // ESC key se modal band ho
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeVideoModal();
        });
    </script>
@endpush