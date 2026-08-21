<div
    class="relative rounded-3xl p-5 backdrop-blur-[50px] before:pointer-events-none before:absolute before:-inset-px before:-z-1 before:rounded-[inherit] before:border before:border-white before:opacity-30 before:[mask-image:linear-gradient(to_bottom_left,black,transparent_30%)] hover:z-2 md:px-10 md:py-10 lg:px-12"
    style="background: radial-gradient(circle at center -40%, hsl(0 0% 100% / 5%), transparent)"
>
    @if ($plan->is_featured)
        <x-outline-glow class="lqd-outline-glow-custom rounded-[27px] [--outline-glow-w:3px]" />
    @endif

    <p class="mx-auto mb-4 inline-flex rounded-full border border-white/10 px-5 py-2 text-xs">
        {{ __($plan->name) }}
    </p>
    <p class="mb-0 text-[46px] font-semibold leading-none -tracking-wider">
        @if (currencyShouldDisplayOnRight(currency()->symbol))
            {{ formatPrice($plan->price, 2) }}<sub class="top-[-0.1em] text-[0.57em] leading-none">{{ currency()->symbol }}</sub>
        @else
            <sub class="top-[-0.1em] text-[0.57em] leading-none">{{ currency()->symbol }}</sub>{{ formatPrice($plan->price, 2) }}
        @endif
    </p>
    <p class="text-[12px] opacity-50">
        {{ __($period) }}
    </p>

    <div class="text-base font-medium decoration-dotted [&_.lqd-price-table-info]:text-xs [&_.lqd-price-table-info]:text-black">
        <x-plan-details-card
            style="style-3"
            :plan="$plan"
            :period="$period"
        />
    </div>

    <a
        class="group relative mt-6 flex w-full items-center justify-center gap-4 rounded-xl bg-[#1A1A1A] p-5 text-center transition-all hover:bg-white hover:text-black hover:shadow-xl hover:shadow-white/20"
        href="{{ route('register', ['plan' => $plan->id]) }}"
    >
        {{ __('Get Started') }}
        <svg
            width="17"
            height="13"
            viewBox="0 0 17 13"
            fill="currentColor"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M15.6832 5.38486C13.4942 5.38486 11.499 3.39061 11.499 1.20063V0.302734H9.70323V1.20063C9.70323 2.79351 10.4018 4.28761 11.4981 5.38486H0.418945V7.18066H11.4981C10.4018 8.27789 9.70323 9.77199 9.70323 11.3649V12.2628H11.499V11.3649C11.499 9.17489 13.4942 7.18066 15.6832 7.18066H16.5811V5.38486H15.6832Z"
            />
        </svg>
    </a>
</div>
