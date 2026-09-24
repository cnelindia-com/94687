@extends('panel.layout.settings')
@section('title', __('Edit') . ' ' . $user?->fullName())
@section('titlebar_actions', '')

@section('settings')
    <form onsubmit="return userSave({{ $user->id }});">
        <div class="space-y-7">
            <div class="grid grid-cols-2 gap-x-4 gap-y-5">
                <x-forms.input-
                    id="name"
                    type="text"
                    name="name"
                    size="lg"
                    label="{{ __('Name') }}"
                    value="{{ $user->name }}"
                />

                <x-forms.input
                    id="surname"
                    type="text"
                    name="surname"
                    size="lg"
                    label="{{ __('Surname') }}"
                    value="{{ $user->surname }}"
                />

                <x-forms.input
                    id="phone"
                    data-mask="+0000000000000"
                    type="text"
                    name="phone"
                    size="lg"
                    placeholder="+000000000000"
                    label="{{ __('Phone') }}"
                    value="{{ $user->phone }}"
                />

                <x-forms.input
                    id="email"
                    type="email"
                    name="email"
                    size="lg"
                    label="{{ __('Email') }}"
                    value="{{ $user->email }}"
                />

                <x-forms.input
                    id="country"
                    container-class="w-full col-span-2"
                    type="select"
                    name="country"
                    size="lg"
                    label="{{ __('Country') }}"
                >
                    @include('panel.admin.users.countries')
                </x-forms.input>

                <x-forms.input
                    id="type"
                    type="select"
                    name="type"
                    size="lg"
                    label="{{ __('Role') }}"
                >
                    @foreach (App\Enums\Roles::cases() as $role)
                        <option
                            value="{{ $role }}"
                            {{ $user->type === $role ? 'selected' : '' }}
                        >
                            {{ $role->label() }}
                        </option>
                    @endforeach
                </x-forms.input>

                <x-forms.input
                    id="status"
                    type="select"
                    name="status"
                    size="lg"
                    label="{{ __('Status') }}"
                >
                    <option
                        value="1"
                        {{ $user->status == 1 ? 'selected' : '' }}
                    >
                        {{ __('Active') }}
                    </option>
                    <option
                        value="0"
                        {{ $user->status == 0 ? 'selected' : '' }}
                    >
                        {{ __('Passive') }}
                    </option>
                </x-forms.input>
            </div>

             <x-forms.input
                id="plan_id"
                type="select"
                name="plan_id"
                size="lg"
                label="{{ __('Plan') }}"
            >
                <option
                    value=""
                    {{ empty($selectedPlanId) ? 'selected' : '' }}
                >
                    {{ __('Select Plan') }}
                </option>

                <optgroup label="{{ __('Monthly Plans') }}">
                    @foreach ($plansSubscriptionMonthly ?? [] as $plan)
                        <option
                            value="{{ $plan->id }}"
                            {{ (isset($selectedPlanId) && (int) $selectedPlanId === (int) $plan->id) ? 'selected' : '' }}
                        >
                            {{ $plan->name }} - {{ $plan->price }} {{ $plan->currency }} / {{ __('Monthly') }}
                        </option>
                    @endforeach
                </optgroup>

                <optgroup label="{{ __('Yearly Plans') }}">
                    @foreach ($plansSubscriptionAnnual ?? [] as $plan)
                        <option
                            value="{{ $plan->id }}"
                            {{ (isset($selectedPlanId) && (int) $selectedPlanId === (int) $plan->id) ? 'selected' : '' }}
                        >
                            {{ $plan->name }} - {{ $plan->price }} {{ $plan->currency }} / {{ __('Yearly') }}@if(!empty($plan->total_credits)) ({{ $plan->total_credits }} {{ __('Credits') }})@endif
                        </option>
                    @endforeach
                </optgroup>
            </x-forms.input>

            @if (auth()->user()?->isSuperAdmin())
                <div class="space-y-4 rounded-lg border border-foreground/10 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-2xs font-medium">{{ __('User Current Credits') }}</span>
                        <span class="text-sm font-semibold">{{ number_format((int) ($user->total_credit ?? 0)) }}</span>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-x-4 gap-y-5">
                            <x-forms.input
                                id="credit_amount"
                                type="number"
                                name="credit_amount"
                                size="lg"
                                min="1"
                                step="1"
                                required
                                form="credit-adjust-form"
                                label="{{ __('Credit Amount') }}"
                                placeholder="{{ __('Enter amount') }}"
                            />

                            <x-forms.input
                                id="credit_action"
                                type="select"
                                name="credit_action"
                                size="lg"
                                form="credit-adjust-form"
                                label="{{ __('Action') }}"
                            >
                                <option value="add">{{ __('Add Credits') }}</option>
                                <option value="remove">{{ __('Remove Credits') }}</option>
                            </x-forms.input>
                        </div>

                        <x-button
                            class="w-full"
                            type="submit"
                            variant="outline"
                            size="lg"
                            form="credit-adjust-form"
                        >
                            {{ __('Update Credits') }}
                        </x-button>
                    </div>
                </div>
            @else
                <div class="flex items-center justify-between rounded-lg border border-foreground/10 px-4 py-3">
                    <span class="text-2xs font-medium">{{ __('User Current Credits') }}</span>
                    <span class="text-sm font-semibold">{{ number_format((int) ($user->total_credit ?? 0)) }}</span>
                </div>
            @endif

            <div x-data="{ showContent: false }">
                <x-button
                    class="flex w-full items-center justify-between gap-7 py-3 text-2xs"
                    type="button"
                    variant="link"
                    @click="showContent = !showContent"
                >
                    <span class="h-px grow bg-current opacity-10"></span>
                    <span class="flex items-center gap-3">
                        {{ __('Credits') }}
                        <x-tabler-chevron-down
                            class="size-4 transition"
                            ::class="{ 'rotate-180': showContent }"
                        />
                    </span>
                    <span class="h-px grow bg-current opacity-10"></span>
                </x-button>
                <div
                    class="hidden pt-5"
                    :class="{ hidden: !showContent }"
                >
                    @livewire('assign-view-credits', ['entities' => $user->entity_credits])
                </div>
            </div>

            <x-button
                class="w-full"
                type="submit"
                variant="outline"
                size="lg"
                form="password-reset-form"
            >
                {{ __('Send Password Reset Link') }}
            </x-button>

            <x-button
                class="w-full"
                id="user_edit_button"
                type="submit"
                size="lg"
            >
                {{ __('Save') }}
            </x-button>
        </div>
    </form>

    <form
        id="password-reset-form"
        method="POST"
        action="{{ route('dashboard.admin.users.sendPasswordReset', $user) }}"
        class="hidden"
    >
        @csrf
    </form>

    @if (auth()->user()?->isSuperAdmin())
        <form
            id="credit-adjust-form"
            method="POST"
            action="{{ route('dashboard.admin.users.credits.update', $user) }}"
            class="hidden"
        >
            @csrf
        </form>
    @endif
@endsection

@push('script')
    <script src="{{ custom_theme_url('/assets/js/panel/user.js') }}"></script>
@endpush
