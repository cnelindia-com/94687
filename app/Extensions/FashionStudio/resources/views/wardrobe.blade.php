@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('My Wardrobe'))
@section('titlebar_pretitle', '')
@section('titlebar_actions')
    <x-button
        @click.prevent="closeUploadModal(); openCreateModal();"
        x-data="{}"
        variant="ghost-shadow"
    >
        {{ __('Create New Cloth Item') }}
    </x-button>

    <x-button
        @click.prevent="closeCreateModal(); openUploadModal();"
        x-data="{}"
        variant="primary"
    >
        <x-tabler-plus class="size-4" />
        {{ __('Upload to Wardrobe') }}
    </x-button>
@endsection
@section('titlebar_subtitle', __('Upload new product images or easily access your previously uploaded ones.'))

@section('content')
    <div
        class="py-10"
        x-data="{ gridSize: $persist(5).as('wardrobe_grid_size') }"
    >
        {{-- Filter Tabs --}}
        <div class="mb-8 flex flex-wrap items-center gap-x-3 gap-y-6 sm:flex-nowrap">
            <button
                class="filter-tab active rounded-full px-3 py-1.5 text-2xs font-medium leading-none transition [&.active]:bg-foreground/10"
                data-filter="all"
                type="button"
            >
                {{ __('All') }}
            </button>
            <button
                class="filter-tab rounded-full px-3 py-1.5 text-2xs font-medium leading-none transition [&.active]:bg-foreground/10"
                data-filter="uploaded"
            >
                {{ __('Uploaded') }}
            </button>
            <button
                class="filter-tab rounded-full px-3 py-1.5 text-2xs font-medium leading-none transition [&.active]:bg-foreground/10"
                data-filter="created"
            >
                {{ __('Created') }}
            </button>

            <div class="flex items-center gap-3 sm:ms-auto">
                <span class="whitespace-nowrap text-2xs font-medium sm:hidden">
                    {{ __('Grid Size') }}
                </span>
                <input
                    class="h-0.5 w-full appearance-none rounded-full bg-heading-foreground/10 [&::-moz-range-thumb]:size-2 [&::-moz-range-thumb]:appearance-none [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:border [&::-moz-range-thumb]:border-none [&::-moz-range-thumb]:border-heading-foreground [&::-moz-range-thumb]:bg-heading-foreground active:[&::-moz-range-thumb]:scale-110 [&::-webkit-slider-thumb]:size-2 [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:border [&::-webkit-slider-thumb]:border-none [&::-webkit-slider-thumb]:border-heading-foreground [&::-webkit-slider-thumb]:bg-heading-foreground active:[&::-webkit-slider-thumb]:scale-110"
                    type="range"
                    min="3"
                    max="6"
                    x-model="gridSize"
                >
            </div>
        </div>

        {{-- Loading State --}}
        <div
            class="flex items-center gap-1"
            id="wardrobe-loading"
        >
            <x-tabler-loader-2 class="size-4 animate-spin" />
            {{ __('Loading your wardrobe') }}
        </div>

        {{-- Wardrobe Grid --}}
        <div
            class="hidden gap-6 [&.show]:grid"
            id="wardrobe-grid"
            :class="{
                'grid-cols-3': gridSize == 3,
                'grid-cols-4': gridSize == 4,
                'grid-cols-5': gridSize == 5,
                'grid-cols-6': gridSize == 6
            }"
        >
            {{-- Products will be loaded here dynamically --}}
        </div>

        {{-- Empty State --}}
        <div
            class="hidden py-20 text-center"
            id="wardrobe-empty"
        >
            <span class="mx-auto mb-3 inline-grid size-28 place-items-center rounded-full bg-foreground/5">
                <x-tabler-hanger-off class="size-14" />
            </span>

            <h3 class="mb-2 text-xl">
                {{ __('No items were found') }}
            </h3>
            <p class="mb-0 text-xs opacity-60">
                {{ __('Upload or create an item to get started') }}
            </p>
            <x-button
                class="mt-6"
                onclick="openUploadModal()"
            >
                {{ __('Upload or Create an Item') }}
            </x-button>
        </div>
    </div>

    {{-- Upload Product Modal --}}
    <div
        class="lqd-modal-img group/modal invisible fixed start-0 top-0 z-[999] grid h-screen w-screen place-items-center px-5 opacity-0 [&.is-active]:visible [&.is-active]:opacity-100"
        id="upload-modal"
        x-data="{}"
    >
        <div
            class="lqd-modal-img-backdrop absolute start-0 top-0 z-0 h-screen w-screen bg-black/50"
            @click="closeUploadModal()"
        ></div>

        <div class="relative max-h-[90vh] w-[min(760px,100%)] overflow-hidden">
            <button
                class="absolute -end-3 -top-3 z-10 inline-grid size-10 place-items-center rounded-full bg-background shadow-lg transition lg:-end-12 lg:top-0"
                type="button"
                onclick="closeUploadModal()"
            >
                <x-tabler-x class="size-5" />
            </button>

            <div class="max-h-[90vh] overflow-y-auto rounded-xl bg-background p-6 shadow-xl">
                <div class="mb-5 flex gap-4 border-b">
                    <button
                        class="active -mb-px border-b border-transparent py-2.5 text-[12px] font-semibold opacity-50 transition hover:opacity-100 [&.active]:border-b-current [&.active]:opacity-100"
                        type="button"
                    >
                        {{ __('Upload Your Product') }}
                    </button>

                    <button
                        class="-mb-px border-b border-transparent py-2.5 text-[12px] font-semibold opacity-50 transition hover:opacity-100 [&.active]:border-b-current [&.active]:opacity-100"
                        type="button"
                        onclick="closeUploadModal(); openCreateModal();"
                    >
                        {{ __('Create a New Product') }}
                    </button>
                </div>

                <form
                    id="upload-form"
                    enctype="multipart/form-data"
                >
                    @csrf
                    <input
                        class="hidden"
                        id="product-upload"
                        type="file"
                        name="product_image"
                        accept="image/*,.heic,.heif"
                    >

                    <div
                        class="mb-4 cursor-pointer rounded-2xl border-2 border-dashed p-8 text-center transition-colors [&.drag-over]:border-primary/50 [&.drag-over]:bg-primary/5"
                        id="upload-area"
                        @click.stop="document.getElementById('product-upload').click()"
                    >
                        <div class="mx-auto mb-4 w-52">
                            <img
                                class="w-full drop-shadow-[0px_4px_14px_hsl(0_0%_0%/10%)]"
                                src="{{ asset('vendor/fashion-studio/images/product-modal.png') }}"
                                aria-hidden="true"
                                alt=""
                                width="443"
                                height="213"
                            >
                        </div>

                        <svg
                            class="mx-auto mb-4 text-foreground opacity-25"
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

                        <h3 class="mb-0">
                            @lang('Drag and Drop Image')
                        </h3>

                        <div class="mx-auto my-5 flex w-[min(100%,300px)] items-center gap-8 text-heading-foreground">
                            <span class="inline-flex h-px grow bg-current opacity-5"></span>
                            {{ __('or') }}
                            <span class="inline-flex h-px grow bg-current opacity-5"></span>
                        </div>

                        <x-button
                            class="mb-4 text-sm"
                            type="button"
                            variant="outline"
                            @click.stop="document.getElementById('product-upload').click()"
                            size="xl"
                        >
                            {{ __('Browse Files') }}
                        </x-button>

                        <ul class="list-inside list-disc text-[12px] opacity-50">
                            <li>{{ __('A well-lit flat lay image showcasing your product') }}</li>
                            <li>{{ __('Ensure good lighting without harsh shadows') }}</li>
                            <li>{{ __('PNG, JPG or HEIC (Max: 25Mb)') }}</li>
                        </ul>
                    </div>

                    <div
                        class="mb-4 hidden space-y-3"
                        id="upload-details"
                    >
                        <x-forms.input
                            class:label="text-xs font-medium text-heading-foreground"
                            size="lg"
                            label="{{ __('Product Name') }}"
                            name="product_name"
                            placeholder="{{ __(' e.g. Blue T-Shirt') }}"
                        />

                        <x-forms.input
                            class:label="text-xs font-medium text-heading-foreground"
                            size="lg"
                            type="select"
                            label="{{ __('Category') }}"
                            name="product_category"
                        >
                            <option value="other">{{ __('Other') }}</option>
                            <option value="tshirt">{{ __('T-Shirt') }}</option>
                            <option value="pants">{{ __('Pants/Shorts') }}</option>
                            <option value="shoes">{{ __('Shoes') }}</option>
                            <option value="accessories">{{ __('Accessories') }}</option>
                            <option value="hat">{{ __('Hat/Cap') }}</option>
                            <option value="glasses">{{ __('Glasses') }}</option>
                        </x-forms.input>
                    </div>

                    <x-button
                        class="w-full"
                        size="lg"
                        type="submit"
                        variant="secondary"
                    >
                        {{ __('Upload Product') }}
                        <span class="inline-grid size-7 place-items-center rounded-full bg-background text-foreground">
                            <x-tabler-chevron-right class="size-4" />
                        </span>
                    </x-button>
                </form>
            </div>
        </div>
    </div>

    {{-- Create Product Modal --}}
    <div
        class="fixed inset-0 z-[999] hidden items-center justify-center [&.is-active]:flex"
        id="create-modal"
        x-data="{}"
    >
        <div
            class="lqd-modal-img-backdrop absolute start-0 top-0 z-0 h-screen w-screen bg-black/50"
            @click="closeCreateModal()"
        ></div>

        <div class="relative w-[min(600px,100%)]">
            <button
                class="absolute -end-3 -top-3 z-10 inline-grid size-10 place-items-center rounded-full bg-background shadow-lg transition lg:-end-12 lg:top-0"
                type="button"
                onclick="closeCreateModal()"
            >
                <x-tabler-x class="size-5" />
            </button>

            <div class="relative max-h-[90vh] overflow-y-auto rounded-xl bg-background p-6 shadow-xl">
                <div class="mb-5 flex gap-4 border-b">
                    <button
                        class="-mb-px border-b border-transparent py-2.5 text-[12px] font-semibold opacity-50 transition hover:opacity-100 [&.active]:border-b-current [&.active]:opacity-100"
                        type="button"
                        onclick="closeCreateModal(); openUploadModal();"
                    >
                        {{ __('Upload Your Product') }}
                    </button>
                    <button
                        class="active -mb-px border-b border-transparent py-2.5 text-[12px] font-semibold opacity-50 transition hover:opacity-100 [&.active]:border-b-current [&.active]:opacity-100"
                        type="button"
                        onclick="closeUploadModal(); openCreateModal();"
                    >
                        {{ __('Create a New Product') }}
                    </button>
                </div>

                <form
                    class="flex flex-col gap-4"
                    id="create-form"
                >
                    @csrf

                    <x-forms.input
                        class:label="text-xs font-medium text-heading-foreground"
                        label="{{ __('Product Description') }}"
                        type="textarea"
                        name="product_description"
                        rows="8"
                        placeholder="{{ __('Describe the look of your product, such as a chic, retro-inspired dress for women.') }}"
                    />

                    <x-button
                        class="w-full"
                        variant="secondary"
                        size="lg"
                        type="submit"
                    >
                        {{ __('Create Product') }}
                        <span class="inline-grid size-7 place-items-center rounded-full bg-background text-foreground">
                            <x-tabler-chevron-right class="size-4" />
                        </span>
                    </x-button>
                </form>
            </div>
        </div>
    </div>

    {{-- Item Detail Modal --}}
    <div
        class="fixed inset-0 z-[999] hidden items-center justify-center px-5 [&.is-active]:flex"
        id="detail-modal"
        x-data="{}"
    >
        <div
            class="lqd-modal-img-backdrop absolute start-0 top-0 z-0 h-screen w-screen bg-black/50"
            @click="closeDetailModal()"
        ></div>

        <div class="relative w-[min(600px,100%)]">
            <button
                class="absolute -end-3 -top-3 z-10 inline-grid size-10 place-items-center rounded-full bg-background shadow-lg transition lg:-end-12 lg:top-0"
                type="button"
                onclick="closeDetailModal()"
            >
                <x-tabler-x class="size-5" />
            </button>

            <div class="relative max-h-[90vh] w-full overflow-y-auto rounded-xl bg-background p-6 shadow-xl">
                <div
                    class="space-y-4"
                    id="detail-content"
                >
                    {{-- Content will be loaded dynamically --}}
                </div>

                <div class="mt-6 flex gap-3">
                    <button
                        class="flex-1 rounded-lg bg-red-100 px-4 py-3 font-medium text-red-700 transition-colors hover:bg-red-200"
                        id="delete-item-btn"
                    >
                        {{ __('Delete Item') }}
                    </button>
                    <button
                        class="flex-1 rounded-lg bg-gray-100 px-4 py-3 font-medium text-gray-700 transition-colors hover:bg-gray-200"
                        onclick="closeDetailModal()"
                    >
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script src="https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js"></script>
<script>
const ROUTES = {
    wardrobe: '{{ route('dashboard.user.fashion-studio.wardrobe.load') }}',
    upload: '{{ route('dashboard.user.fashion-studio.wardrobe.upload') }}',
    create: '{{ route('dashboard.user.fashion-studio.wardrobe.create') }}',
    delete: '{{ url('dashboard/user/fashion-studio/wardrobe/delete') }}',
};

