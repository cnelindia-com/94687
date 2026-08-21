@php
    $tools = $tools ?? [];
    $colors = ['#E6FCFF', '#FFF1E6', '#ECE6FF', '#CCFFD9', '#FFE4DE'];
    $i = 0;

    // TODO: impelement a background color option for tool items
    foreach ($tools as $key => $item) {
        if (!isset($item['bg_color'])) {
            $tools[$key]['bg_color'] = $colors[$i] ?? '#E6FCFF';
        }
        $i++;
    }
@endphp

@php
function getEmbedUrl($url) {
    if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
        preg_match('/(youtu\.be\/|v=)([^&]+)/', $url, $matches);
        return isset($matches[2]) 
            ? 'https://www.youtube.com/embed/' . $matches[2] . '?autoplay=1&rel=0' 
            : $url;
    }

    if (str_contains($url, 'vimeo.com')) {
        $id = substr(parse_url($url, PHP_URL_PATH), 1);
        return 'https://player.vimeo.com/video/' . $id . '?autoplay=1';
    }

    return $url;
}
@endphp

 <style>
/* Section ka extra gap remove */
#tools {
    padding-top: 40px !important;
    padding-bottom: 40px !important;
}

/* Tool item ka unnecessary spacing kam karo */
.tool-item {
    padding-top: 40px !important;
    padding-bottom: 40px !important;
}

/* Card ka height control karo */
.tool-item-card {
    min-height: auto !important;
    padding-top: 30px !important;
    padding-bottom: 30px !important;
}

/* GSAP ka extra wrapper space remove */
/* .pin-spacer {
    height: auto !important;
    min-height: 0 !important;
    padding: 0 !important;
    margin: 0 !important;
} */

/* Nav wrap ke upar ka gap hatao */
.tools-nav-wrap {
    margin-bottom: 0 !important;
}

/* Extra safe fix */
.container {
    margin-top: 0 !important;
}

[id^="video_container_"] {
    width: 100% !important;
    
}

[id^="video_container_"] iframe,
[id^="video_container_"] video {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    border-radius: 20px;
    display: block;
}

[id^="video_container_"] {
    width: 100% !important;
    min-width: 100% !important;
    background-color: #f0f1ef !important;
}

[id^="video_container_"] iframe,
[id^="video_container_"] video {
    width: 100% !important;
    height: 100% !important;
    border-radius: 20px;
    display: block;
}

.video-wrapper {
     position: relative !important;
    overflow: hidden !important;
    border-radius: 20px !important;
    background-color: transparent !important;
}

.video-preloaded {
    position: absolute !important;
    top: 0;
    left: 0;
    width: 100% !important;
    height: 100% !important;
    opacity: 0;
    pointer-events: none;
    z-index: -1;
}

/* Jab dikhana ho */
.video-preloaded.active {
    opacity: 1;
    pointer-events: auto;
    z-index: 1;
}
</style>


{!! adsense_tools_728x90() !!}
<section
    class="site-section"
    id="tools"
    x-data="landingPageTools"
