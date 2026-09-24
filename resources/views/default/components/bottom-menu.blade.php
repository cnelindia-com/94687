@php use App\Models\OpenAIGenerator; @endphp


<div
    class="invisible fixed bottom-16 right-0 z-[99] max-h-[calc(85vh-4rem)] w-full origin-bottom translate-y-2 scale-95 overflow-y-auto overscroll-contain rounded-t-2xl bg-[#fff] opacity-0 shadow-[-5px_-10px_30px_rgba(0,0,0,0.07)] transition-all dark:bg-zinc-800 lg:!hidden [&.lqd-is-active]:visible [&.lqd-is-active]:translate-y-0 [&.lqd-is-active]:scale-100 [&.lqd-is-active]:opacity-100"
    x-init
    :class="{ 'lqd-is-active': !$store.mobileNav.templatesCollapse }"
>
    <ul class="relative h-full text-2xs font-medium text-heading-foreground">
        @foreach ($openAiList ?? [] as $aiWriter)
            <li class="relative">
                <a
                    class="flex items-center gap-2 border-b border-l-0 border-r-0 border-t-0 border-solid border-[--tblr-border-color] p-3 py-2 text-inherit"
                    @if (($aiWriter->type == 'text' || $aiWriter->type == 'code') && $aiWriter->slug != 'ai_webchat') href="{{ route('dashboard.user.openai.generator.workbook', $aiWriter->slug) }}"
					@elseif ($aiWriter->slug == 'ai_webchat' && \Illuminate\Support\Facades\Route::has('dashboard.user.openai.webchat.workbook'))
           	 		href="{{ route('dashboard.user.openai.webchat.workbook') }}"
					@else href="{{ route('dashboard.user.openai.generator', $aiWriter->slug) }}" @endif
                >
                    <span
                        class="relative inline-flex size-9 items-center justify-center rounded-full transition-all duration-300 [&_svg]:size-5"
                        style="background: {{ $aiWriter->color }}"
                    >
                        <span class="inline-block transition-all duration-300">
                            {!! html_entity_decode(stripslashes($aiWriter->image)) !!}
                        </span>
                    </span>
                    {{ $aiWriter->title }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
