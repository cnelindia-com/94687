@extends('panel.layout.settings', ['layout' => 'wide'])
@section('title', __('Settings'))
@section('titlebar_subtitle', __('Manage your photoshoot and account settings in one place'))

@section('settings')
    <!-- ========== SECTION 1: PHOTOSHOOT SETTINGS ========== -->
    <div class="mb-8">
        <h3 class="mb-[25px] text-[20px]">{{ __('Photoshoot Generation Settings') }}</h3>
        
        <form method="post" action="{{ route('dashboard.user.fashion-studio.user_settings.update') }}" id="settings_form">
            @csrf
            
            @if(isset($isSuperadmin) && $isSuperadmin)
                <div class="flex gap-3 mb-4">
                    <x-button variant="secondary" class="flex-1" type="button" onclick="window.open('{{ route('dashboard.user.fashion-studio.default-models.index') }}', '_blank')">
                        {{ __('Default Models') }}
                    </x-button>
                    <x-button variant="secondary" class="flex-1" type="button" onclick="window.open('{{ route('fashion-studio.default-poses') }}', '_blank')">
                        {{ __('Default Poses') }}
                    </x-button>
                    <x-button variant="secondary" class="flex-1" type="button" onclick="window.open('{{ route('fashion-studio.default-backgrounds') }}', '_blank')">
                        {{ __('Default Backgrounds') }}
                    </x-button>
                </div>
            @endif

            <x-card class="mb-3 max-md:text-center" size="lg">
                <div class="col-md-12 space-y-4">
                    <x-forms.input id="num_images" name="num_images" type="select" label="{{ __('Number of Generated Images') }}" tooltip="{{ __('Choose how many images to generate per photoshoot. More images use more credits.') }}">
                        @for ($i = 1; $i <= ($maxImages ?? 4); $i++)
                            <option value="{{ $i }}" @selected((int)($settings->num_images ?? 1) === $i)>{{ $i }} {{ $i === 1 ? __('image') : __('images') }}</option>
                        @endfor
                    </x-forms.input>

                    <x-forms.input id="resolution" name="resolution" type="select" label="{{ __('Resolution') }}" tooltip="{{ __('Higher resolution produces better quality images but may take longer to generate.') }}">
                        @foreach (($resolutions ?? ['1024x1024', '768x1024', '1024x768']) as $resolution)
                            <option value="{{ $resolution }}" @selected(($settings->resolution ?? '1024x1024') === $resolution)>{{ $resolution }}</option>
                        @endforeach
                    </x-forms.input>

                    <x-forms.input id="ratio" name="ratio" type="select" label="{{ __('Ratio') }}" tooltip="{{ __('Select the aspect ratio for your generated images.') }}">
                        @foreach (($ratios ?? ['1:1', '4:3', '16:9']) as $ratio)
                            <option value="{{ $ratio }}" @selected(($settings->ratio ?? '1:1') === $ratio)>{{ $ratio }}</option>
                        @endforeach
                    </x-forms.input>
                </div>
            </x-card>

            <x-button variant="primary" class="w-full mb-5" type="submit">
                {{ __('Save Photoshoot Settings') }}
            </x-button>
        </form>
    </div>

    <!-- Divider -->
    <hr class="my-8">

    <!-- ========== SECTION 2: ACCOUNT SETTINGS ========== -->
    <div class="mt-8">
        <h3 class="mb-[25px] text-[20px]">{{ __('Account Settings') }}</h3>
        
        <form id="user_edit_form" onsubmit="return userProfileSave();" enctype="multipart/form-data">
            @csrf
            <x-card class="max-md:text-center" size="lg">
                <div class="mb-[10px]">
                    <label class="form-label">{{ __('Avatar') }}</label>
                    <input class="form-control" id="avatar" type="file" name="avatar" accept="image/*">
                </div>
                
                <div class="mb-[10px]">
                    <label class="form-label">{{ __('Name') }}</label>
                    <input class="form-control" id="name" type="text" name="name" value="{{ Auth::user()->name ?? '' }}">
                </div>
                
                <div class="mb-[10px]">
                    <label class="form-label">{{ __('Surname') }}</label>
                    <input class="form-control" id="surname" type="text" name="surname" value="{{ Auth::user()->surname ?? '' }}">
                </div>
                
                <div class="mb-[10px]">
                    <label class="form-label">{{ __('Phone') }}</label>
                    <input class="form-control" id="phone" data-mask="+0000000000000" data-mask-visible="true" type="text" name="phone" placeholder="+000000000000" autocomplete="off" value="{{ Auth::user()->phone ?? '' }}">
                </div>
                
                <div class="mb-[10px]">
                    <label class="form-label">{{ __('Email') }}</label>
                    <input class="form-control" type="email" value="{{ Auth::user()->email ?? '' }}" disabled>
                </div>
                
                <div class="mb-[10px]">
                    <label class="form-label">{{ __('Address Line 1') }}</label>
                    <x-forms.input id="address" type="text" name="address" value="{{ Auth::user()->address ?? '' }}" />
                </div>
                
                <div class="mb-[10px]">
                    <label class="form-label">{{ __('Postal Code') }}</label>
                    <x-forms.input id="postal" type="text" name="postal" value="{{ Auth::user()->postal ?? '' }}" />
                </div>
                
                <div class="mb-[10px]">
                    <label class="form-label">{{ __('City') }}</label>
                    <x-forms.input id="city" type="text" name="city" value="{{ Auth::user()->city ?? '' }}" />
                </div>
                
                <div class="mb-[10px]">
                    <label class="form-label">{{ __('State') }}</label>
                    <x-forms.input id="state" type="text" name="state" value="{{ Auth::user()->state ?? '' }}" />
                </div>
                
                <div class="mb-[10px]">
                    <label class="form-label">{{ __('Country') }}</label>
                    <select class="form-select" id="country" name="country">
                        <option value="">{{ __('Select Country') }}</option>
                        @include('panel.admin.users.countries')
                    </select>
                </div>
                
                <hr class="my-5">
                
                <h4>@lang('Change Password')</h4>
                <x-alert class="!mt-2 mb-3">
                    <p>{{ __('Please leave empty if you don’t want to change your password.') }}</p>
                </x-alert>
                
                <div class="mb-[10px]">
                    <label class="form-label">{{ __('Old Password') }}</label>
                    <input class="form-control" autocomplete="off" id="old_password" type="password" name="old_password">
                </div>
                
                <div class="mb-[10px]">
                    <label class="form-label">{{ __('New Password') }}</label>
                    <input class="form-control" autocomplete="off" id="new_password" type="password" name="new_password">
                </div>
                
                <div class="mb-[10px]">
                    <label class="form-label">{{ __('Confirm Your New Password') }}</label>
                    <input class="form-control" id="new_password_confirmation" type="password" name="new_password_confirmation" autocomplete="off">
                </div>

                @if(isset($app_is_demo) && $app_is_demo && Auth::user() && Auth::user()->isAdmin())
                    <a class="btn btn-primary w-full" onclick="return toastr.info('Admin settings disabled on Demo version.')">
                        {{ __('Save') }}
                    </a>
                @else
                    <button class="btn btn-primary w-full" id="user_edit_button" form="user_edit_form">
                        {{ __('Save Account Settings') }}
                    </button>
                @endif
            </x-card>
        </form>

        <!-- Delete Account Section -->
        <x-card class="mt-5">
            <h4>@lang('Delete Account')</h4>
            <p>{{ __('If you no longer want to use your account, you can request to delete it.') }}</p>
            <div class="col-12">
                <a class="btn btn-danger" href="{{ route('dashboard.user.settings.deleteAccount') }}">
                    {{ __('Request Account Deletion') }}
                </a>
            </div>
        </x-card>
    </div>
@endsection

@push('script')
    <script src="{{ custom_theme_url('/assets/js/panel/user.js') }}"></script>
@endpush