>
    <!-- <div
        class="tools-nav-wrap relative z-10 hidden lg:block"
        x-ref="toolsNavWrap"
        >
        <x-progressive-blur class="-top-8 h-auto lg:rounded-xl" />

        <div class="container">
            <div
                class="flex gap-3 py-8"
                x-ref="toolsNav"
            >
                @for ($i = 0; $i < count($tools); $i++)
                    <a
                        class="tool-nav-link relative inline-flex h-1 grow overflow-hidden rounded-full bg-black/10 before:absolute before:-inset-y-3 before:z-10 before:w-full"
                        href="#tool-item-{{ $i }}"
                        x-ref="itemProgressBar_{{ $i }}"
                    >
                        <span class="absolute inset-0 inline-block origin-left scale-x-0 bg-current"></span>
                    </a>
                @endfor
            </div>
        </div>
    </div> -->

    <div>
        @foreach ($tools as $item)
            @php
                $bg_from = $loop->first ? '#F9F9F9' : (isset($bg_to) ? $bg_to : $tools[$loop->index - 1]['bg_color']);
                $bg_via = $item['bg_color'];
                $bg_to = $loop->last ? '#F9F9F9' : $tools[$loop->index + 1]['bg_color'];

                // Get the image dimensions
                $imagePath = public_path(str_replace(url('/'), '', $item['image']));
                $imageSize = [0, 0];
                if (file_exists($imagePath)) {
                    $imageSize = getimagesize($imagePath);
                }
                $width = $imageSize[0] ?? 'auto';
                $height = $imageSize[1] ?? 'auto';
            @endphp

            <div
                class="tool-item relative grid grid-cols-1 place-items-center py-20"
                id="tool-item-{{ $loop->index }}"
                x-ref="item_{{ $loop->index }}"
            >
                <div
                    class="tool-item-bg absolute inset-0 z-0"
                    style="background: linear-gradient(to bottom, {{ $bg_from }} 0%, {{ $bg_via }}, {{ $bg_via }}, {{ $bg_to }} 100%)"
                ></div>

                <div class="container">
                    <div
                        class="tool-item-card relative z-1 grid min-h-[max(600px,40vh)] origin-top grid-cols-12 items-center gap-5 rounded-3xl p-7 max-md:place-content-start max-md:gap-y-10 sm:p-10 md:p-16"
                        style="background-color: {{ lightenColor($item['bg_color'], 40) }}"
                        x-ref="itemCard_{{ $loop->index }}"
                    >
                        <figure
                         class="col-start-1 col-end-12 md:col-end-6"
                        aria-hidden="true"
                          >
    <div class="video-wrapper relative w-full"
         style="position: relative; overflow: hidden; border-radius: 20px;">

    {{-- IMAGE --}}
    <img
        id="tool_image_{{ $loop->index }}"
        class="w-full rounded-[20px] shadow cursor-pointer"
        src="{{ custom_theme_url($item->image, true) }}"
        width="{{ $width }}"
        height="{{ $height }}"
        onclick="loadInlineVideo({{ $loop->index }}, '{{ !empty($item->video_url) ? $item->video_url : setting('video_url') }}')"
    >

    {{-- VIDEO --}}
    <div
    id="video_container_{{ $loop->index }}"
    data-video-url="{{ !empty($item->video_url) ? $item->video_url : setting('video_url') }}"
    style="display:none; border-radius:20px; overflow:hidden; width:100%;"
></div>
</div>
</figure>

                        <div class="col-span-12 col-start-1 md:col-span-6 md:col-start-7">
                            <h2 class="mb-5">
                                {!! __($item->title) !!}
                            </h2>
                            <p class="mb-12 opacity-80">
                                {!! __($item->description) !!}
                            </p>

                            {{-- TODO: need a button url option in backend --}}
                            <a
    class="flex items-center gap-2 text-base font-semibold -tracking-wide underline cursor-pointer transition-all duration-300 hover:gap-4 hover:opacity-70"
    
    @if(!empty($item->buy_link_url))
        href="{{ $item->buy_link_url }}"
        
    @else
        
    @endif
>
    {{ !empty($item->buy_link) ? $item->buy_link : __('See it in action') }}

    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
        <path d="M9.99997 0.387695C5.61613 0.387695 1.70509 3.53866 0.788054 7.82938C0.329894 9.97329 0.638534 12.2531 1.66069 14.193C2.64445 16.0599 4.25725 17.5688 6.18685 18.4239C8.19397 19.3136 10.4982 19.4687 12.6078 18.8619C14.643 18.2768 16.4476 16.9926 17.6761 15.2687C20.2449 11.6646 19.9014 6.60202 16.8793 3.37258C15.1081 1.47994 12.5925 0.387695 9.99997 0.387695ZM14.5189 10.311L11.9509 12.9409C11.3008 13.6069 10.2731 12.5979 10.9206 11.9351L12.2164 10.6081H6.07573C5.63965 10.6081 5.27581 10.244 5.27581 9.80817C5.27581 9.37233 5.63989 9.00825 6.07573 9.00825H12.1857L10.8642 7.68706C10.2076 7.03042 11.2257 6.0121 11.8823 6.66874L14.5129 9.29914C14.7918 9.57778 14.7945 10.029 14.5189 10.311Z"/>
    </svg>
</a>

                            
                        </div>
                    </div>
                </div>

                
            </div>
        @endforeach
    </div>
</section>

{{-- Video Modal --}}
<!-- <div id="video_modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center;">
    <div style="position:relative; width:90%; max-width:900px; background:#000; border-radius:12px; overflow:hidden;">
        <button
            onclick="closeVideoModal()"
            style="position:absolute; top:10px; right:14px; background:none; border:none; color:#fff; font-size:28px; cursor:pointer; z-index:10;"
        >&times;</button>
        <div id="video_container" style="width:100%; aspect-ratio:16/9;"></div>
    </div>