let allProducts = [];
let currentFilter = 'all';
let selectedUploadFile = null;

// ========== HEIC TO JPG CONVERSION FUNCTION ==========
async function convertHeicToJpg(file) {
    return new Promise((resolve, reject) => {
        const fileExt = file.name.split('.').pop().toLowerCase();
        
        if (fileExt !== 'heic' && fileExt !== 'heif') {
            resolve(file);
            return;
        }

        console.log('Converting HEIC file:', file.name);
        
        const uploadArea = document.getElementById('upload-area');
        const originalContent = uploadArea.innerHTML;
        
        // Show loading state
        uploadArea.innerHTML = `
            <div class="text-center py-8">
                <div class="mx-auto mb-4 w-16 h-16 rounded-full bg-blue-50 flex items-center justify-center">
                    <svg class="animate-spin text-blue-500" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 6v6l4 2"></path>
                    </svg>
                </div>
               
                <p class="text-2xs opacity-60">Please wait...</p>
            </div>
        `;

        heic2any({
            blob: file,
            toType: 'image/jpeg',
            quality: 0.9,
            multiple: false
        })
        .then((result) => {
            const jpegBlob = Array.isArray(result) ? result[0] : result;
            const newFileName = file.name.replace(/\.(heic|heif)$/i, '.jpg');
            const jpegFile = new File([jpegBlob], newFileName, {
                type: 'image/jpeg',
                lastModified: Date.now()
            });
            
            console.log('Conversion successful:', newFileName);
            
            // Create preview
            const reader = new FileReader();
            reader.onload = function(e) {
                uploadArea.innerHTML = `
                    <div class="text-center">
                        <div class="mb-4 rounded-lg overflow-hidden bg-gray-50">
                            <img src="${e.target.result}" alt="Preview" class="w-full h-auto max-h-56 object-cover mx-auto">
                        </div>
                        <div class="mb-2">
                            
                            <p class="text-sm font-medium mb-1">${newFileName}</p>
                            <p class="text-2xs opacity-50">${(jpegFile.size / 1024 / 1024).toFixed(2)} MB</p>
                        </div>
                        <button type="button" onclick="resetUploadArea()" 
                            class="inline-flex items-center gap-2 mt-3 px-4 py-2 text-sm text-blue-600 hover:underline">
                            Choose Different Image
                        </button>
                    </div>
                `;
            };
            reader.readAsDataURL(jpegFile);
            resolve(jpegFile);
        })
        .catch((error) => {
            console.error('Conversion error:', error);
            uploadArea.innerHTML = originalContent;
            reject(new Error('Failed to convert HEIC image. Please use JPG or PNG.'));
        });
    });
}

