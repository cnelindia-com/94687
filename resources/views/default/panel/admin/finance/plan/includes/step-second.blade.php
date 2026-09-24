<div class="space-y-8">

    @if ($planAiToolsMenu)
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
            <x-form-step
                class="col-span-2 m-0"
                step="1"
                label="{{ __('AI Tools') }}"
            />
            @foreach ($planAiToolsMenu as $tool)
                <x-form.group
                    class="col-span-2 sm:col-span-1"
                    no-group-label
                    :error="'plan.plan_ai_tools.' . $tool['key']"
                >
                    <x-form.checkbox
                        class="border-input rounded-input border !px-2.5 !py-3"
                        wire:model="plan.plan_ai_tools.{{ $tool['key'] }}"
                        value="{{ $tool['key'] }}"
                        label="{{ $tool['label'] }}"
                        tooltip="{{ $tool['tooltip'] ?? $tool['label'] }}"
                    />
                </x-form.group>
            @endforeach

            <!-- pritam -->
            

           <x-form.group
    class="col-span-2 sm:col-span-1"
    no-group-label
    x-show="$wire.plan?.plan_ai_tools?.ext_fashion_studio_dropdown"
    x-transition
>
    <x-form.checkbox
        class="border-input rounded-input border !px-2.5 !py-3"
        wire:model="plan.image_2k"
        label="2K Image"
    />
</x-form.group>

<x-form.group
    class="col-span-2 sm:col-span-1"
    no-group-label
    x-show="$wire.plan?.plan_ai_tools?.ext_fashion_studio_dropdown"
    x-transition
>
    <x-form.checkbox
        class="border-input rounded-input border !px-2.5 !py-3"
        wire:model="plan.image_4k"
        label="4K Image"
    />
</x-form.group>

<x-form.group
    class="col-span-2 sm:col-span-1"
    no-group-label
>
    <x-form.checkbox
        class="border-input rounded-input border !px-2.5 !py-3"
        wire:model="plan.default_plan"
        label="Default Plan"
    />
</x-form.group>

<!-- ========== CREATE VIDEO TOGGLE ========== -->
<x-form.group
    class="col-span-2 sm:col-span-1"
    no-group-label
    x-show="$wire.plan?.plan_ai_tools?.ext_fashion_studio_dropdown"
    x-transition
>
    <x-form.checkbox
        class="border-input rounded-input border !px-2.5 !py-3"
        wire:model="plan.create_video"
        label="Create Video"
    />
</x-form.group>

<!-- ========== CHANGE MODELS TOGGLE ========== -->
<x-form.group
    class="col-span-2 sm:col-span-1"
    no-group-label
    x-show="$wire.plan?.plan_ai_tools?.ext_fashion_studio_dropdown"
    x-transition
>
    <x-form.checkbox
        class="border-input rounded-input border !px-2.5 !py-3"
        wire:model="plan.change_models"
        label="Change Models"
    />
</x-form.group>

<x-form.group
    class="col-span-2 sm:col-span-1"
    no-group-label
    x-show="$wire.plan?.plan_ai_tools?.ext_fashion_studio_dropdown"
    x-transition
>

</x-form.group>

<x-form.group
    class="col-span-2 sm:col-span-1"
    no-group-label
    x-show="$wire.plan?.plan_ai_tools?.ext_fashion_studio_dropdown"
    x-transition
>
    
</x-form.group>

<div class="col-span-2">
            <label class="form-label fw-bold mb-2">
                {{ __('Total Credits') }}
                <span class="text-danger">*</span>
            </label>
            <input 
                type="number"
                wire:model.defer="plan.total_credits"
                class="form-control"
                placeholder="{{ __('Enter total credits for this plan') }}"
                  
                
                required
            />
            <div class="form-text text-muted mt-1">
                {{ __('Number of credits user will receive with this plan') }}
            </div>
            @error('plan.total_credits')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- 1k  -->

        <div class="col-span-2 sm:col-span-1">
                <label class="form-label fw-bold mb-2">
                    {{ __('1K Image Weight') }}
                </label>
                <input 
                    type="number"
                    wire:model.defer="plan.image_1k_weight"
                    class="form-control"
                    placeholder="{{ __('Enter weight for 1K image') }}"
                    
                />
                <div class="form-text text-muted mt-1">
                    {{ __('Credit cost for generating 1K resolution image') }}
                </div>
                @error('plan.image_1k_weight')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- 2k -->
            <div class="col-span-2 sm:col-span-1">
                <label class="form-label fw-bold mb-2">
                    {{ __('2K Image Weight') }}
                </label>
                <input 
                    type="number"
                    wire:model.defer="plan.image_2k_weight"
                    class="form-control"
                    placeholder="{{ __('Enter weight for 2K image') }}"
                    
                />
                <div class="form-text text-muted mt-1">
                    {{ __('Credit cost for generating 2K resolution image') }}
                </div>
                @error('plan.image_2k_weight')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- ========== 4K IMAGE WEIGHT FIELD ========== -->
            <div class="col-span-2 sm:col-span-1">
                <label class="form-label fw-bold mb-2">
                    {{ __('4K Image Weight') }}
                </label>
                <input 
                    type="number"
                    wire:model.defer="plan.image_4k_weight"
                    class="form-control"
                    placeholder="{{ __('Enter weight for 4K image') }}"
                    
                />
                <div class="form-text text-muted mt-1">
                    {{ __('Credit cost for generating 4K resolution image') }}
                </div>
                @error('plan.image_4k_weight')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>


            <!-- ========== CREATE VIDEO FIELD ========== -->
            <div class="col-span-2 sm:col-span-1">
                <label class="form-label fw-bold mb-2">
                    {{ __('Create Video Weight') }}
                </label>
                <input 
                    type="number"
                    wire:model.defer="plan.create_video_weight"
                    class="form-control"
                    placeholder="{{ __('Enter weight for video generation') }}"
                    
                />
                <div class="form-text text-muted mt-1">
                    {{ __('Credit cost for generating video') }}
                </div>
                @error('plan.create_video_weight')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>


        </div>
    @endif

    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
        <x-form-step
            class="col-span-2 m-0"
            step="2"
            label="{{ __('Features') }}"
        />
        @foreach ($planFeatureMenu as $feature)
            <x-form.group
                class="col-span-2 sm:col-span-1"
                no-group-label
                :error="'plan.plan_features.' . $feature['key']"
            >
                <x-form.checkbox
                    class="border-input rounded-input border !px-2.5 !py-3"
                    wire:model="plan.plan_features.{{ $feature['key'] }}"
                    label="{{ $feature['label'] }}"
                    value="{{ $feature['key'] }}"
                />
            </x-form.group>
        @endforeach
    </div>
</div>
