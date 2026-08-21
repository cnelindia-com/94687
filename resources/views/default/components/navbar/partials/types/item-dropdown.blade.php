@php
	$href =
		\App\Helpers\Classes\Helper::hasRoute($item['route']) && $item['route_slug']
			? route($item['route'], $item['route_slug'])
			: route(\App\Helpers\Classes\Helper::hasRoute($item['route']) ? $item['route'] : 'default');

	$is_active = $href === url()->current();

	if (!$is_active) {
		foreach ($item['children'] as $child) {
			if (!Route::has($child['route'])) {
				continue;
			}

			$child_href = $child['route_slug'] ? route($child['route'], $child['route_slug']) : route($child['route']);
			$child_is_active = $child_href === url()->current();

			if ($child_is_active) {
				$is_active = true;
				break;
			}
		}
	}

	// Check if this item is in the bolt menu (middle_nav_items)
	$middle_nav_urls = app(\App\Services\Common\MenuService::class)->boltMenu();
	$is_bolt_parent = isset($item['key']) && in_array($item['key'], array_keys($middle_nav_urls));
@endphp

<x-navbar.item
	id="{{ data_get($item, 'parent_key') ? data_get($item, 'parent_key') . '-' : '' }}{{ data_get($item, 'key') }}"
	has-dropdown
>
<!-- pritam -->

	<x-navbar.dropdown.dropdown open="true">
		@foreach ($item['children'] as $child)
			@php
				$key = data_get($child, 'key');
			@endphp

			@if (\App\Helpers\Classes\PlanHelper::planMenuCheck($userPlan, $key))
				@if (data_get($child, 'show_condition', true) && data_get($item, 'is_active'))
					@php
						$child_href =
							$child['route_slug'] && \App\Helpers\Classes\Helper::hasRoute($child['route'])
								? route($child['route'], $child['route_slug'])
								: route(\App\Helpers\Classes\Helper::hasRoute($child['route']) ? $child['route'] : 'default');

						$child_is_active = $child_href === url()->current();

						// Hide icon for bolt menu items in dropdown when parent is in bolt menu
						$dropdown_icon = ($is_bolt_parent && data_get($child, 'bolt_menu')) ? '' : ($child['icon'] ?? '');

						// same active class like parent
						$child_class = $child_is_active ? 'testaihwe' : '';
					@endphp

					<x-navbar.dropdown.item>
						<x-navbar.dropdown.link
							class="{{ $child_class }}"
							icon="{{ $dropdown_icon }}"
							label="{{ __($child['label']) }}"
							href="{{ $child['route'] }}"
							badge="{{ data_get($child, 'badge') ?? '' }}"
							slug="{{ $child['route_slug'] }}"
							active-condition="{{ $child_is_active }}"
							onclick="{{ data_get($child, 'onclick') ?? '' }}"
						></x-navbar.dropdown.link>
					</x-navbar.dropdown.item>
				@endif
			@endif
		@endforeach
	</x-navbar.dropdown.dropdown>
</x-navbar.item>
<style>
    .testaihwe {
        background-color: #f3eef9 !important;
        border-radius: 12px !important;
        color: hsl(var(--navbar-foreground-active)) !important;
		text-decoration-line: none !important;
    }
</style>