// Reset upload area function
window.resetUploadArea = function() {
    const fileInput = document.getElementById('product-upload');
    const uploadDetails = document.getElementById('upload-details');
    const uploadArea = document.getElementById('upload-area');
    
    if (fileInput) fileInput.value = '';
    if (uploadDetails) uploadDetails.classList.add('hidden');
    if (uploadArea) {
        uploadArea.innerHTML = `
            <div class="mx-auto mb-4 w-52">
                <img
                    class="w-full drop-shadow-[0px_4px_14px_hsl(0_0%_0%/10%)]"
                    src="{{ asset('vendor/fashion-studio/images/product-modal.png') }}"
                    aria-hidden="true"
                    alt=""
                    width="443"
                    height="213"
                >
            </div>
            <svg class="mx-auto mb-4 text-foreground opacity-25" width="38" height="38" viewBox="0 0 38 38" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M32.4073 32.4839C28.7298 36.1613 24.2608 38 19 38C13.7392 38 9.24462 36.1613 5.51613 32.4839C1.83871 28.7554 0 24.2608 0 19C0 13.7392 1.83871 9.27016 5.51613 5.59274C9.24462 1.86425 13.7392 0 19 0C24.2608 0 28.7298 1.86425 32.4073 5.59274C36.1358 9.27016 38 13.7392 38 19C38 24.2608 36.1358 28.7554 32.4073 32.4839ZM29.8024 8.19758C26.8401 5.18414 23.2392 3.67742 19 3.67742C14.7608 3.67742 11.1344 5.18414 8.12097 8.19758C5.1586 11.1599 3.67742 14.7608 3.67742 19C3.67742 23.2392 5.1586 26.8656 8.12097 29.879C11.1344 32.8414 14.7608 34.3226 19 34.3226C23.2392 34.3226 26.8401 32.8414 29.8024 29.879C32.8159 26.8656 34.3226 23.2392 34.3226 19C34.3226 14.7608 32.8159 11.1599 29.8024 8.19758ZM20.5323 28.8065H17.4677C16.8548 28.8065 16.5484 28.5 16.5484 27.8871V22C16.5484 20.3431 15.2052 19 13.5484 19H11.4153C11.0067 19 10.7258 18.8212 10.5726 18.4637C10.4194 18.0551 10.4704 17.7231 10.7258 17.4677L18.3871 9.80645C18.7957 9.39785 19.2043 9.39785 19.6129 9.80645L27.2742 17.4677C27.5296 17.7231 27.5806 18.0551 27.4274 18.4637C27.2742 18.8212 26.9933 19 26.5847 19H24.4516C22.7948 19 21.4516 20.3431 21.4516 22V27.8871C21.4516 28.5 21.1452 28.8065 20.5323 28.8065Z"/>
            </svg>
            <h3 class="mb-0">Drag and Drop Image</h3>
            <div class="mx-auto my-5 flex w-[min(100%,300px)] items-center gap-8 text-heading-foreground">
                <span class="inline-flex h-px grow bg-current opacity-5"></span>
                or
                <span class="inline-flex h-px grow bg-current opacity-5"></span>
            </div>
            <button type="button" class="lqd-btn group inline-flex items-center justify-center gap-1.5 font-medium rounded-button transition-all hover:-translate-y-0.5 hover:shadow-xl hover:shadow-black/5 disabled:bg-foreground/10 disabled:text-foreground/35 disabled:pointer-events-none lqd-btn-outline outline outline-button-border -outline-offset-1 focus-visible:outline-2 focus-visible:outline-offset-0 focus-visible:outline-secondary lqd-btn-xl px-5 py-4 lqd-btn-hover-none mb-4 text-sm" onclick="document.getElementById('product-upload').click()">
                Browse Files
            </button>
            <ul class="list-inside list-disc text-[12px] opacity-50">
                <li>A well-lit flat lay image showcasing your product</li>
                <li>Ensure good lighting without harsh shadows</li>
                <li>PNG, JPG or HEIC (Max: 25Mb)</li>
            </ul>
        `;
    }
    selectedUploadFile = null;
};