</div> -->

@push('script')
<script>
(() => {
    document.addEventListener('alpine:init', () => {
        Alpine.data('landingPageTools', () => ({
            toolItems: document.querySelectorAll('.tool-item'),
            toolCards: document.querySelectorAll('.tool-item-card'),

            init() {
                this.pinCards();
            },

           pinCards() {
    const total = this.toolCards.length;

    this.toolCards.forEach((card, index) => {
        const scaleAmount = index === total - 1
            ? 1
            : 1 - ((total - 1 - index) * 0.04);

        ScrollTrigger.create({
            trigger: card,
            endTrigger: '#tools',
            animation: gsap.to(card, { scale: scaleAmount }),
            scrub: 0.5,
            pin: true,
            pinSpacing: false,
            invalidateOnRefresh: true,
            start: 'top top+=70',      
            end: 'bottom top+=80',

            onEnter() {
                card.style.willChange = 'transform';
            },
            onLeave() {
                card.style.willChange = '';
            },
            onEnterBack() {
                card.style.willChange = 'transform';
            },
            onLeaveBack() {
                card.style.willChange = '';
            },
        });

        // ✅ 4th card ke liye  IntersectionObserver
        if (index === 3) {
           const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            autoPlayVideo(index);
        }
    });
}, { threshold: 0.1 });
observer.observe(card);
        }
    });
}
        }));
    });
})();

// ✅ Auto video play function
function autoPlayVideo(index) {
    const container = document.getElementById('video_container_' + index);
    if (!container) return;
    if (container.innerHTML.trim() !== '') return; // pehle se chal raha hai

    const url = container.getAttribute('data-video-url') || '';
    if (!url) return;

    loadInlineVideo(index, url);
}




// ✅ Video load function 
function loadInlineVideo(index, url) {
    if (!url || url.trim() === '') return;

    const container = document.getElementById('video_container_' + index);
    const image = document.getElementById('tool_image_' + index);
    if (!container || !image) return;

    const youtubeMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
    const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);

    let videoHTML = '';

    if (youtubeMatch) {
        videoHTML = `<iframe
            src="https://www.youtube.com/embed/${youtubeMatch[1]}?autoplay=1&controls=1&rel=0&mute=1&loop=1&playlist=${youtubeMatch[1]}"
            style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;border-radius:20px;"
            allow="autoplay; encrypted-media"
            allowfullscreen></iframe>`;
    } else if (vimeoMatch) {
        videoHTML = `<iframe
            src="https://player.vimeo.com/video/${vimeoMatch[1]}?autoplay=1&muted=1&loop=1"
            style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;border-radius:20px;"
            allow="autoplay; encrypted-media"
            allowfullscreen></iframe>`;
    } else if (url.match(/\.(mp4|webm|ogg)$/i)) {
        videoHTML = `<video
            src="${url}"
            style="position:absolute;top:0;left:0;width:100%;height:100%;border-radius:20px;object-fit:cover;"
            autoplay muted loop playsinline></video>`;
    } else {
        return;
    }

    
    const imgW = image.offsetWidth;
    const imgH = image.offsetHeight;

    // Image hide karo
image.style.display = 'none';

    // Container ko wrapper ke hisaab se size do — mobile pe bhi turant kaam karta hai
container.style.position = 'relative';
container.style.width = '100%';
container.style.aspectRatio = '16/9';
container.style.borderRadius = '20px';
container.style.overflow = 'hidden';
container.style.display = 'block';
container.style.backgroundColor = '#000';
container.classList.add('active');

container.innerHTML = videoHTML;

    // ✅ Image visibility:hidden karo - display:none nahi
    // Taaki wrapper ki space bani rahe
    image.style.visibility = 'hidden';
    image.style.position = 'relative';
}


document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('[id^="video_container_"]').forEach((container, i) => {
            container.innerHTML = '';
            container.style.display = 'none';
            const img = document.getElementById('tool_image_' + i);
            if (img) img.style.display = '';
        });
    }
});
</script>
@endpush

