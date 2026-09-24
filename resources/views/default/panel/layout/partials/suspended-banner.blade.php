@if (!empty($isSuspendedTeamMember))
    <div class="border-b border-amber-500/30 bg-amber-500/10 px-4 py-3 text-amber-950 dark:text-amber-100">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3">
            <p class="m-0 text-sm font-medium">
                {{ __('Your account has been suspended. You can only access Support and Settings.') }}
            </p>
            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ route('dashboard.support.list') }}"
                    class="inline-flex items-center rounded-lg bg-background px-3 py-2 text-xs font-medium text-foreground shadow-sm hover:bg-foreground/5"
                    onclick="event.preventDefault(); window.location.replace(this.href);"
                >
                    {{ __('Support') }}
                </a>
                <a
                    href="{{ route('dashboard.user.fashion-studio.user_settings.index') }}"
                    class="inline-flex items-center rounded-lg bg-background px-3 py-2 text-xs font-medium text-foreground shadow-sm hover:bg-foreground/5"
                    onclick="event.preventDefault(); window.location.replace(this.href);"
                >
                    {{ __('Settings') }}
                </a>
                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    class="inline"
                >
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-lg bg-red-600 px-3 py-2 text-xs font-medium text-white hover:bg-red-700"
                    >
                        {{ __('Logout') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endif
