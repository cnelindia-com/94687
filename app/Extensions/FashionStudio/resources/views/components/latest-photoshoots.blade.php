@php
    $videoProExtensionInstalled = \App\Helpers\Classes\MarketplaceHelper::isRegistered('ai-video-pro');
    $canCreateVideo = \App\Helpers\Classes\PlanHelper::planMenuCheck(
            \App\Helpers\Classes\PlanHelper::userPlan(),
            'ext_fashion_studio_create_video'
        );
@endphp

@push('css')
    <link
        rel="stylesheet"
        href="{{ custom_theme_url('/assets/libs/cropperjs/cropper.min.css') }}"
    />
    <style>
        .cropper-container {
            min-height: 100%;
        }
    </style>
@endpush

<div
    class="bg-background py-12"
    x-cloak
    x-data="myPhotoShootsComponent"
    @keyup.escape.window="isCropMode ? exitCropMode() : (modalShow = false)"
    @keyup.arrow-left.window="modalShow && !isCropMode && prevItem()"
    @keyup.arrow-right.window="modalShow && !isCropMode && nextItem()"
>
    <div>
        <div class="mb-5 flex flex-wrap items-center justify-between gap-4">
            <h3 class="m-0">
                {{ __('Latest Photoshoots') }}
            </h3>

            <x-button
                href="{{ route('dashboard.user.fashion-studio.photo_shoots.my') }}"
                variant="link"
            >
                {{ __('View All') }}
                <x-tabler-chevron-right class="size-4 transition group-hover:translate-x-1 rtl:rotate-180 rtl:group-hover:-translate-x-1" />
            </x-button>
        </div>

        <p
            class="flex items-center gap-1"
            x-show="loading"
        >
            <x-tabler-loader-2 class="size-4 animate-spin" />
            {{ __('Loading images') }}
        </p>

        {{-- Empty State --}}
        <div x-show="images.length === 0 && !loading">
            <p class="mb-4 opacity-60">
                {{ __('No photoshoots yet. Start creating your first photoshoot.') }}
            </p>
            <x-button
                href="{{ route('dashboard.user.fashion-studio.photo_shoots.index') }}"
                variant="primary"
            >
                <x-tabler-plus class="size-4" />
                {{ __('New Photoshoot') }}
            </x-button>
        </div>

        {{-- Gallery Grid --}}
        <div class="grid grid-cols-2 gap-1 empty:hidden md:grid-cols-3 lg:grid-cols-4">
            <template
                x-for="image in images"
                :key="image.id"
            >
                <div
                    class="image-result group relative aspect-square break-inside-avoid"
                    :data-id="image.id"
                    :class="{ 'grid place-items-center text-center border': image.is_processing, 'cursor-pointer': !image.is_processing }"
                >
                    <div class="relative size-full overflow-hidden">
                        {{-- Processing overlay for videos --}}
                        <template x-if="image.is_processing">
                            <div>
                                <span class="mx-auto inline-grid size-7 animate-spin place-items-center">
                                    {{-- blade-formatter-disable --}}
									<svg width="27" height="27" viewBox="0 0 27 27" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M13.3333 26.6667C5.98667 26.6667 0 20.68 0 13.3333C0 10.84 0.693333 8.41333 2 6.30667C2.38667 5.68 3.21333 5.49333 3.84 5.88C4.46667 6.26667 4.65333 7.09332 4.26667 7.71999C3.22667 9.39999 2.66667 11.3467 2.66667 13.3333C2.66667 19.2133 7.45333 24 13.3333 24C19.2133 24 24 19.2133 24 13.3333C24 7.45333 19.2133 2.66667 13.3333 2.66667C12.6 2.66667 12 2.06667 12 1.33333C12 0.6 12.6 0 13.3333 0C20.68 0 26.6667 5.98667 26.6667 13.3333C26.6667 20.68 20.68 26.6667 13.3333 26.6667Z" fill="url(#paint0_linear_12849_867)"/> <defs> <linearGradient id="paint0_linear_12849_867" x1="1.65222e-07" y1="5.44" x2="22.3733" y2="25.1733" gradientUnits="userSpaceOnUse"> <stop stop-color="hsl(var(--gradient-from))"/> <stop offset="0.502" stop-color="hsl(var(--gradient-via))"/> <stop offset="1" stop-color="hsl(var(--gradient-to))"/> </linearGradient> </defs> </svg>
									{{-- blade-formatter-enable --}}
                                </span>
                                <p class="m-0 bg-gradient-to-br from-gradient-from via-gradient-via to-gradient-to bg-clip-text text-sm font-semibold text-transparent">
                                    {{ __('In Progress') }}
                                </p>
                            </div>
                        </template>

                        <template x-if="image.is_video && !image.is_processing">
                            <video
                                class="size-full object-cover object-center"
                                :src="image.url"
                                :alt="image.title || image.input"
                                muted
                                loop
                                playsinline
                                @mouseenter="$el.play()"
                                @mouseleave="$el.pause(); $el.currentTime = 0;"
                                @click="setActiveItem({...image, output: image.url}); modalShow = true"
                            ></video>
                        </template>
                        <template x-if="!image.is_video && !image.is_processing">
                            <img
                                class="size-full object-cover object-center"
                                :src="image.thumbnail || image.url"
                                :alt="image.title || image.input"
                                loading="lazy"
                                @click="setActiveItem({...image, output: image.url}); modalShow = true"
                            >
                        </template>

                        {{-- Video indicator --}}
                        <template x-if="image.is_video && !image.is_processing">
                            <div class="absolute left-2 top-2 flex items-center gap-1 rounded-full bg-black/70 px-2 py-1 text-white">
                                <x-tabler-video class="size-4" />
                                <span class="text-2xs font-medium">{{ __('Video') }}</span>
                            </div>
                        </template>

                        {{-- Product thumbnails overlay --}}
                        <template x-if="image.parsedPayload?.product_urls?.length > 0 && !image.is_processing">
                            <div class="absolute bottom-3.5 start-3.5 flex gap-1 rounded-lg bg-background p-1 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                                <template
                                    x-for="(productUrl, idx) in image.parsedPayload.product_urls.slice(0, 3)"
                                    :key="idx"
                                >
                                    <img
                                        class="size-12 rounded object-cover"
                                        :src="productUrl"
                                        :alt="'Product ' + (idx + 1)"
                                    />
                                </template>
                                <template x-if="image.parsedPayload.product_urls.length > 3">
                                    <div class="inline-grid size-12 place-items-center rounded">
                                        <span x-text="`+${image.parsedPayload.product_urls.length - 3}`"></span>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Hover overlay --}}
                        <div
                            class="absolute right-0 top-0 px-3 py-4 opacity-0 transition-all duration-300 group-hover:opacity-100"
                            x-show="!image.is_processing"
                        >
                            <x-dropdown.dropdown
                                class:dropdown-dropdown="max-lg:end-0 max-lg:start-auto"
                                anchor="end"
                                :teleport="false"
                            >
                                <x-slot:trigger
                                    class="size-10"
                                >
                                    <x-tabler-dots-vertical class="size-8 rounded-full bg-white/90 p-1 text-black/70 hover:text-foreground" />
                                    <span class="sr-only">{{ __('Options') }}</span>
                                </x-slot:trigger>
                                <x-slot:dropdown
                                    class="min-w-[170px]"
                                >
                                    <ul class="py-1 text-xs font-medium">
                                        <li>
                                            <a
                                                class="text-heading-foreground/2 flex px-5 py-2 transition-colors hover:bg-heading-foreground/[3%]"
                                                href="javascript:void(0);"
                                                @click.prevent="makeAction(image.id, 'download'); toggle('collapse')"
                                            >
                                                <x-tabler-download class="me-2 size-5" />
                                                {{ __('Download') }}
                                            </a>
                                        </li>
                                        <template x-if="!image.is_video">
                                            <li>
                                                <a
                                                    class="text-heading-foreground/2 flex px-5 py-2 transition-colors hover:bg-heading-foreground/[3%]"
                                                    href="javascript:void(0);"
                                                    @click.prevent="makeAction(image.id, 'edit'); toggle('collapse')"
                                                >
                                                    <x-tabler-scissors class="me-2 size-5" />
                                                    {{ __('Edit') }}
                                                </a>
                                            </li>
                                        </template>
                                        @if ($canCreateVideo)
                                            <template x-if="!image.is_video">
                                                <li>
                                                    <a
                                                        class="text-heading-foreground/2 flex px-5 py-2 transition-colors hover:bg-heading-foreground/[3%]"
                                                        @click.prevent="makeAction(image.id, 'video'); toggle('collapse')"
                                                        href="javascript:void(0);"
                                                    >
                                                        <x-tabler-video class="me-2 size-5" />
                                                        {{ __('Create Video') }}
                                                    </a>
                                                </li>
                                            </template>
                                        @endif
                                        <li>
                                            <a
                                                class="text-heading-foreground/2 flex px-5 py-2 transition-colors hover:bg-heading-foreground/[3%]"
                                                href="javascript:void(0);"
                                                @click.prevent="makeAction(image.id, 'remove'); toggle('collapse')"
                                            >
                                                <x-tabler-circle-minus class="me-2 size-5" />
                                                {{ __('Remove') }}
                                            </a>
                                        </li>
                                    </ul>
                                </x-slot:dropdown>
                            </x-dropdown.dropdown>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Image Preview Modal --}}
        <div
            class="lqd-modal-img group/modal invisible fixed start-0 top-0 z-[999] grid h-screen w-screen place-items-center border px-5 opacity-0 [&.is-active]:visible [&.is-active]:opacity-100"
            id="modal_image"
            :class="{ 'is-active': modalShow }"
        >
            <div
                class="lqd-modal-img-backdrop absolute start-0 top-0 z-0 h-screen w-screen bg-black/50 opacity-0 transition-opacity group-[&.is-active]/modal:opacity-100"
                @click="isCropMode ? null : (modalShow = false)"
            ></div>

            <div class="lqd-modal-img-content-wrap relative z-10 w-full max-w-6xl">
                <div
                    class="lqd-modal-img-content relative flex max-h-[90vh] min-h-[min(90vh,570px)] w-full translate-y-2 scale-[0.985] flex-wrap justify-between overflow-y-auto overscroll-contain rounded-xl bg-background p-5 opacity-0 shadow-2xl transition-all group-[&.is-active]/modal:translate-y-0 group-[&.is-active]/modal:scale-100 group-[&.is-active]/modal:opacity-100">
                    {{-- Close button --}}
                    <a
                        class="absolute end-2 top-3 z-10 flex size-9 items-center justify-center rounded-full border bg-background text-inherit shadow-sm transition-all hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black"
                        @click.prevent="isCropMode ? exitCropMode() : (modalShow = false)"
                        href="javascript:void(0);"
                    >
                        <x-tabler-x class="size-4" />
                    </a>

                    {{-- Normal Preview Mode --}}
                    <template x-if="!isCropMode">
                        <figure class="lqd-modal-fig relative aspect-square min-h-[1px] w-full rounded-lg bg-cover bg-center max-md:min-h-[350px] md:w-6/12">
                            <template x-if="activeItem?.is_video">
                                <video
                                    class="lqd-modal-img mx-auto h-full w-auto object-cover object-center"
                                    :src="activeItem?.output"
                                    :alt="activeItem?.input"
                                    controls
                                    autoplay
                                    loop
                                ></video>
                            </template>
                            <template x-if="!activeItem?.is_video">
                                <img
                                    class="lqd-modal-img mx-auto h-full w-auto object-cover object-center"
                                    :src="activeItem?.output"
                                    :alt="activeItem?.input"
                                />
                            </template>
                        </figure>
                    </template>

                    {{-- Crop Mode --}}
                    <template x-if="isCropMode">
                        <div class="relative min-h-[1px] w-full max-md:min-h-[350px] md:w-6/12">
                            <div
                                class="relative h-full w-full overflow-hidden rounded-lg bg-foreground/5"
                                style="min-height: 400px;"
                            >
                                <img
                                    class="max-w-full"
                                    id="cropImage"
                                    :src="activeItem?.output"
                                    alt="Crop preview"
                                    style="display: block; max-height: 60vh;"
                                />
                            </div>
                        </div>
                    </template>

                    {{-- Sidebar - Normal Mode --}}
                    <template x-if="!isCropMode">
                        <div class="relative flex w-full flex-col p-3 md:w-5/12">
                            <div class="relative flex flex-col items-start pb-6">
                                <h3 class="mb-6">{{ __('Photoshoot Details') }}</h3>

                                {{-- Products --}}
                                <template x-if="activeItem?.parsedPayload?.product_urls?.length > 0">
                                    <div class="mb-6 flex w-full items-center justify-between gap-1">
                                        <p class="m-0 text-xs font-medium">
                                            {{ __('Style:') }}
                                        </p>
                                        <div class="flex gap-3">
                                            <template
                                                x-for="(productUrl, idx) in activeItem.parsedPayload.product_urls"
                                                :key="idx"
                                            >
                                                <img
                                                    class="size-16 rounded border object-cover object-center"
                                                    :src="productUrl"
                                                    :alt="'Product ' + (idx + 1)"
                                                />
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- Details List --}}
                                <div class="mb-6 w-full space-y-4">
                                    <template
                                        x-for="detail in getActiveItemDetails()"
                                        :key="detail.label"
                                    >
                                        <div class="flex items-center justify-between py-1.5">
                                            <span
                                                class="text-2xs"
                                                x-text="detail.label"
                                            ></span>
                                            <span
                                                class="text-2xs font-medium opacity-50"
                                                x-text="detail.value"
                                            ></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="mt-auto space-y-3">
                                <div class="grid grid-cols-3 gap-1.5">
                                    <template x-if="!activeItem?.is_video">
                                        <x-button
                                            class="text-xs font-medium"
                                            @click.prevent="makeAction(activeItem.id, 'edit')"
                                            variant="outline"
                                            size="lg"
                                        >
                                            {{ __('Edit') }}
                                        </x-button>
                                    </template>
                                    <template x-if="!activeItem?.is_video">
                                        <x-button
                                            class="text-xs font-medium"
                                            @click.prevent="enterCropMode()"
                                            variant="outline"
                                            size="lg"
                                        >
                                            {{ __('Crop') }}
                                        </x-button>
                                    </template>
                                    @if ($canCreateVideo)
                                        <template x-if="!activeItem?.is_video">
                                            <x-button
                                                class="text-xs font-medium"
                                                @click.prevent="makeAction(activeItem.id, 'video')"
                                                variant="outline"
                                                size="lg"
                                            >
                                                {{ __('Video') }}
                                            </x-button>
                                        </template>
                                    @endif
                                </div>

                                <x-button
                                    class="w-full text-xs font-medium"
                                    @click.prevent="makeAction(activeItem.id, 'download')"
                                    size="lg"
                                >
                                    {{ __('Download') }}
                                </x-button>
                            </div>
                        </div>
                    </template>

                    {{-- Sidebar - Crop Mode --}}
                    <template x-if="isCropMode">
                        <div class="relative flex w-full flex-col p-3 md:w-5/12">
                            <h3 class="mb-4">{{ __('Crop Image') }}</h3>

                            {{-- Aspect Ratio Buttons --}}
                            <div class="mb-6">
                                <p class="mb-3 text-xs font-medium">{{ __('Aspect Ratio') }}</p>
                                <div class="flex flex-wrap gap-2">
                                    <x-button
                                        class="text-xs font-medium [&.active]:bg-primary [&.active]:text-primary-foreground"
                                        type="button"
                                        variant="outline"
                                        ::class="{ 'active': !cropAspectRatio }"
                                        @click="setCropAspectRatio(null)"
                                    >
                                        {{ __('Free') }}
                                    </x-button>
                                    <x-button
                                        class="text-xs font-medium [&.active]:bg-primary [&.active]:text-primary-foreground"
                                        type="button"
                                        variant="outline"
                                        ::class="{ 'active': cropAspectRatio === 1 }"
                                        @click="setCropAspectRatio(1)"
                                    >
                                        1:1
                                    </x-button>
                                    <x-button
                                        class="text-xs font-medium [&.active]:bg-primary [&.active]:text-primary-foreground"
                                        type="button"
                                        variant="outline"
                                        ::class="{ 'active': cropAspectRatio === 4 / 3 }"
                                        @click="setCropAspectRatio(4/3)"
                                    >
                                        4:3
                                    </x-button>
                                    <x-button
                                        class="text-xs font-medium [&.active]:bg-primary [&.active]:text-primary-foreground"
                                        type="button"
                                        variant="outline"
                                        ::class="{ 'active': cropAspectRatio === 16 / 9 }"
                                        @click="setCropAspectRatio(16/9)"
                                    >
                                        16:9
                                    </x-button>
                                    <x-button
                                        class="text-xs font-medium [&.active]:bg-primary [&.active]:text-primary-foreground"
                                        type="button"
                                        variant="outline"
                                        ::class="{ 'active': cropAspectRatio === 3 / 4 }"
                                        @click="setCropAspectRatio(3/4)"
                                    >
                                        3:4
                                    </x-button>
                                    <x-button
                                        class="text-xs font-medium [&.active]:bg-primary [&.active]:text-primary-foreground"
                                        type="button"
                                        variant="outline"
                                        ::class="{ 'active': cropAspectRatio === 9 / 16 }"
                                        @click="setCropAspectRatio(9/16)"
                                    >
                                        9:16
                                    </x-button>
                                </div>
                            </div>

                            {{-- Crop Action Buttons --}}
                            <div class="mt-auto space-y-3">
                                <x-button
                                    class="w-full text-xs font-medium"
                                    type="button"
                                    variant="primary"
                                    size="lg"
                                    @click="saveCroppedImage()"
                                    ::disabled="cropSaving"
                                >
                                    <span x-show="!cropSaving">{{ __('Save Cropped Image') }}</span>
                                    <span
                                        class="flex items-center gap-2"
                                        x-show="cropSaving"
                                    >
                                        <x-tabler-loader-2 class="size-4 animate-spin" />
                                        {{ __('Saving...') }}
                                    </span>
                                </x-button>
                                <x-button
                                    class="w-full text-xs font-medium"
                                    type="button"
                                    variant="outline"
                                    size="lg"
                                    @click="exitCropMode()"
                                >
                                    {{ __('Cancel') }}
                                </x-button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Navigation buttons (hidden in crop mode) --}}
                <template x-if="!isCropMode">
                    <a
                        class="absolute -start-1 top-1/2 z-10 inline-flex size-9 -translate-y-1/2 items-center justify-center rounded-full bg-background text-inherit shadow-md transition-all hover:scale-110 hover:bg-primary hover:text-primary-foreground lg:-start-4"
                        href="javascript:void(0);"
                        @click.prevent="prevItem()"
                    >
                        <x-tabler-chevron-left class="size-5" />
                    </a>
                </template>

                <template x-if="!isCropMode">
                    <a
                        class="absolute -end-1 top-1/2 z-10 inline-flex size-9 -translate-y-1/2 items-center justify-center rounded-full bg-background text-inherit shadow-md transition-all hover:scale-110 hover:bg-primary hover:text-primary-foreground lg:-end-4"
                        href="javascript:void(0);"
                        @click.prevent="nextItem()"
                    >
                        <x-tabler-chevron-right class="size-5" />
                    </a>
                </template>
            </div>
        </div>
    </div>
