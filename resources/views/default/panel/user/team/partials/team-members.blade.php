<x-table class="lqd-team-members-table">
    <x-slot:head>
        <tr>
            <th>
                @lang('Member')
            </th>
            <th>
                @lang('Email')
            </th>
            <th>
                @lang('Role')
            </th>
            <th>
                @lang('Status')
            </th>
            <!-- <th class="text-center">
                @lang('Allocated Credits')
            </th> -->
            <th class="text-center">
                @lang('Used Credits')
            </th>
            <th class="text-center">
                @lang('Remaining Credits')
            </th>
            <th class="text-end">
                @lang('Actions')
            </th>
        </tr>
    </x-slot:head>

    <x-slot:body>
        @foreach ($members as $member)
            <tr>
                <!-- Member Name -->
                <td>
                    @if ($member->user_id)
                        <div>
                            <p class="m-0 font-medium">
                                {{ $member?->user?->name . ' ' . $member?->user?->surname }}
                                @if ($member->id === auth()->user()->id || $member->user_id === auth()->user()->id)
                                    <span class="opacity-60">(You)</span>
                                @endif
                            </p>
                        </div>
                    @else
                        <p class="m-0 font-medium">{{ __('Pending') }}</p>
                    @endif
                </td>

                <!-- Email -->
                <td>
                    <p class="m-0">{{ $member->email }}</p>
                </td>

                <!-- Role -->
                <td>
                    <p class="m-0">{{ $member->role ?: __('unknown') }}</p>
                </td>

                <!-- Status -->
                <td>
                    <x-badge variant="{{ $member->status == 'waiting' ? 'secondary' : ($member->status == 'active' ? 'success' : 'danger') }}">
                        @lang(ucfirst($member->status))
                    </x-badge>
                </td>

                <!-- User Credits (user's own balance from total_credit) -->
                <!-- <td class="text-center">
                    @if ($member->team_role === 1 || strtolower($member->role ?? '') === 'creator')
                        <p class="m-0">{{ $member->allocated_credits ?? 0 }}</p>
                    @else
                        <p class="m-0 opacity-60">–</p>
                    @endif
                </td> -->

                <!-- Used Credits (Creators only) -->
                <td class="text-center">
                    @if ($member->team_role === 1 || strtolower($member->role ?? '') === 'creator')
                        <p class="m-0">{{ $member->used_credits ?? 0 }}</p>
                    @else
                        <p class="m-0 opacity-60">–</p>
                    @endif
                </td>

                <!-- Remaining Credits (Creators only) -->
                <td class="text-center">
                    @if ($member->team_role === 1 || strtolower($member->role ?? '') === 'creator')
                        <p class="m-0">{{ $member->remaining_credits ?? 0 }}</p>
                    @else
                        <p class="m-0 opacity-60">–</p>
                    @endif
                </td>

                <!-- Actions -->
                <td class="whitespace-nowrap text-end">
                    <!-- Edit Button -->
                    <x-button
                        class="size-9"
                        variant="ghost-shadow"
                        size="none"
                        href="{{ route('dashboard.user.team.member.edit', [$team->id, $member->id]) }}"
                        title="{{ __('Edit') }}"
                    >
                        <x-tabler-pencil class="size-4" />
                    </x-button>

<form
    action="{{ route('dashboard.user.team.member.suspend', [$team->id, $member->id]) }}"
    method="POST"
    class="inline"
>
    @csrf
    <x-button
       class="size-9"
        variant="ghost-shadow"
        size="none"
        type="submit"
        title="{{ $member->status == 'suspended' ? __('Activate') : __('Suspend') }}"
    >
        @if ($member->status == 'suspended')
            <x-tabler-user-check class="size-4" />
        @else
            <x-tabler-user-off class="size-4" />
        @endif
    </x-button>
</form>

                    <!-- Delete Button -->
                    <x-button
                        class="size-9"
                        variant="ghost-shadow"
                        hover-variant="danger"
                        size="none"
                        href="{{ route('dashboard.user.team.member.delete', [$team->id, $member->id]) }}"
                        onclick="return confirm('Are you sure? This is permanent and will delete all documents related to user.')"
                        title="{{ __('Delete') }}"
                    >
                        <x-tabler-trash class="size-4" /> 
                    </x-button>
                </td>
            </tr>
        @endforeach
    </x-slot:body>
</x-table>