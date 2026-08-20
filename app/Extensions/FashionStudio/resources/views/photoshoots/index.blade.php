@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('Photoshoot'))
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
@section('titlebar_subtitle', __('Upload product images and produce stunningly realistic photoshoots in seconds.'))

@section('content')
    <div
        class="py-10"
        x-data="photoshootApp"
    >
        <div class="flex flex-col gap-4 md:flex-row lg:gap-10">
            {{-- Left Panel: Setup Photoshoot --}}
            <div class="basis-1/3">
                <p class="mb-5 border-b py-2.5 text-[12px] font-semibold transition-border">
                    {{ __('Set Up Photoshoot') }}
                </p>

                <x-card
                    class:body="p-5"
                    variant="outline"
                >
                    <form
                        class="flex flex-col gap-4"
                        id="photoshoot-form"
                        @submit.prevent="generatePhotoshoot"
                    >
                        @csrf

                        {{-- Product Selection --}}
                        <div class="flex gap-4">
                            <div
                                class="group size-24 shrink-0 overflow-hidden rounded-lg md:size-[108px]"
                                id="product-preview"
                            >
                                <img
                                    class="size-full object-cover object-center blur-sm group-[&.selected]:blur-0"
                                    src="{{ asset('vendor/fashion-studio/images/style.png') }}"
                                    alt="{{ __('A preview of the selected product style') }}"
                                >
                            </div>
                            <div class="flex grow flex-col">
                                <p class="mb-0.5 text-3xs font-medium opacity-70">
                                    {{ __('Style:') }}
                                </p>
                                <p
                                    class="mb-5 text-2xs font-medium"
                                    x-text="selectedProductsText"
                                >
                                    {{ __('Select Up to 3 Products') }}
                                </p>
                                <x-button
                                    class="mt-auto"
                                    type="button"
                                    variant="outline"
                                    @click.prevent="showPanel('products')"
                                >
                                    {{ __('Select Products') }}
                                    <x-tabler-chevron-right class="size-4" />
                                </x-button>
                            </div>
                        </div>

                        {{-- Model Selection --}}
                        <div class="flex gap-4">
                            <div
                                class="group size-24 shrink-0 overflow-hidden rounded-lg md:size-[108px]"
                                id="model-preview"
                            >
                                <img
                                    class="size-full object-cover object-center blur-sm group-[&.selected]:blur-0"
                                    src="{{ asset('vendor/fashion-studio/images/model.png') }}"
                                    alt="{{ __('A preview of the selected model') }}"
                                >
                            </div>
                            <div class="flex grow flex-col">
                                <p class="mb-0.5 text-3xs font-medium opacity-70">
                                    {{ __('Model:') }}
                                </p>
                                <p
                                    class="mb-5 text-2xs font-medium"
                                    x-text="selectedModelText"
                                >
                                    {{ __('Random') }}
                                </p>
                                <x-button
                                    class="mt-auto"
                                    type="button"
                                    variant="outline"
                                    @click.prevent="showPanel('model')"
                                >
                                    {{ __('Select Model') }}
                                    <x-tabler-chevron-right class="size-4" />
                                </x-button>
                            </div>
                        </div>

                        {{-- Pose Selection --}}
                        <div class="flex gap-4">
                            <div
                                class="group size-24 shrink-0 overflow-hidden rounded-lg md:size-[108px]"
                                id="pose-preview"
                            >
                                <img
                                    class="size-full object-cover object-center blur-sm group-[&.selected]:blur-0"
                                    src="{{ asset('vendor/fashion-studio/images/pose.jpg') }}"
                                    alt="{{ __('A preview of the selected pose') }}"
                                >
                            </div>
                            <div class="flex grow flex-col">
                                <p class="mb-0.5 text-3xs font-medium opacity-70">
                                    {{ __('Pose:') }}
                                </p>
                                <p
                                    class="mb-5 text-2xs font-medium"
                                    x-text="selectedPoseText"
                                >
                                    {{ __('Random') }}
                                </p>
                                <x-button
                                    class="mt-auto"
                                    type="button"
                                    variant="outline"
                                    @click.prevent="showPanel('pose')"
                                >
                                    {{ __('Select Pose') }}
                                    <x-tabler-chevron-right class="size-4" />
                                </x-button>
                            </div>
                        </div>

                        {{-- Background Selection --}}
                        <div class="flex gap-4">
                            <div
                                class="group size-24 shrink-0 overflow-hidden rounded-lg md:size-[108px]"
                                id="background-preview"
                            >
                                <img
                                    class="size-full object-cover object-center blur-sm group-[&.selected]:blur-0"
                                    src="{{ asset('vendor/fashion-studio/images/bg.png') }}"
                                    alt="{{ __('A preview of the selected background') }}"
                                >
                            </div>
                            <div class="flex grow flex-col">
                                <p class="mb-0.5 text-3xs font-medium opacity-70">
                                    {{ __('Background:') }}
                                </p>
                                <p
                                    class="mb-5 text-2xs font-medium"
                                    x-text="selectedBackgroundText"
                                >
                                    {{ __('Random') }}
                                </p>
                                <x-button
                                    class="mt-auto"
                                    type="button"
                                    variant="outline"
                                    @click.prevent="showPanel('background')"
                                >
                                    {{ __('Select Background') }}
                                    <x-tabler-chevron-right class="size-4" />
                                </x-button>
                            </div>
                        </div>

                        {{-- Generate Button --}}
                        <x-button
                            class="w-full"
                            id="generate-btn"
                            type="submit"
                            variant="primary"
                            ::disabled="generating"
                            size="xl"
                        >
                            {{ __('Generate') }}
                        </x-button>
                    </form>
                </x-card>
            </div>

            {{-- Right Panel: Dynamic Content --}}
            <div
                class="flex basis-2/3 scroll-mt-5 flex-col"
                x-ref="panelContainer"
            >
                {{-- Products Selection Panel --}}
                <div
                    class="flex grow flex-col"
                    x-show="activePanel === 'products'"
                >
                    <div class="mb-5 flex items-center gap-6 border-b transition-border max-sm:overflow-y-auto max-sm:whitespace-nowrap max-sm:pb-px">
                        <button
                            class="-mb-px border-b border-current py-2.5 text-[12px] font-semibold text-heading-foreground transition"
                            type="button"
                            @click="productTab !== 'wardrobe' && loadWardrobe(); productTab = 'wardrobe';"
                        >
                            {{ __('Pick From Wardrobe') }}
                        </button>
                        <button
                            class="-mb-px border-b border-transparent py-2.5 text-[12px] font-semibold text-heading-foreground opacity-50 transition hover:opacity-100"
                            type="button"
                            @click="openUploadModal('product'); productTab = 'upload';"
                        >
                            {{ __('Upload Your Product') }}
                        </button>
                        <button
                            class="-mb-px border-b border-transparent py-2.5 text-[12px] font-semibold text-heading-foreground opacity-50 transition hover:opacity-100"
                            type="button"
                            @click="openCreateModal('product'); productTab = 'create';"
                        >
                            {{ __('Create New Product') }}
                        </button>
                    </div>

                    <div class="grid w-full grow grid-cols-1">
                        <div
                            class="col-start-1 col-end-1 row-start-1 row-end-1 flex w-full flex-col items-center justify-center py-12 text-center"
                            x-show="wardrobeLoading"
                        >
                            <x-tabler-loader-2 class="mx-auto mb-3.5 size-7 animate-spin text-heading-foreground" />

                            <p class="mb-0 text-sm font-semibold opacity-60">
                                {{ __('Loading your wardrobe...') }}
                            </p>
                        </div>

                        <div
                            class="col-start-1 col-end-1 row-start-1 row-end-1 flex w-full flex-col items-center justify-center py-12 text-center"
                            x-show="!wardrobeLoading && products.length === 0"
                            x-cloak
                        >
                            <span class="mx-auto mb-3 inline-grid size-28 place-items-center rounded-full bg-foreground/5">
                                <x-tabler-hanger-off class="size-14" />
                            </span>

                            <h3 class="mb-2 text-xl">
                                {{ __('Your wardrobe is empty') }}
                            </h3>
                            <p class="mb-0 text-xs opacity-60">
                                {{ __('Upload products to get started') }}
                            </p>
                        </div>

                        <div
                            class="col-start-1 col-end-1 row-start-1 row-end-1 grid w-full grid-cols-2 content-start items-start gap-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 lg:gap-4"
                            x-show="!wardrobeLoading && products.length > 0"
                            x-cloak
                        >
                            <template
                                x-for="product in products"
                                :key="product.id"
                            >
                                <div
                                    class="group relative rounded border p-2.5 transition-all hover:-translate-y-1 hover:shadow-lg hover:shadow-black/5 [&.selected]:ring-4 [&.selected]:ring-primary"
                                    @click="product.status?.toLowerCase() !== 'processing' && toggleProductSelection(product.id)"
                                    :class="{ 'cursor-pointer': product.status?.toLowerCase() !== 'processing', 'selected': selectedProducts.includes(product.id) }"
                                >
                                    <div
                                        class="absolute end-4 top-4 z-10 inline-grid size-9 place-items-center rounded-full bg-secondary text-secondary-foreground shadow-lg shadow-black/5"
                                        x-show="selectedProducts.includes(product.id)"
                                    >
                                        <x-tabler-check class="size-5" />
                                    </div>

                                    <img
                                        class="aspect-square w-full rounded object-cover object-center"
                                        :src="product.thumbnail"
                                        :alt="product.name"
                                    >
                                    <p
                                        class="absolute bottom-1 end-1 start-1 m-0 translate-y-1 truncate rounded border bg-background px-1.5 py-0.5 text-3xs font-medium opacity-0 transition group-hover:translate-y-0 group-hover:opacity-100"
                                        x-text="product.name"
                                        :title="product.name"
                                    ></p>

                                    <template x-if="product.exist_type !== 'static'">
                                        <div
                                            class="absolute end-2 top-2 z-20 opacity-0 transition group-hover:opacity-100"
                                            @click.stop
                                        >
                                            <x-dropdown.dropdown
                                                class:dropdown-dropdown="max-lg:end-0 max-lg:start-auto"
                                                anchor="end"
                                                :teleport="false"
                                            >
                                                <x-slot:trigger
                                                    class="size-7 rounded-full bg-background/90 text-foreground shadow-sm transition hover:bg-background"
                                                >
                                                    <x-tabler-dots-vertical class="size-4" />
                                                    <span class="sr-only">{{ __('Options') }}</span>
                                                </x-slot:trigger>
                                                <x-slot:dropdown
                                                    class="min-w-[120px]"
                                                >
                                                    <ul class="py-1 text-xs font-medium">
                                                        <li>
                                                            <a
                                                                class="flex items-center gap-2 px-3 py-2 text-red-500 transition-colors hover:bg-red-50 dark:hover:bg-red-500/10"
                                                                href="javascript:void(0);"
                                                                @click.prevent="deleteItem('product', product.id); toggle('collapse')"
                                                            >
                                                                <x-tabler-trash class="size-4" />
                                                                {{ __('Delete') }}
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </x-slot:dropdown>
                                            </x-dropdown.dropdown>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Model Selection Panel --}}
                <div
                    class="flex grow flex-col"
                    x-show="activePanel === 'model'"
                    x-cloak
                >
                    <div class="mb-5 flex items-center gap-6 border-b transition-border max-sm:overflow-y-auto max-sm:whitespace-nowrap max-sm:pb-px">
                        <button
                            class="-mb-px border-b border-current py-2.5 text-[12px] font-semibold text-heading-foreground transition"
                            type="button"
                            @click="modelTab !== 'pick' && loadModels(); modelTab = 'pick';"
                        >
                            {{ __('Pick a Model') }}
                        </button>
                        <button
                            class="-mb-px border-b border-transparent py-2.5 text-[12px] font-semibold text-heading-foreground opacity-50 transition hover:opacity-100"
                            type="button"
                            @click="openUploadModal('model'); modelTab = 'upload';"
                        >
                            {{ __('Upload Your Model') }}
                        </button>
                        <button
                            class="-mb-px border-b border-transparent py-2.5 text-[12px] font-semibold text-heading-foreground opacity-50 transition hover:opacity-100"
                            type="button"
                            @click="openCreateModal('model'); modelTab = 'create';"
                        >
                            {{ __('Create New Model') }}
                        </button>
                    </div>

                    <div class="grid w-full grow grid-cols-1">
                        <div
                            class="col-start-1 col-end-1 row-start-1 row-end-1 flex w-full flex-col items-center justify-center py-12 text-center"
                            x-show="modelsLoading"
                        >
                            <x-tabler-loader-2 class="mx-auto mb-3.5 size-7 animate-spin text-heading-foreground" />

                            <p class="mb-0 text-sm font-semibold opacity-60">
                                {{ __('Loading Models...') }}
                            </p>
                        </div>

                        <div
                            class="grid grid-cols-2 items-start gap-4 md:grid-cols-2 lg:grid-cols-3"
                            x-show="!modelsLoading"
                        >
                            <template
                                x-for="model in models"
                                :key="model.id"
                            >
                                <div
                                    class="group relative rounded border p-2.5 transition-all hover:-translate-y-1 hover:shadow-lg hover:shadow-black/5 [&.selected]:ring-4 [&.selected]:ring-primary"
                                    @click="model.status?.toLowerCase() !== 'processing' && toggleModelSelection(model)"
                                    :class="{ 'cursor-pointer': model.status?.toLowerCase() !== 'processing', 'selected': selectedModel === model.id }"
                                >
                                    <div
                                        class="absolute end-4 top-4 z-10 inline-grid size-9 place-items-center rounded-full bg-secondary text-secondary-foreground shadow-lg shadow-black/5"
                                        x-show="selectedModel === model.id"
                                    >
                                        <x-tabler-check class="size-5" />
                                    </div>
