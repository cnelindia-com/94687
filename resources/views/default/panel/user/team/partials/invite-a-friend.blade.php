<div class="flex flex-wrap justify-between gap-y-5">
    <x-card class="lqd-team-invite-instruction w-full lg:w-[48%]">
        <h2 class="mb-4">
            @lang('How it Works')
        </h2>
        <p>
            @lang('Adding and overseeing team members is a straightforward process. Here\'s a quick example to initiate collaboration within seconds.')
        </p>

        <hr class="my-8">

        <ol class="mb-12 flex flex-col gap-6 text-heading-foreground">
            <li>
                <span class="size-7 me-2 inline-flex items-center justify-center rounded-full bg-primary/10 font-extrabold text-primary">
                    1
                </span>
                @lang('Send an <strong>invitation</strong> link to your team members.')
            </li>
            <li>
                <span class="size-7 me-2 inline-flex items-center justify-center rounded-full bg-primary/10 font-extrabold text-primary">
                    2
                </span>
                @lang('<strong>Let them register</strong> with their email address.')
            </li>
            <li>
                <span class="size-7 me-2 inline-flex items-center justify-center rounded-full bg-primary/10 font-extrabold text-primary">
                    3
                </span>
                @lang('Once <strong>they confirm their account</strong> they will be added to your team.')
            </li>
        </ol>
    </x-card>

    <x-card class="lqd-team-invite-form w-full lg:w-[48%]">
        <figure class="mb-7">
            <img
                class="mx-auto w-full lg:w-7/12"
                src="{{ custom_theme_url('assets/img/team/team.png') }}"
                alt="Team"
            >
        </figure>
        <p class="mb-6 text-center text-xl font-semibold">
            Add your team members' email address <br> to start collaborating.
            📧
        </p>
        
        <!-- Multi-Step Form -->
        <form
            class="flex flex-col gap-4"
            id="team-invite-form"
            action="{{ route('dashboard.user.team.invitation.store', $team?->id ?? 0) }}"
            method="post"
            x-data="{ 
                step: 1, 
                selectedRole: 'viewer',
                availableCredits: {{ $team->user?->total_credits ?? $team->user?->total_credit ?? 0 }},
                creditsToAllocate: {{ ($team->user?->total_credits ?? $team->user?->total_credit ?? 0) < 10 ? ($team->user?->total_credits ?? $team->user?->total_credit ?? 0) : 0 }},
                email: '',
                updateCreditsForRole() {
                    if (this.selectedRole === 'viewer') {
                        this.creditsToAllocate = 0;
                    }
                }
            }"
            x-init="updateCreditsForRole()"
        >
            @csrf
        
            <input
                type="hidden"
                name="team_id"
                value="{{ $team?->id }}"
            >

            <!-- ========== STEP 1: Email + Role Selection ========== -->
            <div x-show="step === 1" x-transition class="space-y-4">
                <!-- Email Input -->
                <x-forms.input
                    id="email"
                    size="lg"
                    type="email"
                    name="email"
                    placeholder="{{ __('Email address') }}"
                    required
                    x-model="email"
                >
                    <x-slot:icon>
                        <x-tabler-mail class="size-4 size-5 absolute end-3 top-1/2 -translate-y-1/2" />
                    </x-slot:icon>
                </x-forms.input>

                <!-- Role Selection (Email के नीचे) -->
                <div class="space-y-3">
    <label class="text-xs font-semibold text-heading-foreground">
        {{ __('Select Role') }}
    </label>
    <div class="flex gap-6">
        <label class="flex items-start cursor-pointer gap-2">
            <input
                type="radio"
                name="role"
                value="creator"
                x-model="selectedRole"
                @change="updateCreditsForRole()"
                class="w-4 h-4 mt-0.5"
            />
            <div class="flex flex-col">
                <span class="text-[14px]">{{ __('Creator') }}</span>
                <span class="text-[13px] text-gray-500">{{ __('(Can Generate, View & Download)') }}</span>
            </div>
        </label>
        
        <label class="flex items-start cursor-pointer gap-2">
            <input
                type="radio"
                name="role"
                value="viewer"
                x-model="selectedRole"
                @change="updateCreditsForRole()"
                class="w-4 h-4 mt-0.5"
            />
            <div class="flex flex-col">
                <span class="text-[14px]">{{ __('Viewer') }}</span>
                <span class="text-[13px] text-gray-500">{{ __('(Can Only View & Download)') }}</span>
            </div>
        </label>
    </div>
    
    <!-- ✅ Message for Viewer Role -->
    <div x-show="selectedRole === 'viewer'" x-transition class="bg-blue-50 border border-blue-200 rounded-md p-3 mt-2">
        <p class="text-sm text-blue-800">
            <strong>ℹ️ Viewer Role:</strong> {{ __('Viewers do not need credits allocation. They can only view and download content.') }}
        </p>
    </div>