// File select handler
window.handleFileSelect = async function(file) {
    const uploadDetails = document.getElementById('upload-details');
    
    if (!file) return;
    
    const isHeic = file.name.match(/\.(heic|heif)$/i);
    
    if (isHeic) {
        try {
            const convertedFile = await convertHeicToJpg(file);
            selectedUploadFile = convertedFile;
            if (uploadDetails) uploadDetails.classList.remove('hidden');
            if (typeof toastr !== 'undefined') {
                
            }
        } catch (error) {
            if (typeof toastr !== 'undefined') {
                toastr.error(error.message);
            }
        }
    } else {
        const uploadArea = document.getElementById('upload-area');
        const reader = new FileReader();
        reader.onload = function(e) {
            if (uploadArea) {
                uploadArea.innerHTML = `
                    <div class="text-center">
                        <div class="mb-4 rounded-lg overflow-hidden bg-gray-50">
                            <img src="${e.target.result}" alt="Preview" class="w-full h-auto max-h-56 object-cover mx-auto">
                        </div>
                        <p class="text-sm font-medium mb-1">${file.name}</p>
                        <p class="text-2xs opacity-50 mb-3">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                        <button type="button" onclick="resetUploadArea()" 
                            class="inline-flex items-center gap-2 mt-3 px-4 py-2 text-sm text-blue-600 hover:underline">
                            Choose Different Image
                        </button>
                    </div>
                `;
            }
            if (uploadDetails) uploadDetails.classList.remove('hidden');
            selectedUploadFile = file;
        };
        reader.readAsDataURL(file);
    }
};

