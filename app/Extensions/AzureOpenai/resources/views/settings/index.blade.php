@use(\App\Domains\Entity\Enums\EntityEnum)
@extends('panel.layout.settings', ['layout' => 'wide'])
@section('title', __('Azure Openai Settings'))
@section('titlebar_actions', '')
@section('titlebar_subtitle', __('This API key is used for all AI-powered features, including AI Chat and Content Writing'))

@section('additional_css')
@endsection

@section('settings')
	<form class="grid grid-cols-1 gap-5" id="azure_openai_form" method="POST" action="{{ route('dashboard.admin.settings.azure-openai.store') }}">
		@csrf
		<x-alert class="mt-2">
			<x-button
				variant="link"
				href="https://docs.magicproject.ai/azure-openai-integration"
				target="_blank"
			>
				{{ __('Documentation') }}
			</x-button>
		</x-alert>

		<!-- Domain Name Field -->
		<x-forms.input
			id="azure_domain"
			size="lg"
			type="text"
			name="azure_domain"
			label="{{ __('Azure Domain') }}"
			placeholder="{{ __('Enter your Azure domain (e.g., mydomain.openai.azure.com)') }}"
			tooltip="{{ __('Please ensure to enter your domain name only without .openai.azure.com')}}"
			value="{{ setting('azure_domain') }}"
			required
		/>
		<!-- Deployed Models Field -->
		<x-forms.input
			id="deployed_models"
			size="lg"
			type="text"
			name="deployed_models"
			label="{{ __('Deployed Model') }}"
			placeholder="{{ __('gpt-4o or gpt-4 or gpt-3.5-turbo and etc.') }}"
			tooltip="{{ __('Please enter the deployed model')}}"
			value="{{ setting('deployed_models') }}"
			required
		/>
		<!-- API Key Field -->
		<x-forms.input
			id="azure_api_key"
			size="lg"
			type="text"
			name="azure_api_key"
			label="{{ __('Azure API Key') }}"
			placeholder="{{ __('Enter your Azure API key') }}"
			tooltip="{{ __('Please enter your Azure API key')}}"
			value="{{ setting('azure_api_key') }}"
			required
		/>
		<!-- API Version Field -->
		<x-forms.input
			id="azure_api_version"
			size="lg"
			type="text"
			name="azure_api_version"
			label="{{ __('Azure API Version') }}"
			placeholder="{{ __('Enter your Azure API version (e.g., 2023-05-15)') }}"
			tooltip="{{ __('Please enter your Azure API version')}}"
			value="{{ setting('azure_api_version') }}"
			required
		/>

		<x-card
			class="pt-5 p-3"
			size="none"
			variant="shadow"
		>
			@php
				$entity = \App\Domains\Entity\Facades\Entity::driver(EntityEnum::AZURE_OPENAI);
				$plans = \App\Models\Plan::query()->where('type', 'subscription')->get();
			@endphp
			<x-forms.input
				type="text"
				size="lg"
				name="selected_title[{{ $entity->model()->id }}]"
				value="{!! $entity->model()->selected_title !!}"
				label="{{ __('Activate ' . $entity->enum()->label()) }}"
				tooltip="{{ __('Activate or deactivate the AI model') }}"
			>

				<x-dropdown.dropdown class="mt-2 w-full">
					<x-slot:trigger
						class="w-full justify-start text-start"
					>
						<small>{{__('View Included Pricing Plans')}}</small>
						<x-tabler-arrow-down class="size-3"/>
					</x-slot:trigger>

					<x-slot:dropdown
						class="min-w-52"
					>
						<div class="p-2 text-2xs">
							@foreach ($plans as $plan)
								@php
									$checked = $entity->model()->aiFinance?->pluck('plan_id')?->toArray() ?: [];
								@endphp
								<x-forms.input
									class:container="h-full bg-input-background mt-2"
									class:label="w-full border h-full rounded px-3 py-4 hover:bg-foreground/5 transition-colors"
									id="ai_model_{{ $entity->enum()->value . '_' . $plan->id }}"
									:checked="in_array($plan->id, $checked, true)"
									type="checkbox"
									name="selected_plans[{{ $entity->model()->id }}][{{ $plan->id }}]"
									value="{{ $plan->id }}"
									label="{{ $plan->name }}"
									custom
								/>
							@endforeach

							<x-forms.input
								class:container="h-full bg-input-background mt-2"
								class:label="w-full border h-full rounded px-3 py-4 hover:bg-foreground/5 transition-colors"
								id="ai_model_{{ $entity->enum()->value.'_no_plan_users' }}"
								:checked="$entity->model()->is_selected === 1"
								type="checkbox"
								name="no_plan_users[{{  $entity->model()->id }}]"
								value="{{ $entity->model()->id }}"
								label="{{ trans('No Plan Users') }}"
								custom
							/>

						</div>

					</x-slot:dropdown>
				</x-dropdown.dropdown>
			</x-forms.input>
</x-card>

		<!-- Submit Button -->
		<x-button
			class="w-full"
			id="azure_openai_save_button"
			size="lg"
			type="submit"
		>
			{{ __('Save Configuration') }}
		</x-button>
	</form>
@endsection

@push('script')
@endpush