<!-- lucky -->
                                    <img
                                        class="mb-2 aspect-[1/0.9866] w-full rounded object-cover"
                                        :src="model.thumbnail"
                                        :alt="model.model_name"
                                    >
                                    <div class="flex items-end justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <p
                                                class="m-0 truncate text-3xs font-medium opacity-60"
                                                x-text="model.model_category || model.gender"
                                            ></p>
                                            <p
                                                class="m-0 truncate text-2xs font-medium"
                                                x-text="model.model_name || model.name"
                                            ></p>
                                        </div>
                                        <template x-if="model.exist_type !== 'static'">
                                            <div
                                                class="shrink-0"
                                                @click.stop
                                            >
                                                <x-dropdown.dropdown
                                                    class:dropdown-dropdown="max-lg:end-0 max-lg:start-auto bottom-full top-auto mb-1"
                                                    anchor="end"
                                                    :teleport="false"
                                                >
                                                    <x-slot:trigger
                                                        class="size-6 rounded-full text-foreground/50 transition hover:bg-foreground/5 hover:text-foreground"
                                                    >
                                                        <x-tabler-dots-vertical class="size-4" />
                                                        <span class="sr-only">{{ __('Options') }}</span>
                                                    </x-slot:trigger>
                                                    <x-slot:dropdown
                                                        class="min-w-[120px]"
                                                    >
                                                        <ul class="py-1 text-xs font-medium">
                                                            <li>
                                                                <a
                                                                    class="flex items-center gap-2 px-3 py-2 text-red-500 transition-colors hover:bg-red-50 dark:hover:bg-red-500/10"
                                                                    href="javascript:void(0);"
                                                                    @click.prevent="deleteItem('model', model.id.toString().replace('user-', '')); toggle('collapse')"
                                                                >
                                                                    <x-tabler-trash class="size-4" />
                                                                    {{ __('Delete') }}
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </x-slot:dropdown>
                                                </x-dropdown.dropdown>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Pose Selection Panel --}}
                <div
                    class="flex grow flex-col"
                    x-show="activePanel === 'pose'"
                    x-cloak
                >
                    <div class="mb-5 flex items-center gap-6 border-b transition-border max-sm:overflow-y-auto max-sm:whitespace-nowrap max-sm:pb-px">
                        <button
                            class="-mb-px border-b border-current py-2.5 text-[12px] font-semibold text-heading-foreground transition"
                            type="button"
                            @click="poseTab !== 'pick' && loadPoses(); poseTab = 'pick';"
                        >
                            {{ __('Pick a Pose') }}
                        </button>
                        <button
                            class="-mb-px border-b border-transparent py-2.5 text-[12px] font-semibold text-heading-foreground opacity-50 transition hover:opacity-100"
                            type="button"
                            @click="openUploadModal('pose'); poseTab = 'upload';"
                        >
                            {{ __('Upload Your Pose') }}
                        </button>
                        <button
                            class="-mb-px border-b border-transparent py-2.5 text-[12px] font-semibold text-heading-foreground opacity-50 transition hover:opacity-100"
                            type="button"
                            @click="openCreateModal('pose'); poseTab = 'create';"
                        >
                            {{ __('Create New Pose') }}
                        </button>
                    </div>

                    <div class="grid w-full grow grid-cols-1">
                        <div
                            class="col-start-1 col-end-1 row-start-1 row-end-1 flex w-full flex-col items-center justify-center py-12 text-center"
                            x-show="posesLoading"
                        >
                            <x-tabler-loader-2 class="mx-auto mb-3.5 size-7 animate-spin text-heading-foreground" />

                            <p class="mb-0 text-sm font-semibold opacity-60">
                                {{ __('Loading Poses...') }}
                            </p>
                        </div>

                        <div
                            class="grid grid-cols-2 items-start gap-4 md:grid-cols-2 lg:grid-cols-3"
                            x-show="!posesLoading"
                        >
                            <template
                                x-for="pose in poses"
                                :key="pose.id"
                            >
                                <div
                                    class="group relative rounded border p-2.5 transition-all hover:-translate-y-1 hover:shadow-lg hover:shadow-black/5 [&.selected]:ring-4 [&.selected]:ring-primary"
                                    @click="pose.status?.toLowerCase() !== 'processing' && togglePoseSelection(pose)"
                                    :class="{ 'cursor-pointer': pose.status?.toLowerCase() !== 'processing', 'selected': selectedPose === pose.id }"
                                >
                                    <div
                                        class="absolute end-4 top-4 z-10 inline-grid size-9 place-items-center rounded-full bg-secondary text-secondary-foreground shadow-lg shadow-black/5"
                                        x-show="selectedPose === pose.id"
                                    >
                                        <x-tabler-check class="size-5" />
                                    </div>