// Modal functions
function openUploadModal() {
    document.getElementById('upload-modal').classList.add('is-active');
    document.body.style.overflow = 'hidden';
}

function closeUploadModal() {
    document.getElementById('upload-modal').classList.remove('is-active');
    document.body.style.overflow = 'auto';
    resetUploadArea();
}

function openCreateModal() {
    document.getElementById('create-modal').classList.add('is-active');
    document.body.style.overflow = 'hidden';
}

function closeCreateModal() {
    document.getElementById('create-modal').classList.remove('is-active');
    document.body.style.overflow = 'auto';
    const createForm = document.getElementById('create-form');
    if (createForm) createForm.reset();
}

function openDetailModal(product) {
    const modal = document.getElementById('detail-modal');
    const content = document.getElementById('detail-content');
    
    if (content) {
        content.innerHTML = `
            <div class="aspect-square bg-gray-50 rounded-lg overflow-hidden mb-4">
                <img src="${product.thumbnail || product.image_url}" alt="${product.name}" class="w-full h-full object-contain bg-white">
            </div>
            <div>
                <h3 class="text-xl font-semibold mb-2">${escapeHtml(product.name)}</h3>
                <p class="text-gray-600 mb-2">${escapeHtml(product.category || 'No category')}</p>
                <p class="text-sm text-gray-500">
                    ${product.exist_type === 'uploaded' ? 'Uploaded' : 'AI Created'}
                    • ${new Date(product.created_at).toLocaleDateString()}
                </p>
            </div>
        `;
        
        const deleteBtn = document.getElementById('delete-item-btn');
        if (deleteBtn) deleteBtn.setAttribute('data-product-id', product.id);
    }
    
    if (modal) {
        modal.classList.add('is-active');
        document.body.style.overflow = 'hidden';
    }
}