</div>

<script src="{{ custom_theme_url('/assets/libs/cropperjs/cropper.min.js') }}"></script>
<script>
    const ROUTES = {
        loadImages: '{{ route('dashboard.user.fashion-studio.photo_shoots.images.load') }}',
        removeImage: '{{ route('dashboard.user.fashion-studio.photo_shoots.images.remove') }}',
        cropImage: '{{ route('dashboard.user.fashion-studio.photo_shoots.images.crop') }}',
        createVideo: '{{ route('dashboard.user.fashion-studio.create_video.index') }}',
        downloadImage: '{{ route('dashboard.user.fashion-studio.photo_shoots.images.download', ['id' => 0]) }}',
        csrfToken: '{{ csrf_token() }}'
    };

    const MESSAGES = {
        loadError: '{{ __('Failed to load images. Please try again.') }}',
        notFound: '{{ __('Image not found.') }}',
        editSoon: '{{ __('Edit functionality coming soon.') }}',
        cropSuccess: '{{ __('Image cropped successfully.') }}',
        cropFailed: '{{ __('Failed to crop image.') }}',
        removeConfirm: '{{ __('Are you sure you want to remove this image?') }}',
        removeSuccess: '{{ __('Image removed successfully.') }}',
        removeFailed: '{{ __('Failed to remove image.') }}',
        unknownAction: '{{ __('Unknown action.') }}'
    };

    function parsePayload(image) {
        if (!image?.payload) return {};
        if (typeof image.payload === 'object') return image.payload;

        try {
            return JSON.parse(image.payload);
        } catch (e) {
            console.error('Failed to parse payload:', e);
            return {};
        }
    }

    function myPhotoShootsComponent() {
        return {
            images: [],
            modalShow: false,
            activeItem: null,
            activeItemId: null,
            loading: true,

            // Crop state (inline mode)
            isCropMode: false,
            cropAspectRatio: null,
            cropSaving: false,
            cropper: null,

            init() {
                this.loadImages();
            },

            setActiveItem(data) {
                this.activeItem = data;
                this.activeItemId = data.id;
            },

            navigateItem(direction) {
                const currentIndex = this.images.findIndex(img => img.id === this.activeItemId);
                const newIndex = currentIndex + direction;

                if (newIndex >= 0 && newIndex < this.images.length) {
                    const newImage = this.images[newIndex];
                    this.setActiveItem({
                        ...newImage,
                        output: newImage.url
                    });
                }
            },

            prevItem() {
                this.navigateItem(-1);
            },

            nextItem() {
                this.navigateItem(1);
            },

            getActiveItemDetails() {
                if (!this.activeItem) return [];

                const details = [];
                const payload = this.activeItem.parsedPayload || {};

                details.push({
                    label: '{{ __('Date') }}',
                    value: this.activeItem.format_date || '{{ __('None') }}'
                });

                if (payload.model_name) details.push({
                    label: '{{ __('Model') }}',
                    value: payload.model_name
                });
                if (payload.pose_description) details.push({
                    label: '{{ __('Pose') }}',
                    value: payload.pose_description
                });
                if (payload.background_name) details.push({
                    label: '{{ __('Background') }}',
                    value: payload.background_name
                });
                if (payload.resolution) details.push({
                    label: '{{ __('Resolution') }}',
                    value: payload.resolution
                });

                // Show aspect_ratio (from cropping) if available, otherwise show ratio (from user settings)
                if (payload.aspect_ratio) {
                    const ratio = parseFloat(payload.aspect_ratio);
                    let ratioText = null;
                    if (Math.abs(ratio - 16 / 9) < 0.01) ratioText = '16:9';
                    else if (Math.abs(ratio - 9 / 16) < 0.01) ratioText = '9:16';
                    else if (Math.abs(ratio - 4 / 3) < 0.01) ratioText = '4:3';
                    else if (Math.abs(ratio - 3 / 4) < 0.01) ratioText = '3:4';
                    else if (Math.abs(ratio - 1) < 0.01) ratioText = '1:1';

                    if (ratioText) {
                        details.push({
                            label: '{{ __('Ratio') }}',
                            value: ratioText
                        });
                    } else if (payload.cropped_width && payload.cropped_height) {
                        details.push({
                            label: '{{ __('Ratio') }}',
                            value: `${Math.round(payload.cropped_width)}:${Math.round(payload.cropped_height)}`
                        });
                    }
                } else if (payload.ratio) {
                    details.push({
                        label: '{{ __('Ratio') }}',
                        value: payload.ratio
                    });
                }

                return details;
            },

            async loadImages() {
                this.loading = true;

                try {
                    const url = new URL(ROUTES.loadImages);
                    url.searchParams.set('page', '1');

                    const response = await fetch(url);
                    if (!response.ok) throw new Error('Failed to fetch images');

                    const data = await response.json();
                    const images = (data.images || data).map(img => ({
                        ...img,
                        parsedPayload: parsePayload(img)
                    }));

                    this.images = images;
                } catch (error) {
                    console.error('Failed to load images:', error);
                    this.showNotification(MESSAGES.loadError, 'error');
                } finally {
                    this.loading = false;
                }
            },

            async makeAction(imageId, action) {
                const image = this.images.find(img => img.id === imageId);
                if (!image) {
                    return this.showNotification(MESSAGES.notFound, 'error');
                }

                const actions = {
                    download: () => this.downloadImage(image),
                    edit: () => this.redirectToEdit(image),
                    video: () => this.redirectToVideo(image),
                    crop: () => this.openCropFromGrid(image),
                    remove: () => this.removeImage(imageId)
                };

                const actionFn = actions[action];
                if (actionFn) {
                    await actionFn();
                } else {
                    this.showNotification(MESSAGES.unknownAction, 'error');
                }
            },

            // Crop functionality - open from grid
            openCropFromGrid(image) {
                if (image.is_video) {
                    return this.showNotification('{{ __('Crop is only available for images.') }}', 'warning');
                }

                this.setActiveItem({
                    ...image,
                    output: image.url
                });
                this.modalShow = true;
                this.enterCropMode();
            },

            // Enter crop mode within the modal
            enterCropMode() {
                if (this.activeItem?.is_video) {
                    return this.showNotification('{{ __('Crop is only available for images.') }}', 'warning');
                }

                this.isCropMode = true;
                this.cropAspectRatio = null;

                // Initialize cropper after DOM update
                this.$nextTick(() => {
                    setTimeout(() => {
                        this.initCropper();
                    }, 100);
                });
            },

            exitCropMode() {
                this.isCropMode = false;
                this.cropAspectRatio = null;

                if (this.cropper) {
                    this.cropper.destroy();
                    this.cropper = null;
                }
            },

            initCropper() {
                const imageElement = document.getElementById('cropImage');
                if (!imageElement) return;

                // Destroy existing cropper
                if (this.cropper) {
                    this.cropper.destroy();
                    this.cropper = null;
                }

                this.cropper = new Cropper(imageElement, {
                    viewMode: 1,
                    dragMode: 'move',
                    aspectRatio: this.cropAspectRatio,
                    autoCropArea: 0.8,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                    responsive: true,
                });
            },

            setCropAspectRatio(ratio) {
                this.cropAspectRatio = ratio;
                if (this.cropper) {
                    this.cropper.setAspectRatio(ratio);
                }
            },

            async saveCroppedImage() {
                if (!this.cropper || !this.activeItemId) return;

                this.cropSaving = true;

                try {
                    // Get cropped canvas
                    const canvas = this.cropper.getCroppedCanvas({
                        maxWidth: 4096,
                        maxHeight: 4096,
                        fillColor: '#fff',
                        imageSmoothingEnabled: true,
                        imageSmoothingQuality: 'high',
                    });

                    if (!canvas) {
                        throw new Error('Failed to get cropped canvas');
                    }

                    // Calculate aspect ratio from actual canvas dimensions
                    const width = canvas.width;
                    const height = canvas.height;
                    const aspectRatio = width / height;

                    // Convert to base64
                    const imageData = canvas.toDataURL('image/png');

                    // Send to backend
                    const response = await fetch(ROUTES.cropImage, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': ROUTES.csrfToken
                        },
                        body: JSON.stringify({
                            image_id: this.activeItemId,
                            image_data: imageData,
                            width: width,
                            height: height,
                            aspect_ratio: aspectRatio
                        })
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.error || 'Failed to save cropped image');
                    }

                    // Add new cropped image to the list with updated payload
                    const newPayload = {
                        ...(this.activeItem.parsedPayload || {}),
                        cropped_width: result.width,
                        cropped_height: result.height,
                        aspect_ratio: result.aspect_ratio
                    };
                    const newImage = {
                        ...this.activeItem,
                        id: result.image_id,
                        url: result.url,
                        thumbnail: result.thumbnail || result.url,
                        output: result.url,
                        payload: newPayload,
                        parsedPayload: newPayload
                    };
                    this.images.unshift(newImage);

                    // Update active item to show the new cropped image
                    this.setActiveItem(newImage);

                    this.showNotification(MESSAGES.cropSuccess, 'success');
                    this.exitCropMode();
                } catch (error) {
                    console.error('Error saving cropped image:', error);
                    this.showNotification(MESSAGES.cropFailed, 'error');
                } finally {
                    this.cropSaving = false;
                }
            },

            redirectToVideo(image) {
                sessionStorage.setItem('createVideoData', JSON.stringify({
                    url: image.url,
                    id: image.id,
                    fileName: `image_${image.id}.jpg`
                }));

                window.location.href = '{{ route('dashboard.user.fashion-studio.create_video.index') }}/' + image.id;
            },

            redirectToEdit(image) {
                sessionStorage.setItem('editImageData', JSON.stringify({
                    url: image.url,
                    id: image.id,
                    fileName: `image_${image.id}.jpg`
                }));

                window.location.href = '{{ route('dashboard.user.fashion-studio.edit_image.index') }}';
            },

            downloadImage(image) {
                const link = document.createElement('a');
                link.href = image.url;
                const extension = image.is_video ? 'mp4' : 'jpg';
                link.download = `${image.is_video ? 'video' : 'image'}_${image.id}_${Date.now()}.${extension}`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },

            async removeImage(imageId) {
                if (!confirm(MESSAGES.removeConfirm)) return;

                try {
                    const response = await fetch(ROUTES.removeImage, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': ROUTES.csrfToken
                        },
                        body: JSON.stringify({
                            image_id: imageId
                        })
                    });

                    if (!response.ok) throw new Error('Failed to remove image');

                    this.images = this.images.filter(img => img.id !== imageId);

                    if (this.activeItemId === imageId) {
                        this.modalShow = false;
                    }

                    this.showNotification(MESSAGES.removeSuccess, 'success');
                } catch (error) {
                    console.error('Error removing image:', error);
                    this.showNotification(MESSAGES.removeFailed, 'error');
                }
            },

            showNotification(message, type = 'info') {
                window.toastr?.[type](message);
            }
        };
    }
</script>