</div>

                <!-- Next Button -->
                <x-button 
                    type="button"
                    class="w-full"
                    @click="updateCreditsForRole(); step = 2"
                    x-bind:disabled="!email"
                >
                    {{ __('Next') }}
                </x-button>
            </div>

            <!-- ========== STEP 2: Credits Allocation (Creator) or Summary (Viewer) ========== -->
            <div x-show="step === 2" x-transition class="space-y-4">
                
                <!-- ✅ Hidden input to always submit credits_to_allocate -->
                <input 
                    type="hidden" 
                    name="credits_to_allocate" 
                    x-bind:value="creditsToAllocate"
                    value="0"
                >

                <!-- ✅ For CREATOR Role: Show Credits Section -->
                <div x-show="selectedRole === 'creator'" x-transition class="space-y-4">
                    <!-- Available Credits Display -->
                    <div class="bg-primary/10 rounded-lg p-4">
                        <p class="text-sm text-heading-foreground">
                            <span class="font-semibold">{{ __('Available Credits') }}:</span>
                            <span class="text-lg font-bold text-primary" x-text="availableCredits"></span>
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            {{ __('Allocated credits are deducted from the team owner`s account') }}
                        </p>
                    </div>

                    <!-- Credits Allocation Input -->
                    <div class="space-y-3">
                        <label for="credits_to_allocate" class="text-sm  text-heading-foreground">
                            {{ __('Credits to Allocate') }}
                        </label>
                        <x-forms.input
                            id="credits_to_allocate"
                            type="number"
                            name="credits_to_allocate_display"
                            x-model.number="creditsToAllocate"
                            x-bind:max="availableCredits"
                            x-bind:min="availableCredits < 10 ? 1 : 10"
                            x-bind:required="selectedRole === 'creator'"
                            x-bind:disabled="selectedRole !== 'creator'"
                            placeholder="{{ __('Enter number of credits') }}"
                            size="lg"
                        />
                        <p class="text-xs text-gray-500" x-show="availableCredits >= 10">
                            Minimum: 10 credits
                        </p>
                        <p class="text-xs text-gray-500" x-show="availableCredits < 10">
                            {{ __('Less than 10 credits are available, so the full available amount is prefilled.') }}
                        </p>
                    </div>
                </div>

                <!-- ✅ For VIEWER Role: Show Info Message -->
                <div x-show="selectedRole === 'viewer'" x-transition class="space-y-4">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                        <p class="text-sm font-semibold text-green-800 mb-2">
                            ✅ {{ __('Viewer Role Selected') }}
                        </p>
                        <p class="text-sm text-green-700">
                            {{ __('This team member will be invited as a Viewer.') }} <br>
                            {{ __('They will not receive any credits and can only view & download content.') }}
                        </p>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex gap-2">
                    <x-button 
                        type="button"
                        variant="outline"
                        class="w-1/2"
                        @click="step = 1"
                    >
                        {{ __('Back') }}
                    </x-button>
                    @if ($app_is_demo)
                        <x-button 
                            type="button"
                            class="w-1/2"
                            onclick="return toastr.info('This feature is disabled in Demo version.')"
                        >
                            {{ __('Invite Friends') }}
                        </x-button>
                    @else
                        <x-button 
                            type="submit"
                            class="w-1/2"
                        >
                            {{ __('Invite Member ') }}
                        </x-button>
                    @endif
                </div>
            </div>
        </form>
    </x-card>
</div>

<style>
    input[type="radio"]:disabled {
        @apply opacity-50 cursor-not-allowed;
    }
    
    button:disabled {
        @apply opacity-50 cursor-not-allowed;
    }
</style>
