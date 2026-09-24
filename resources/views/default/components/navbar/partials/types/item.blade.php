@if (data_get($item, 'show_condition', true) && (! data_get($item, 'is_admin') || (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))))
    @php
        $href = '';
        $routeSlug = data_get($item, 'route_slug');
        $routeName = data_get($item, 'route');
        if ($routeSlug && \App\Helpers\Classes\Helper::hasRoute($routeName)) {
            $href = route($routeName, $routeSlug);
        } elseif (\App\Helpers\Classes\Helper::hasRoute($routeName)) {
            $href = route($routeName);
        } else {
            $href = $routeName;
        }
        $activeConditionArr = data_get($item, 'active_condition') ?: [];
        if (! is_array($activeConditionArr)) {
            $activeConditionArr = $activeConditionArr ? [$activeConditionArr] : [];
        }
        $is_active = $href === url()->current()
            || ($activeConditionArr !== [] && activeRoute(...$activeConditionArr) === 'active');
    @endphp

    <x-navbar.item id="  {{ data_get($item, 'parent_key') ? data_get($item, 'parent_key') . '-' : '' }}{{ data_get($item, 'key') }}">
        <x-navbar.link
            class:letter-icon="{{ data_get($item, 'letter_icon_bg') }}"
            class="{{ data_get($item, 'class') }}"
            data-name="{{ data_get($item, 'data-name') }}"
            letter-icon-styles="{{ data_get($item, 'letter_icon_bg') }}"
            label="{!! __(data_get($item, 'label')) !!}"
            href="{{ $href }}"
            slug="{{ $routeSlug }}"
            icon="{{ data_get($item, 'icon') }}"
            active-condition="{{ $is_active }}"
            letter-icon="{{ (int) data_get($item, 'letter_icon', 0) }}"
            onclick="{{ data_get($item, 'onclick') ?? '' }}"
            badge="{{ data_get($item, 'badge') ?? '' }}"
        />
    </x-navbar.item>
@endif
