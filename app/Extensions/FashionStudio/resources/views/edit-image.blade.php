@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('Fashion PlayGround'))
@section('titlebar_pretitle', '')
@section('titlebar_actions')
    <x-button
        href="{{ route('dashboard.user.fashion-studio.photo_shoots.my') }}"
        variant="ghost-shadow"
    >
        {{ __('My Photoshoots') }}
    </x-button>

    <x-button
        href="{{ route('dashboard.user.fashion-studio.photo_shoots.index') }}"
        variant="primary"
    >
        <x-tabler-plus class="size-4" />
        {{ __('New Photoshoot') }}
    </x-button>
@endsection
@section('titlebar_subtitle', __('Edit and creatively reimagine the image you have selected.'))

@section('content')
    <div
        class="py-10"
        x-data="editImageApp"
    >
        <div class="flex flex-col gap-4 lg:flex-row lg:gap-9">
            {{-- Left Panel: Upload Image and Prompt --}}
            <div class="w-full lg:basis-1/3">
                <p class="mb-5 border-b py-2.5 text-[12px] font-semibold transition-border">
                    {{ __('Fashion PlayGround') }}
                </p>

                <form
                    class="space-y-4 sm:space-y-6"
                    id="edit-image-form"
                    @submit.prevent="generateEditImage"
                >
                    @csrf

                    {{-- Upload Image --}}
                    <div>
                        <label class="mb-4 flex items-center gap-1 text-2xs font-medium">
                            {{ __('Upload Image') }}
                            <x-info-tooltip
                                class:content="-start-4 translate-x-0 text-2xs"
                                text="{{ __('Upload the image you want to edit.') }}"
                            />
                        </label>

                        <input
                            class="hidden"
                            type="file"
                            x-ref="imageInput"
                            accept="image/*,.heic,.heif"
                            @change="handleFileSelect($event)"
                        >

                        <div
                            class="cursor-pointer rounded-[10px] border border-dashed border-foreground/10 p-6 text-center transition hover:border-primary hover:bg-primary/5 sm:p-8"
                            @click="$refs.imageInput.click()"
                            @dragover.prevent="dragOver = true"
                            @dragleave.prevent="dragOver = false"
                            @drop.prevent="handleFileDrop($event)"
                            :class="{ 'drag-over': dragOver }"
                        >
                            <div x-show="!imagePreview && !convertingHeic">
                                <svg
                                    class="mx-auto mb-2.5 opacity-25"
                                    width="38"
                                    height="38"
                                    viewBox="0 0 38 38"
                                    fill="currentColor"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M32.4073 32.4839C28.7298 36.1613 24.2608 38 19 38C13.7392 38 9.24462 36.1613 5.51613 32.4839C1.83871 28.7554 0 24.2608 0 19C0 13.7392 1.83871 9.27016 5.51613 5.59274C9.24462 1.86425 13.7392 0 19 0C24.2608 0 28.7298 1.86425 32.4073 5.59274C36.1358 9.27016 38 13.7392 38 19C38 24.2608 36.1358 28.7554 32.4073 32.4839ZM29.8024 8.19758C26.8401 5.18414 23.2392 3.67742 19 3.67742C14.7608 3.67742 11.1344 5.18414 8.12097 8.19758C5.1586 11.1599 3.67742 14.7608 3.67742 19C3.67742 23.2392 5.1586 26.8656 8.12097 29.879C11.1344 32.8414 14.7608 34.3226 19 34.3226C23.2392 34.3226 26.8401 32.8414 29.8024 29.879C32.8159 26.8656 34.3226 23.2392 34.3226 19C34.3226 14.7608 32.8159 11.1599 29.8024 8.19758ZM20.5323 28.8065H17.4677C16.8548 28.8065 16.5484 28.5 16.5484 27.8871V22C16.5484 20.3431 15.2052 19 13.5484 19H11.4153C11.0067 19 10.7258 18.8212 10.5726 18.4637C10.4194 18.0551 10.4704 17.7231 10.7258 17.4677L18.3871 9.80645C18.7957 9.39785 19.2043 9.39785 19.6129 9.80645L27.2742 17.4677C27.5296 17.7231 27.5806 18.0551 27.4274 18.4637C27.2742 18.8212 26.9933 19 26.5847 19H24.4516C22.7948 19 21.4516 20.3431 21.4516 22V27.8871C21.4516 28.5 21.1452 28.8065 20.5323 28.8065Z"
                                    />
                                </svg>
                                <p class="mb-2 text-sm font-medium">
                                    {{ __('Drag and drop or click to browse') }}
                                </p>
                                <p class="m-0 text-4xs font-medium opacity-50">
                                    {{ __('Max File Size: 25mb') }}
                                </p>
                                <p class="mt-2 text-4xs font-medium">
                                    {{ __(' PNG, JPG or HEIC') }}
                                </p>
                            </div>

                            <div x-show="convertingHeic" x-cloak>
                                <div class="text-center py-4">
                                    <div class="mx-auto mb-3 w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center">
                                        <svg class="animate-spin text-blue-500" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <path d="M12 6v6l4 2"></path>
                                        </svg>
                                    </div>
                                   
                                    <p class="text-4xs opacity-60">Please wait</p>
                                </div>
                            </div>

                            <div
                                x-show="imagePreview && !convertingHeic"
                                x-cloak
                            >
                                <div class="relative mx-auto mb-2 w-full">
                                    <img
                                        class="max-h-[320px] w-full rounded-lg border object-contain bg-gray-50"
                                        :src="imagePreview"
                                    >
                                    <button
                                        class="absolute -end-4 -top-4 inline-grid size-8 place-items-center rounded-full bg-background text-foreground shadow-lg shadow-black/5 transition hover:scale-110 hover:bg-red-500 hover:text-white"
                                        type="button"
                                        @click.stop="resetFile()"
                                        title="{{ __('Remove Image') }}"
                                    >
                                        <x-tabler-x class="size-4" />
                                    </button>
                                </div>
                                <p
                                    class="m-0 text-4xs font-medium opacity-50"
                                    x-text="imageFileName"
                                ></p>
                                <p
                                    x-show="isHeicConverted"
                                    class="mt-1 text-4xs font-medium"
                                    
                                ></p>
                            </div>
                        </div>
                    </div>

                    {{-- Edit Prompt --}}
                    <x-forms.input
                        class:label="text-heading-foreground text-2xs font-medium"
                        label="{{ __('What would you like to change?') }}"
                        type="textarea"
                        x-model="editPrompt"
                        rows="4"
                        placeholder="{{ __('Explain your idea') }}"
                    />

                    <x-button
                        class="w-full"
                        id="generate-btn"
                        size="xl"
                        type="submit"
                        variant="primary"
                        disabled
                        ::disabled="generating || !imageFile || !editPrompt.trim()"
                    >
                        {{ __('Generate') }}
                    </x-button>
                </form>
            </div>

            {{-- Right Panel: Results --}}
            <div class="flex w-full flex-col lg:basis-2/3">
                <div class="mb-5 flex flex-wrap justify-between gap-3 border-b py-2.5 transition-border">
                    <p class="m-0 text-[12px] font-semibold">
                        {{ __('Generated Results') }}
                    </p>

                    <x-button
                        class="text-[12px] font-semibold"
                        variant="link"
                        @click="downloadAll"
                        x-show="results.length"
                        x-cloak
                    >
                        {{ __('Download All') }}
                        <x-tabler-download class="size-4" />
                    </x-button>
                </div>

                {{-- Empty State --}}
                <div
                    class="grid min-h-[250px] grow place-items-center rounded-[10px] border border-dashed border-foreground/10 p-8 text-center"
                    x-show="!results.length && !generating"
                >
                    <div x-show="!generating">
                        <svg class="mx-auto mb-5" width="28" height="26" viewBox="0 0 28 26" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M9.15053 21.5418L9.76695 21.4163C9.94061 21.3827 10.0972 21.2897 10.2097 21.1533C10.3223 21.0168 10.3839 20.8455 10.3839 20.6686C10.3839 20.4918 10.3223 20.3204 10.2097 20.184C10.0972 20.0475 9.94061 19.9546 9.76695 19.921L9.15053 19.7954C8.38982 19.641 7.69147 19.2659 7.14274 18.7169C6.594 18.1679 6.21921 17.4694 6.06519 16.7086L5.93962 16.0922C5.90603 15.9186 5.81303 15.762 5.6766 15.6495C5.54017 15.5369 5.36881 15.4754 5.19193 15.4754C5.01505 15.4754 4.84369 15.5369 4.70726 15.6495C4.57083 15.762 4.47783 15.9186 4.44424 16.0922L4.31867 16.7086C4.16456 17.4692 3.78971 18.1674 3.24096 18.7161C2.69222 19.2648 1.99392 19.6397 1.23333 19.7938L0.616915 19.9193C0.443257 19.9529 0.286707 20.0459 0.174137 20.1823C0.0615681 20.3188 0 20.4901 0 20.667C0 20.8439 0.0615681 21.0152 0.174137 21.1516C0.286707 21.2881 0.443257 21.381 0.616915 21.4146L1.23333 21.5402C1.9937 21.6942 2.69185 22.0688 3.24057 22.6172C3.78929 23.1656 4.16427 23.8635 4.31867 24.6237L4.44424 25.2401C4.47783 25.4138 4.57083 25.5703 4.70726 25.6829C4.84369 25.7954 5.01505 25.857 5.19193 25.857C5.36881 25.857 5.54017 25.7954 5.6766 25.6829C5.81303 25.5703 5.90603 25.4138 5.93962 25.2401L6.06519 24.6237C6.21979 23.8636 6.59483 23.1658 7.14352 22.6174C7.69221 22.069 8.39025 21.696 9.15053 21.5418Z" fill="url(#paint0_linear_19_3)"/> <path d="M24.0928 10.3752L26.3628 9.91531C26.5891 9.86897 26.7926 9.74588 26.9386 9.56687C27.0847 9.38786 27.1645 9.1639 27.1645 8.93285C27.1645 8.70181 27.0847 8.47785 26.9386 8.29884C26.7926 8.11982 26.5891 7.99674 26.3628 7.95039L24.0928 7.49382C22.9995 7.27197 21.9957 6.73301 21.2069 5.94421C20.4181 5.15541 19.8791 4.15173 19.6572 3.05848L19.1973 0.788628C19.1488 0.56523 19.0253 0.36517 18.8473 0.221703C18.6693 0.0782349 18.4475 0 18.2189 0C17.9903 0 17.7685 0.0782349 17.5905 0.221703C17.4125 0.36517 17.289 0.56523 17.2405 0.788628L16.7806 3.05848C16.5589 4.15182 16.02 5.15561 15.2311 5.94444C14.4423 6.73327 13.4384 7.27217 12.345 7.49382L10.075 7.95366C9.84867 8 9.64525 8.12308 9.49917 8.3021C9.35309 8.48111 9.27331 8.70507 9.27331 8.93612C9.27331 9.16716 9.35309 9.39112 9.49917 9.57013C9.64525 9.74915 9.84867 9.87223 10.075 9.91858L12.345 10.3784C13.4385 10.5999 14.4425 11.1387 15.2314 11.9276C16.0203 12.7164 16.5591 13.7203 16.7806 14.8138L17.2405 17.0836C17.289 17.307 17.4125 17.5071 17.5905 17.6505C17.7685 17.794 17.9903 17.8722 18.2189 17.8722C18.4475 17.8722 18.6693 17.794 18.8473 17.6505C19.0253 17.5071 19.1488 17.307 19.1973 17.0836L19.6572 14.8138C19.8791 13.7205 20.4181 12.7168 21.2069 11.928C21.9957 11.1392 22.9995 10.6003 24.0928 10.3784V10.3752Z" fill="url(#paint1_linear_19_3)"/> <defs> <linearGradient id="paint0_linear_19_3" x1="6.43368e-08" y1="17.5932" x2="8.71043" y2="25.2775" gradientUnits="userSpaceOnUse"> <stop stop-color="#82E2F4"/> <stop offset="0.502" stop-color="#8A8AED"/> <stop offset="1" stop-color="#6977DE"/> </linearGradient> <linearGradient id="paint1_linear_19_3" x1="9.27331" y1="3.64594" x2="24.2701" y2="16.8872" gradientUnits="userSpaceOnUse"> <stop stop-color="#82E2F4"/> <stop offset="0.502" stop-color="#8A8AED"/> <stop offset="1" stop-color="#6977DE"/> </linearGradient> </defs> </svg>
                        <p class="text-sm font-medium">
                            {{ __('Your images will appear here.') }}
                        </p>
                    </div>
                </div>

                <div
                    class="lqd-loading-skeleton lqd-is-loading grid grid-cols-2 gap-4"
                    x-cloak
                    x-show="generating"
                >
                    <div
                        class="aspect-[3/4] w-full rounded"
                        data-lqd-skeleton-el
                    ></div>
                    <div
                        class="aspect-[3/4] w-full rounded"
                        data-lqd-skeleton-el
                    ></div>
                </div>

                {{-- Results Grid --}}
                <div
                    class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2"
                    x-show="results.length"
                    x-cloak
                >
                    <template
                        x-for="(result, index) in results"
                        :key="index"
                    >
                        <div class="group relative">
                            <img
                                class="w-full rounded-lg"
                                :src="result.image_url"
                                :alt="'Generated ' + (index + 1)"
                            >
                            <div class="absolute inset-0 flex items-center justify-center rounded-lg bg-black/10 opacity-0 transition-opacity group-hover:opacity-100">
                                <x-button
                                    @click.prevent="downloadImage(result.image_url, index)"
                                >
                                    {{ __('Download') }}
                                </x-button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script src="https://cdn.jsdelivr.net/npm/heic-to@1.5.2/dist/iife/heic-to.js"></script>
<script>
    const ROUTES = {
        generate: '{{ route('dashboard.user.fashion-studio.edit_image.generate') }}',
        checkStatus: '{{ url('dashboard/user/fashion-studio/edit_image/status') }}',
        proxyImage: '{{ route('dashboard.user.fashion-studio.proxy.image') }}',
    };

    function editImageApp() {
        return {
            // File state
            imageFile: null,
            imagePreview: null,
            imageFileName: null,
            dragOver: false,
            convertingHeic: false,
            isHeicConverted: false,

            // Prompt state
            editPrompt: '',

            // Generation state
            generating: false,
            generationComplete: false,
            results: [],

            // Initialize - check for pre-loaded image
            init() {
                const editImageData = sessionStorage.getItem('editImageData');
                if (editImageData) {
                    try {
                        const data = JSON.parse(editImageData);
                        this.loadImageFromUrl(data.url, data.fileName);
                        sessionStorage.removeItem('editImageData');
                    } catch (error) {
                        console.error('Failed to load pre-selected image:', error);
                    }
                }
            },

            // Load image from URL
            async loadImageFromUrl(url, fileName) {
                try {
                    const response = await fetch(url);
                    const blob = await response.blob();
                    const file = new File([blob], fileName, {
                        type: blob.type
                    });

                    this.imageFile = file;
                    this.imagePreview = url;
                    this.imageFileName = fileName;
                } catch (error) {
                    console.error('Failed to fetch image:', error);
                    toastr.error("{{ __('Failed to load selected image') }}");
                }
            },

            // HEIC to JPG conversion function
             async convertHeicToJpg(file) {
    const fileExt = file.name.split('.').pop().toLowerCase();

    if (fileExt !== 'heic' && fileExt !== 'heif') {
        return file; // seedha File object
    }

    console.log('Converting HEIC file:', file.name);

    try {
        const jpegBlob = await HeicTo({
            blob: file,
            type: 'image/jpeg',
            quality: 0.9
        });

        const newFileName = file.name.replace(/\.(heic|heif)$/i, '.jpg');
        const jpegFile = new File([jpegBlob], newFileName, {
            type: 'image/jpeg',
            lastModified: Date.now()
        });

        console.log('Conversion successful:', newFileName);
        return jpegFile; // seedha File object

    } catch (error) {
        console.warn('HEIC conversion failed:', error?.message);
        // Yahan tag laga do taaki caller ko pata chale server-fallback chahiye
        file._needsServerConversion = true;
        return file; // original file, marked
    }
},

            handleFileSelect(event) {
                const file = event.target.files[0];
                if (file) {
                    const maxSize = 25 * 1024 * 1024; // 25 MB in bytes
                    
                    if (file.size > maxSize) {
                        toastr.error("{{ __('File size should not exceed 25 MB') }}");
                        event.target.value = "";
                        return;
                    }
                    
                    this.processFile(file);
                }
            },

            handleFileDrop(event) {
                this.dragOver = false;
                const file = event.dataTransfer.files[0];
                if (file) {
                    const maxSize = 25 * 1024 * 1024;
                    
                    if (file.size > maxSize) {
                        toastr.error("{{ __('File size should not exceed 25 MB') }}");
                        return;
                    }
                    
                    this.processFile(file);
                }
            },

            async processFile(file) {
                const ext = (file.name || '').split('.').pop().toLowerCase();
                const isHeic = ext === 'heif' || ext === 'heic';
                const isImage = file.type && file.type.startsWith('image/');

                if (!isImage && !isHeic) {
                    toastr.error("{{ __('Please upload an image file') }}");
                    return;
                }

                if (file.size > 25 * 1024 * 1024) {
                    toastr.error("{{ __('File size must be less than 25MB') }}");
                    return;
                }

                let finalFile = file;
                let previewSet = false;

                // Handle HEIC files
                if (isHeic) {
                    this.convertingHeic = true;
                    this.imagePreview = null;
                    
                    // Attempt 1: Client-side HEIC conversion via heic2any
                    // Attempt 1: Client-side HEIC conversion via heic-to library
try {
    if (typeof HeicTo !== 'undefined') {
        const jpegFile = await this.convertHeicToJpg(file);
        if (jpegFile !== file) {
            finalFile = jpegFile;
            this.isHeicConverted = true;
            
            const reader = new FileReader();
            await new Promise((resolve) => {
                reader.onload = (e) => {
                    this.imagePreview = e.target.result;
                    previewSet = true;
                    resolve();
                };
                reader.onerror = resolve;
                reader.readAsDataURL(jpegFile);
            });
        }
    } else {
        console.warn('HeicTo library not loaded');
    }
} catch (error) {
    console.warn('HEIC conversion failed:', error);
}
                    
                    this.convertingHeic = false;
                    
                    // If preview not set, show original file using object URL (browsers like Safari support HEIC natively)
                    if (!previewSet) {
                        try {
                            const objectUrl = URL.createObjectURL(file);
                            this.imagePreview = objectUrl;
                            this.imageFile = file;
                            this.imageFileName = file.name;
                            this.convertingHeic = false;
                            previewSet = true;
                            console.log('Showing HEIC file directly via object URL');
                        } catch (e) {
                            console.warn('Could not create object URL for HEIC:', e);
                        }
                    }
                    
                    // If still no preview, show a placeholder
                    if (!previewSet) {
                        this.imagePreview = this.buildHeicPlaceholderSvg(file.name);
                        this.imageFile = file;
                        this.imageFileName = file.name;
                        this.convertingHeic = false;
                        toastr.info("{{ __('HEIC preview not available, but file will upload correctly.') }}");
                        return;
                    }
                } else {
                    this.isHeicConverted = false;
                }

                if (!previewSet) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.imageFile = finalFile;
                        this.imagePreview = e.target.result;
                        this.imageFileName = finalFile.name;
                    };
                    reader.readAsDataURL(finalFile);
                } else {
                    this.imageFile = finalFile;
                    this.imageFileName = finalFile.name;
                }
            },

            buildHeicPlaceholderSvg(fileName) {
                const svg = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="160" height="160" viewBox="0 0 160 160">
                        <rect width="160" height="160" fill="#f3f4f6"/>
                        <path d="M50 50h60v60H50z" fill="none" stroke="#9ca3af" stroke-width="3"/>
                        <circle cx="65" cy="65" r="6" fill="#9ca3af"/>
                        <path d="M50 100l20-20 15 15 15-20 10 25" fill="none" stroke="#9ca3af" stroke-width="3"/>
                        <text x="80" y="135" font-size="11" text-anchor="middle" fill="#6b7280" font-family="sans-serif">HEIC</text>
                    </svg>`;
                return 'data:image/svg+xml;base64,' + btoa(svg);
            },

            resetFile() {
                this.imageFile = null;
                this.imagePreview = null;
                this.imageFileName = null;
                this.isHeicConverted = false;
                this.convertingHeic = false;
                if (this.$refs.imageInput) {
                    this.$refs.imageInput.value = '';
                }
            },

            // Generate edited image
            async generateEditImage() {
                if (!this.imageFile) {
                    toastr.warning("{{ __('Please upload an image') }}");
                    return;
                }

                if (!this.editPrompt.trim()) {
                    toastr.warning("{{ __('Please describe what you would like to change') }}");
                    return;
                }

                this.generating = true;
                this.generationComplete = false;

                const formData = new FormData();
                formData.append('image', this.imageFile);
                formData.append('prompt', this.editPrompt);
                formData.append('_token', document.querySelector('input[name="_token"]').value);

                try {
                    const response = await fetch(ROUTES.generate, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.checkGenerationStatus(data.id);
                    } else {
                        throw new Error(data.message || "{{ __('Generation failed') }}");
                    }
                } catch (error) {
                    console.error('Generation error:', error);
                    toastr.error(error.message || "{{ __('Failed to edit image') }}");
                    this.generating = false;
                }
            },

            // Check generation status
            checkGenerationStatus(id) {
                let isCompleted = false;

                const checkInterval = setInterval(async () => {
                    if (isCompleted) return;

                    try {
                        const response = await fetch(`${ROUTES.checkStatus}/${id}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (isCompleted) return;

                        if (data.status?.toLowerCase() === 'completed') {
                            isCompleted = true;
                            clearInterval(checkInterval);
                            this.generating = false;
                            this.generationComplete = true;
                            toastr.success("{{ __('Image edited successfully!') }}");

                            this.results = data.results || [data.edit_image];
                        } else if (data.status?.toLowerCase() === 'failed') {
                            isCompleted = true;
                            clearInterval(checkInterval);
                            this.generating = false;
                            toastr.error("{{ __('Generation failed. Please try again.') }}");
                        }
                    } catch (error) {
                        isCompleted = true;
                        clearInterval(checkInterval);
                        console.error('Status check error:', error);
                        this.generating = false;
                        toastr.error("{{ __('Failed to check generation status') }}");
                    }
                }, 3000);

                setTimeout(() => {
                    if (isCompleted) return;
                    isCompleted = true;
                    clearInterval(checkInterval);
                    if (this.generating) {
                        this.generating = false;
                        toastr.error("{{ __('Generation timeout. Please try again.') }}");
                    }
                }, 360000);
            },

            // Download single image using proxy route (handles S3/Spaces same-origin requirement)
            async downloadImage(url, index) {
                const proxyUrl = ROUTES.proxyImage + '?url=' + encodeURIComponent(url);
                // Use anchor element with download attribute on same-origin proxy URL
                const link = document.createElement('a');
                link.href = proxyUrl;
                link.download = `edited-image-${index + 1}.png`;
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },

            // Download all results
            async downloadAll() {
                for (let index = 0; index < this.results.length; index++) {
                    await this.downloadImage(this.results[index].image_url, index);
                }
            }
        };
    }
</script>
@endpush