<!-- pritam -->
                                    <img
                                        class="mb-2 aspect-[1/1.2767] w-full rounded object-cover"
                                        :src="pose.thumbnail"
                                        :alt="pose.pose_name"
                                    >
                                    <div class="flex items-end justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <p class="m-0 truncate text-3xs font-medium opacity-60">
                                                @lang('Pose')
                                            </p>
                                            <p
                                                class="m-0 truncate text-2xs font-medium"
                                                x-text="pose.pose_name || pose.name"
                                            ></p>
                                        </div>
                                        <template x-if="pose.exist_type !== 'static'">
                                            <div
                                                class="shrink-0"
                                                @click.stop
                                            >
                                                <x-dropdown.dropdown
                                                    class:dropdown-dropdown="max-lg:end-0 max-lg:start-auto bottom-full top-auto mb-1"
                                                    anchor="end"
                                                    :teleport="false"
                                                >
                                                    <x-slot:trigger
                                                        class="size-6 rounded-full text-foreground/50 transition hover:bg-foreground/5 hover:text-foreground"
                                                    >
                                                        <x-tabler-dots-vertical class="size-4" />
                                                        <span class="sr-only">{{ __('Options') }}</span>
                                                    </x-slot:trigger>
                                                    <x-slot:dropdown
                                                        class="min-w-[120px]"
                                                    >
                                                        <ul class="py-1 text-xs font-medium">
                                                            <li>
                                                                <a
                                                                    class="flex items-center gap-2 px-3 py-2 text-red-500 transition-colors hover:bg-red-50 dark:hover:bg-red-500/10"
                                                                    href="javascript:void(0);"
                                                                    @click.prevent="deleteItem('pose', pose.id.toString().replace('user-', '')); toggle('collapse')"
                                                                >
                                                                    <x-tabler-trash class="size-4" />
                                                                    {{ __('Delete') }}
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </x-slot:dropdown>
                                                </x-dropdown.dropdown>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Background Selection Panel --}}
                <div
                    class="flex grow flex-col"
                    x-show="activePanel === 'background'"
                    x-cloak
                >
                    <div class="mb-5 flex items-center gap-6 border-b transition-border max-sm:overflow-y-auto max-sm:whitespace-nowrap max-sm:pb-px">
                        <button
                            class="-mb-px border-b border-current py-2.5 text-[12px] font-semibold text-heading-foreground transition"
                            type="button"
                            @click="backgroundTab !== 'pick' && loadBackgrounds(); backgroundTab = 'pick';"
                        >
                            {{ __('Pick a Background Image') }}
                        </button>
                        <button
                            class="-mb-px border-b border-transparent py-2.5 text-[12px] font-semibold text-heading-foreground opacity-50 transition hover:opacity-100"
                            type="button"
                            @click="openUploadModal('background'); backgroundTab = 'upload';"
                        >
                            {{ __('Upload Your Background') }}
                        </button>
                        <button
                            class="-mb-px border-b border-transparent py-2.5 text-[12px] font-semibold text-heading-foreground opacity-50 transition hover:opacity-100"
                            type="button"
                            @click="openCreateModal('background'); backgroundTab = 'create';"
                        >
                            {{ __('Create New Background') }}
                        </button>
                    </div>

                    <div class="grid w-full grow grid-cols-1">
                        <div
                            class="col-start-1 col-end-1 row-start-1 row-end-1 flex w-full flex-col items-center justify-center py-12 text-center"
                            x-show="backgroundsLoading"
                        >
                            <x-tabler-loader-2 class="mx-auto mb-3.5 size-7 animate-spin text-heading-foreground" />

                            <p class="mb-0 text-sm font-semibold opacity-60">
                                {{ __('Loading Backgrounds...') }}
                            </p>
                        </div>

                        <div
                            class="grid grid-cols-2 items-start gap-4 md:grid-cols-2 lg:grid-cols-3"
                            x-show="!backgroundsLoading"
                        >
                            <template
                                x-for="background in backgrounds"
                                :key="background.id"
                            >
                                <div
                                    class="group relative rounded border p-2.5 transition-all hover:-translate-y-1 hover:shadow-lg hover:shadow-black/5 [&.selected]:ring-4 [&.selected]:ring-primary"
                                    @click="background.status?.toLowerCase() !== 'processing' && toggleBackgroundSelection(background)"
                                    :class="{ 'cursor-pointer': background.status?.toLowerCase() !== 'processing', 'selected': selectedBackground === background.id }"
                                >
                                    <div
                                        class="absolute end-4 top-4 z-10 inline-grid size-9 place-items-center rounded-full bg-secondary text-secondary-foreground shadow-lg shadow-black/5"
                                        x-show="selectedBackground === background.id"
                                    >
                                        <x-tabler-check class="size-5" />
                                    </div>

