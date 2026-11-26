@php
	
	 $items = app(\App\Services\Common\MenuService::class)->generate();

	$user = auth()->user();
	$isAdmin = $user?->isAdmin();
@endphp
<!--lucky -->
<!-- pritam sidebar menu-->
@foreach ($items as $key => $item)
    @if($key==='links' || $key==='user_label')
        @continue
    @endif
    @php
        // Cache values once
        $isActive       = data_get($item, 'is_active', false);
        $showCondition  = data_get($item, 'show_condition', true);
        $isAdminOnly    = data_get($item, 'is_admin', false);
        $childrenCount  = count(data_get($item, 'children', []) ?: []) ?: (int) data_get($item, 'children_count', 0);
        $type           = data_get($item, 'type');
        $parentKey      = data_get($item, 'parent_key');

        // Skip early if plan doesn't allow it
        if (!\App\Helpers\Classes\PlanHelper::planMenuCheck($userPlan, $key)) {
            continue;
        }

        // Skip if inactive or condition fails
        if (!$isActive || !$showCondition) {
            continue;
        }

        // Support Settings should be visible only to super admin
        if ($key === 'support_settings' && (!auth()->check() || !auth()->user()?->isSuperAdmin())) {
            continue;
        }

        // Skip if admin-only and user not allowed
        if ($isAdminOnly && (!$isAdmin || !$user->checkPermission($key))) {
            continue;     
        }
    @endphp

    {{-- Hide AI Fashion Studio parent; keep its children as normal top-level items --}}
    @if ($key === 'ext_fashion_studio_dropdown')
        @foreach (data_get($item, 'children', []) ?: [] as $fashionChild)
            @php
                $fashionKey = data_get($fashionChild, 'key');
            @endphp
            @if (!data_get($fashionChild, 'is_active', false) || !data_get($fashionChild, 'show_condition', true))
                @continue
            @endif
            @if (!\App\Helpers\Classes\PlanHelper::planMenuCheck($userPlan, $fashionKey))
                @continue
            @endif
            @php
                $item = $fashionChild;
                $type = data_get($fashionChild, 'type', 'item');
            @endphp
            @includeIf('default.components.navbar.partials.types.' . $type)
        @endforeach
        @continue
    @endif

    {{-- Force top-level render for ext_fashion_studio_dropdown children --}}
    @if ($parentKey === 'ext_fashion_studio_dropdown')
        @includeIf('default.components.navbar.partials.types.' . $type)
    @elseif ($childrenCount)
        @includeIf('default.components.navbar.partials.types.item-dropdown')
    @else
        @includeIf('default.components.navbar.partials.types.' . $type)
    @endif

	 {{-- Show Credits Below Support --}}
    @if ($key === 'support' && auth()->check())
        @php
            $authUser = auth()->user();
            $userCredits = $authUser->total_credit ?? $authUser->total_credits ?? 0;
        @endphp

         <x-navbar.item id="credits">
        <x-navbar.link
            label="{{ __('Credits') . ': ' . $userCredits }}"
            href="{{ route('dashboard.user.payment.subscription') }}"
            icon="tabler-coins"
            active-condition="request()->routeIs('dashboard.user.payment.subscription')"
            target="_self"
        />
    </x-navbar.item>
    @endif
@endforeach