function closeDetailModal() {
    const modal = document.getElementById('detail-modal');
    if (modal) modal.classList.remove('is-active');
    document.body.style.overflow = 'auto';
}

// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load wardrobe products
function loadWardrobe() {
    const loading = document.getElementById('wardrobe-loading');
    const grid = document.getElementById('wardrobe-grid');
    const empty = document.getElementById('wardrobe-empty');

    if (loading) loading.classList.remove('hidden');
    if (grid) grid.classList.remove('show');
    if (empty) empty.classList.add('hidden');

    fetch(ROUTES.wardrobe, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (loading) loading.classList.add('hidden');
        
        if (data.success && data.products.length > 0) {
            allProducts = data.products;
            renderProducts();
        } else if (empty) {
            empty.classList.remove('hidden');
        }
    })
    .catch(error => {
        console.error('Error loading wardrobe:', error);
        if (loading) loading.classList.add('hidden');
        if (typeof toastr !== 'undefined') {
            toastr.error('Failed to load wardrobe');
        }
    });
}

// Render products based on filter
function renderProducts() {
    const grid = document.getElementById('wardrobe-grid');
    const empty = document.getElementById('wardrobe-empty');

    let filteredProducts = allProducts;
    if (currentFilter !== 'all') {
        filteredProducts = allProducts.filter(p => p.exist_type === currentFilter);
    }

    if (filteredProducts.length === 0) {
        if (grid) grid.classList.remove('show');
        if (empty) empty.classList.remove('hidden');
        return;
    }

    if (empty) empty.classList.add('hidden');
    if (grid) {
        grid.classList.add('show');
        grid.innerHTML = '';
        filteredProducts.forEach(product => {
            grid.appendChild(createProductCard(product));
        });
    }
}