@push('script')
<script>
function loadInlineVideo(index, url) {
    if (!url) return;

    const container = document.getElementById('video_container_' + index);
    const image = document.getElementById('tool_image_' + index);
    if (!container || !image) return;

    const youtubeMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
    const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);

    let videoHTML = '';

    if (youtubeMatch) {
        videoHTML = `<iframe 
        src="https://www.youtube.com/embed/${youtubeMatch[1]}?autoplay=1&controls=1&rel=0&mute=1&loop=1&playlist=${youtubeMatch[1]}"
            style="width:100%;height:100%;border:none;" allow="autoplay; encrypted-media" allowfullscreen></iframe>`;
    } else if (vimeoMatch) {
        videoHTML = `<iframe 
        src="https://player.vimeo.com/video/${vimeoMatch[1]}?autoplay=1&muted=1&loop=1"
            style="width:100%;height:100%;border:none;" allow="autoplay; encrypted-media" allowfullscreen></iframe>`;
    } else if (url && url !== '') {
        videoHTML = `<video src="${url}" style="width:100%;height:100%;" 
        autoplay muted loop playsinline></video>`;
    } else {
        
        return;
    }

    const imgRect = image.getBoundingClientRect();
    container.style.width = '100%';
    container.style.height = imgRect.height + 'px';
    container.style.borderRadius = window.getComputedStyle(image).borderRadius;
    container.style.overflow = 'hidden';
    container.style.display = 'block';
    container.innerHTML = videoHTML;
    image.style.display = 'none';
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        
        document.querySelectorAll('[id^="video_container_"]').forEach((container, i) => {
            container.innerHTML = '';
            container.style.display = 'none';
            const img = document.getElementById('tool_image_' + i);
            if (img) img.style.display = '';
        });
    }
});
</script>
@endpush

@push('script')
    {{-- existing scripts --}}
    <script>

      function loadInlineVideo(index, url) {
    if (!url) return;

    const container = document.getElementById('video_container_' + index);
    const image = document.getElementById('tool_image_' + index);

    const youtubeMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
    const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);

    let videoHTML = '';

    if (youtubeMatch) {
        videoHTML = `<iframe 
        src="https://www.youtube.com/embed/${youtubeMatch[1]}?autoplay=1&controls=1&rel=0&mute=1&loop=1&playlist=${youtubeMatch[1]}"
            style="width:100%;height:100%;border:none;border-radius:20px;" allow="autoplay; encrypted-media"></iframe>`;
    } else if (vimeoMatch) {
        videoHTML = `<iframe 
        src="https://player.vimeo.com/video/${vimeoMatch[1]}?autoplay=1&muted=1&loop=1"
            style="width:100%;height:100%;border:none;border-radius:20px;" allow="autoplay; encrypted-media"></iframe>`;
    } else {
        videoHTML = `<video src="${url}" style="width:100%;height:100%;" 
        autoplay muted loop playsinline></video>`;
    }

  
    const imgRect = image.getBoundingClientRect();
    const parentRect = container.parentElement.getBoundingClientRect();

    
    container.style.width = '100%';
    container.style.height = imgRect.height + 'px';
    container.style.borderRadius = window.getComputedStyle(image).borderRadius;
    container.style.overflow = 'hidden';
    container.style.display = 'block';

    container.innerHTML = videoHTML;
    image.style.display = 'none';
}

        function openVideoModal(url) {
            if (!url) return;
            const container = document.getElementById('video_container');
            const modal = document.getElementById('video_modal');

            const youtubeMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
            const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);

            if (youtubeMatch) {
                container.innerHTML = `<iframe src="https://www.youtube.com/embed/${youtubeMatch[1]}?autoplay=1" style="width:100%;height:100%;border:none;" allow="autoplay; fullscreen" allowfullscreen></iframe>`;
            } else if (vimeoMatch) {
                container.innerHTML = `<iframe src="https://player.vimeo.com/video/${vimeoMatch[1]}?autoplay=1" style="width:100%;height:100%;border:none;" allow="autoplay; fullscreen" allowfullscreen></iframe>`;
            } else {
                container.innerHTML = `<video src="${url}" style="width:100%;height:100%;" controls autoplay></video>`;
            }

            modal.style.display = 'flex';
        }

        function closeVideoModal() {
            document.getElementById('video_modal').style.display = 'none';
            document.getElementById('video_container').innerHTML = '';
        }

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeVideoModal();
        });
    </script>
@endpush