<!-- pritam -->
                                    <img
                                        class="mb-2 aspect-[1/1.2767] w-full rounded object-cover"
                                        :src="background.thumbnail"
                                        :alt="background.background_name"
                                    >
                                    <div class="flex items-end justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <p
                                                class="m-0 truncate text-3xs font-medium opacity-60"
                                                x-text="background.background_category || background.category"
                                            ></p>
                                            <p
                                                class="m-0 truncate text-2xs font-medium"
                                                x-text="background.background_name || background.name"
                                            ></p>
                                        </div>
                                        <template x-if="background.exist_type !== 'static'">
                                            <div
                                                class="shrink-0"
                                                @click.stop
                                            >
                                                <x-dropdown.dropdown
                                                    class:dropdown-dropdown="max-lg:end-0 max-lg:start-auto bottom-full top-auto mb-1"
                                                    anchor="end"
                                                    :teleport="false"
                                                >
                                                    <x-slot:trigger
                                                        class="size-6 rounded-full text-foreground/50 transition hover:bg-foreground/5 hover:text-foreground"
                                                    >
                                                        <x-tabler-dots-vertical class="size-4" />
                                                        <span class="sr-only">{{ __('Options') }}</span>
                                                    </x-slot:trigger>
                                                    <x-slot:dropdown
                                                        class="min-w-[120px]"
                                                    >
                                                        <ul class="py-1 text-xs font-medium">
                                                            <li>
                                                                <a
                                                                    class="flex items-center gap-2 px-3 py-2 text-red-500 transition-colors hover:bg-red-50 dark:hover:bg-red-500/10"
                                                                    href="javascript:void(0);"
                                                                    @click.prevent="deleteItem('background', background.id.toString().replace('user-', '')); toggle('collapse')"
                                                                >
                                                                    <x-tabler-trash class="size-4" />
                                                                    {{ __('Delete') }}
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </x-slot:dropdown>
                                                </x-dropdown.dropdown>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Results Panel --}}
                <div
                    class="flex grow flex-col"
                    x-show="activePanel === 'results'"
                    x-cloak
                >
                    <div class="mb-5 flex flex-wrap justify-between gap-3 border-b py-2.5 transition-border">
                        <p class="m-0 text-[12px] font-semibold">
                            {{ __('Generated Results') }}
                        </p>

                        <x-button
                            class="text-[12px] font-semibold"
                            variant="link"
                            @click="downloadAll"
                            x-show="results.length"
                        >
                            {{ __('Download All') }}
                            <x-tabler-download class="size-4" />
                        </x-button>
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

                    {{-- Results Grid - Dynamic columns based on image count --}}
                    <div
                        class="grid items-start gap-4"
                        :class="{
                            'grid-cols-1': results.length === 1,
                            'grid-cols-2': results.length === 2 || results.length === 4,
                            'grid-cols-3': results.length === 3
                        }"
                        x-show="results.length"
                    >
                        <template
                            x-for="(result, index) in results"
                            :key="result.id"
                        >
                            <div class="group relative">
                                <img
                                    class="w-full rounded-lg"
                                    :src="result.image_url"
                                    :alt="'Generated Photoshoot ' + (index + 1)"
                                >

                                {{-- Hover Overlay --}}
                                <div class="absolute inset-0 flex items-center justify-center rounded-lg bg-black/10 opacity-0 transition-opacity group-hover:opacity-100">
                                    <div class="flex gap-2 opacity-0 transition-opacity group-hover:opacity-100">
                                        {{-- View Button --}}
                                        <x-button
    class="inline-grid size-9 place-items-center bg-white p-0 text-black hover:bg-white hover:text-black"
    data-fslightbox="gallery"
    data-type="image"
    size="none"
    ::href="result.image_url"
    title="{{ __('View') }}"
