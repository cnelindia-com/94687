<x-form-step
	step="11"
	label="{{ __('Marketing Bot Theme Tools Section') }}"
>
</x-form-step>
<div class="col-md-12">
	<div class="mb-3">
		<label class="form-label">{{ __('Marketing Tools') }}</label>
		<select
			class="form-select"
			id="marketing_tools"
			name="marketing_tools"
		>
			<option
				value="1"
				{{ setting('marketing_tools', '1') === '1' ? 'selected' : '' }}
			>
				{{ __('Active') }}</option>
			<option
				value="0"
				{{ setting('marketing_tools', '1') === '0' ? 'selected' : '' }}
			>
				{{ __('Passive') }}</option>
		</select>
	</div>
	<div class="mb-3">
		<label class="form-label">{{ __('Marquee') }}</label>
		<select
			class="form-select"
			id="marquee_active"
			name="marquee_active"
		>
			<option
				value="1"
				{{ setting('marquee_active', '1') === '1' ? 'selected' : '' }}
			>
				{{ __('Active') }}</option>
			<option
				value="0"
				{{ setting('marquee_active', '1') === '0' ? 'selected' : '' }}
			>
				{{ __('Passive') }}</option>
		</select>
	</div>
	<div class="mb-3">
		<label class="form-label">{{ __('Vertical Slider') }}</label>
		<select
			class="form-select"
			id="vertical_slider_active"
			name="vertical_slider_active"
		>
			<option
				value="1"
				{{ setting('vertical_slider_active', '1') === '1' ? 'selected' : '' }}
			>
				{{ __('Active') }}</option>
			<option
				value="0"
				{{ setting('vertical_slider_active', '1') === '0' ? 'selected' : '' }}
			>
				{{ __('Passive') }}</option>
		</select>
	</div>
</div>