// Create product card
function createProductCard(product) {
    const div = document.createElement('div');
    div.className = 'group cursor-pointer';
    div.innerHTML = `
        <div class="aspect-square overflow-hidden rounded-lg transition hover:-translate-y-1 hover:shadow-lg">
            <img src="${product.thumbnail || product.image_url}" alt="${escapeHtml(product.name)}" class="w-full h-full object-contain bg-white">
        </div>
    `;
    div.addEventListener('click', () => openDetailModal(product));
    return div;
}

// DOM Content Loaded
document.addEventListener('DOMContentLoaded', function() {
    // Filter tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.getAttribute('data-filter');
            renderProducts();
        });
    });
    
    // Close modals on outside click
    const uploadModal = document.getElementById('upload-modal');
    const createModal = document.getElementById('create-modal');
    const detailModal = document.getElementById('detail-modal');
    
    if (uploadModal) {
        uploadModal.addEventListener('click', function(e) {
            if (e.target === this) closeUploadModal();
        });
    }
    
    if (createModal) {
        createModal.addEventListener('click', function(e) {
            if (e.target === this) closeCreateModal();
        });
    }
    
    if (detailModal) {
        detailModal.addEventListener('click', function(e) {
            if (e.target === this) closeDetailModal();
        });
    }
    
    // File upload handling
    const fileInput = document.getElementById('product-upload');
    const uploadArea = document.getElementById('upload-area');
    
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                handleFileSelect(this.files[0]);
            }
        });
    }
    
    if (uploadArea) {
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('border-primary', 'bg-purple-50');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('border-primary', 'bg-purple-50');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('border-primary', 'bg-purple-50');
            const files = e.dataTransfer.files;
            if (files.length > 0 && fileInput) {
                fileInput.files = files;
                handleFileSelect(files[0]);
            }
        });
    }
    
    // Upload form submission
    const uploadForm = document.getElementById('upload-form');
    if (uploadForm) {
        uploadForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const fileInput = document.getElementById('product-upload');
            const uploadFile = selectedUploadFile || (fileInput ? fileInput.files[0] : null);
            
            if (!uploadFile) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Please choose an image first');
                }
                return;
            }
            
            formData.set('product_image', uploadFile);
            
            fetch(ROUTES.upload, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Product uploaded successfully!');
                    }
                    closeUploadModal();
                    loadWardrobe();
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(data.message || 'Upload failed');
                    }
                }
            })
            .catch(error => {
                console.error('Upload error:', error);
                if (typeof toastr !== 'undefined') {
                    toastr.error('Failed to upload product');
                }
            });
        });
    }
    
    // Create form submission
    const createForm = document.getElementById('create-form');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch(ROUTES.create, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Product created successfully');
                    }
                    closeCreateModal();
                    loadWardrobe();
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(data.message || 'Creation failed');
                    }
                }
            })
            .catch(error => {
                console.error('Create error:', error);
                if (typeof toastr !== 'undefined') {
                    toastr.error('Failed to create product');
                }
            });
        });
    }
    
    // Delete item
    const deleteBtn = document.getElementById('delete-item-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            
            if (confirm('Are you sure you want to delete this item?')) {
                fetch(`${ROUTES.delete}/${productId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (typeof toastr !== 'undefined') {
                            toastr.success('Item deleted successfully');
                        }
                        closeDetailModal();
                        loadWardrobe();
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(data.message || 'Delete failed');
                        }
                    }
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to delete item');
                    }
                });
            }
        });
    }
    
    // Initialize
    loadWardrobe();
});
</script>
@endpush