>
    <x-tabler-eye class="size-4" />
</x-button>

                                        {{-- Download Button --}}
                                        <x-button
                                            class="inline-grid size-9 place-items-center bg-white p-0 text-black hover:bg-white hover:text-black"
                                            size="none"
                                            @click.prevent="downloadResult(result.image_url, index)"
                                            title="{{ __('Download') }}"
                                        >
                                            <x-tabler-download class="size-4" />
                                        </x-button>

                                        {{-- Delete Button --}}
                                        <x-button
                                            class="inline-grid size-9 place-items-center p-0"
                                            variant="danger"
                                            @click.prevent="deleteResult(result.id, index)"
                                            title="{{ __('Delete') }}"
                                        >
                                            <x-tabler-trash class="size-4" />
                                        </x-button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- Universal Upload/Create Modal --}}
        <div
            class="fixed inset-0 z-[999] flex items-center justify-center px-5"
            x-show="modalOpen"
            x-cloak
            @keydown.esc.window="closeModal"
        >
            <div
                class="absolute inset-0 z-0 bg-black/50"
                x-show="modalOpen"
                x-cloak
                x-transition.opacity
                @click="closeModal"
            ></div>
            <div
                class="relative z-1 w-[min(760px,100%)]"
                x-show="modalOpen"
                x-cloak
                x-transition
            >
                <button
                    class="absolute -end-4 -top-4 z-10 inline-grid size-9 place-items-center rounded-full bg-background shadow-lg transition lg:-end-12 lg:top-0"
                    type="button"
                    @click="closeModal"
                >
                    <x-tabler-x class="size-5" />
                </button>

                <div class="h-full max-h-[90vh] w-full overflow-y-auto rounded-xl bg-background p-5 shadow-xl shadow-black/5 md:p-8">
                    <div class="mb-5 flex gap-4 border-b">
                        <button
                            class="-mb-px border-b border-transparent py-2.5 text-[12px] font-semibold opacity-50 transition hover:opacity-100 [&.active]:border-b-current [&.active]:opacity-100"
                            type="button"
                            :class="{ 'active': modalMode === 'upload' }"
                            @click="modalMode = 'upload'"
                        >
                            <span x-text="'Upload Your ' + modalType.charAt(0).toUpperCase() + modalType.slice(1)"></span>
                        </button>
                        <button
                            class="-mb-px border-b border-transparent py-2.5 text-[12px] font-semibold opacity-50 transition hover:opacity-100 [&.active]:border-b-current [&.active]:opacity-100"
                            type="button"
                            :class="{ 'active': modalMode === 'create' }"
                            @click="modalMode = 'create'"
                        >
                            <span x-text="'Create New ' + modalType.charAt(0).toUpperCase() + modalType.slice(1)"></span>
                        </button>
                    </div>

                    {{-- Upload Form --}}
                    <form
                        x-show="modalMode === 'upload'"
                        @submit.prevent="submitUpload()"
                        enctype="multipart/form-data"
                    >
                        @csrf
                        <input
                            class="hidden"
                            type="file"
                            x-ref="fileInput"
                            accept="image/*,.heic,.heif"
                            @change="handleFileSelect($event)"
                        >

                        <div
                            class="mb-4 cursor-pointer rounded-2xl border-2 border-dashed p-8 text-center transition-colors [&.drag-over]:border-primary/50 [&.drag-over]:bg-primary/5"
                            @click="$refs.fileInput.click()"
                            @dragover.prevent="dragOver = true"
                            @dragleave.prevent="dragOver = false"
                            @drop.prevent="handleFileDrop($event)"
                            :class="{ 'drag-over': dragOver }"
                        >

                            <template x-if="!uploadPreview">
                                <div>
                                    <div class="mx-auto mb-4 w-52">
                                        <img
                                            class="w-full drop-shadow-[0px_4px_14px_hsl(0_0%_0%/10%)]"
                                            :src="modalType === 'product' ? '{{ asset('vendor/fashion-studio/images/product-modal.png') }}' :
                                                modalType === 'model' ? '{{ asset('vendor/fashion-studio/images/model-modal.png') }}' :
                                                modalType === 'pose' ? '{{ asset('vendor/fashion-studio/images/pose-modal.png') }}' :
                                                modalType === 'background' ? '{{ asset('vendor/fashion-studio/images/background-modal.png') }}' :
                                                ''"
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
                                        @click.stop="$refs.fileInput.click()"
                                        size="xl"
                                    >
                                        {{ __('Browse Files') }}
                                    </x-button>

                                    {{-- Dynamic tips based on modal type --}}
                                    <div class="space-y-1 text-[12px] opacity-50">
                                        {{-- Product tips --}}
                                        <template x-if="modalType === 'product'">
                                            <ul class="list-inside list-disc">
                                                <li>{{ __('A well-lit flat lay image showcasing your product') }}</li>
                                                <li>{{ __('Ensure good lighting without harsh shadows') }}</li>
                                                <li>{{ __('PNG, JPG or HEIC (Max: 25Mb)') }}</li>
                                            </ul>
                                        </template>

                                        {{-- Model tips --}}
                                        <template x-if="modalType === 'model'">
                                            <ul class="list-inside list-disc">
                                                <li>{{ __('Clearly visible face and hair') }}</li>
                                                <li>{{ __('Ensure good lighting without harsh shadows') }}</li>
                                                <li>{{ __('PNG, JPG or HEIC (Max: 25Mb)') }}</li>
                                            </ul>
                                        </template>

                                        {{-- Pose tips --}}
                                        <template x-if="modalType === 'pose'">
                                            <ul class="list-inside list-disc">
                                                <li>{{ __('PNG, JPG or HEIC (Max: 25Mb)') }}</li>
                                            </ul>
                                        </template>

                                        {{-- Background tips --}}
                                        <template x-if="modalType === 'background'">
                                            <ul class="list-inside list-disc">
                                                <li>{{ __('PNG, JPG or HEIC (Max: 25Mb)') }}</li>
                                            </ul>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <template x-if="uploadPreview">
                                <div class="text-center">
                                    <img
                                        class="mx-auto mb-2 max-h-48 rounded-lg"
                                        :src="uploadPreview"
                                    >

                                    <p
                                        class="text-2xs font-medium opacity-60"
                                        x-text="uploadFileName"
                                    ></p>

                                    <x-button
                                        variant="link"
                                        type="button"
                                        @click.stop="resetUpload()"
                                    >
                                        {{-- blade-formatter-disable --}}
										<svg class="size-4" width="38" height="38" viewBox="0 0 38 38" fill="currentColor" xmlns="http://www.w3.org/2000/svg" class="opacity-25"><path d="M32.4073 32.4839C28.7298 36.1613 24.2608 38 19 38C13.7392 38 9.24462 36.1613 5.51613 32.4839C1.83871 28.7554 0 24.2608 0 19C0 13.7392 1.83871 9.27016 5.51613 5.59274C9.24462 1.86425 13.7392 0 19 0C24.2608 0 28.7298 1.86425 32.4073 5.59274C36.1358 9.27016 38 13.7392 38 19C38 24.2608 36.1358 28.7554 32.4073 32.4839ZM29.8024 8.19758C26.8401 5.18414 23.2392 3.67742 19 3.67742C14.7608 3.67742 11.1344 5.18414 8.12097 8.19758C5.1586 11.1599 3.67742 14.7608 3.67742 19C3.67742 23.2392 5.1586 26.8656 8.12097 29.879C11.1344 32.8414 14.7608 34.3226 19 34.3226C23.2392 34.3226 26.8401 32.8414 29.8024 29.879C32.8159 26.8656 34.3226 23.2392 34.3226 19C34.3226 14.7608 32.8159 11.1599 29.8024 8.19758ZM20.5323 28.8065H17.4677C16.8548 28.8065 16.5484 28.5 16.5484 27.8871V22C16.5484 20.3431 15.2052 19 13.5484 19H11.4153C11.0067 19 10.7258 18.8212 10.5726 18.4637C10.4194 18.0551 10.4704 17.7231 10.7258 17.4677L18.3871 9.80645C18.7957 9.39785 19.2043 9.39785 19.6129 9.80645L27.2742 17.4677C27.5296 17.7231 27.5806 18.0551 27.4274 18.4637C27.2742 18.8212 26.9933 19 26.5847 19H24.4516C22.7948 19 21.4516 20.3431 21.4516 22V27.8871C21.4516 28.5 21.1452 28.8065 20.5323 28.8065Z"/></svg>
										{{-- blade-formatter-enable --}}
                                        {{ __('Upload Another') }}
                                    </x-button>
                                </div>
                            </template>
                        </div>

                        {{-- Dynamic form fields based on modal type --}}
                        <div
                            class="mb-4 space-y-3"
                            x-show="uploadPreview"
                        >
                            {{-- Product-specific fields --}}
                            <template x-if="modalType === 'product'">
                                <div class="space-y-5">
                                    <x-forms.input
                                        class:label="text-xs font-medium text-heading-foreground"
                                        size="lg"
                                        label="{{ __('Product Name') }}"
                                        x-model="uploadForm.name"
                                        placeholder="{{ __(' e.g. My Product') }}"
                                    />

                                    <x-forms.input
                                        class:label="text-xs font-medium text-heading-foreground"
                                        size="lg"
                                        label="{{ __('Category') }}"
                                        x-model="uploadForm.category"
                                        placeholder="{{ __(' e.g. Casual') }}"
                                    />
                                </div>
                            </template>

                            {{-- Model-specific fields --}}
                            <template x-if="modalType === 'model'">
                                <div class="space-y-3">
                                    <x-forms.input
                                        class:label="text-xs font-medium text-heading-foreground"
                                        size="lg"
                                        label="{{ __('Model Gender') }}"
                                        x-model="uploadForm.gender"
                                        required
                                        type="select"
                                    >
                                        <option value="">{{ __('Select Gender') }}</option>
                                        <option value="Male">{{ __('Male') }}</option>
                                        <option value="Female">{{ __('Female') }}</option>
                                    </x-forms.input>

                                    <x-forms.input
                                        class:label="text-xs font-medium text-heading-foreground"
                                        size="lg"
                                        label="{{ __('Model Name') }}"
                                        x-model="uploadForm.name"
                                        required
                                        placeholder="{{ __('e.g., Sanchez') }}"
                                    />
                                </div>
                            </template>

                            {{-- Pose-specific fields --}}
                            <template x-if="modalType === 'pose'">
                                <div>
                                    <x-forms.input
                                        class:label="text-xs font-medium text-heading-foreground"
                                        size="lg"
                                        label="{{ __('Pose Name') }}"
                                        x-model="uploadForm.name"
                                        required
                                        placeholder="{{ __('e.g., Standing') }}"
                                    />
                                </div>
                            </template>

                            {{-- Background-specific fields --}}
                            <template x-if="modalType === 'background'">
                                <div class="space-y-3">
                                    <x-forms.input
                                        class:label="text-xs font-medium text-heading-foreground"
                                        size="lg"
                                        label="{{ __('Background Type') }}"
                                        x-model="uploadForm.category"
                                        type="select"
                                    >
                                        <option value="">{{ __('Select Type') }}</option>
                                        <option value="Outdoor">{{ __('Outdoor') }}</option>
                                        <option value="Studio">{{ __('Studio') }}</option>
                                        <option value="City">{{ __('City') }}</option>
                                        <option value="Wall">{{ __('Wall') }}</option>
                                        <option value="Other">{{ __('Other') }}</option>
                                    </x-forms.input>

                                    <x-forms.input
                                        class:label="text-xs font-medium text-heading-foreground"
                                        size="lg"
                                        label="{{ __('Background Name') }}"
                                        x-model="uploadForm.name"
                                        placeholder="{{ __('Sunset') }}"
                                    />
                                </div>
                            </template>
                        </div>

                        <x-button
                            class="mt-2 w-full text-xs"
                            type="submit"
                            size="lg"
                            variant="secondary"
                            ::disabled="uploading"
                        >
                            <span
                                x-text="uploading ?
									'Uploading...' :
									modalType === 'product' ? '{{ __('Upload Product') }}' :
									modalType === 'model' ? '{{ __('Create Model') }}' :
									modalType === 'pose' ? '{{ __('Upload Pose') }}' :
									'{{ __('Upload Background Image') }}'
								"
                            ></span>
                            <span class="inline-grid size-7 place-items-center rounded-full bg-background text-foreground">
                                <x-tabler-chevron-right class="size-4" />
                            </span>
                        </x-button>
                    </form>

                    {{-- Create Form --}}
                    <form
                        class="flex flex-col gap-4"
                        x-show="modalMode === 'create'"
                        @submit.prevent="submitCreate()"
                    >
                        @csrf

                        <x-forms.input
                            class:label="text-xs font-medium text-heading-foreground"
                            class:label-extra="m-0"
                            size="lg"
                            rows="8"
                            label=" "
                            type="textarea"
                            x-model="createForm.description"
                            ::placeholder="'Describe your ' + modalType + ' in detail...'"
                        >
                            <x-slot:label-extra>
                                <span x-text="modalType.charAt(0).toUpperCase() + modalType.slice(1) + ' Description'"></span>
                            </x-slot:label-extra>
                        </x-forms.input>

                        <x-button
                            type="submit"
                            variant="secondary"
                            ::disabled="creating"
                            size="lg"
                        >
                            <span x-text="creating ? 'Creating...' : 'Create ' + modalType.charAt(0).toUpperCase() + modalType.slice(1)"></span>
                            <span class="inline-grid size-7 place-items-center rounded-full bg-background text-foreground">
                                <x-tabler-chevron-right class="size-4" />
                            </span>
                        </x-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ custom_theme_url('/assets/libs/fslightbox/fslightbox.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js"></script>
@endpush

@include('fashion-studio::photoshoots.photoshoots-scripts')
