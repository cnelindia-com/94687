@if(\App\Helpers\Classes\Helper::appIsNotDemo())
	<x-button
		class="lqd-image-generator-tabs-trigger py-2 text-2xs font-bold text-heading-foreground hover:shadow-none [&.active]:bg-foreground/10"
		data-generator-name="nano-banana"
		tag="button"
		type="button"
		variant="ghost"
		x-data="{}"
		::class="{ 'active': activeGenerator === 'nano-banana' }"
		x-bind:data-active="activeGenerator === 'nano-banana'"
		@click="changeActiveGenerator('nano-banana')"
	>
		@lang('Nano Banana')
	</x-button>
@endif

