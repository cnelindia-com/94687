@extends('panel.layout.app')
@section('title', 'Support Request #' . $ticket->ticket_id)
@section('titlebar_actions', '')

@section('content')
    <div class="py-10">
        <x-card size="none">
            <div class="flex h-[75vh] overflow-hidden max-md:h-auto max-md:flex-col">
                <div class="w-full">
                    <div class="conversation-area flex grow flex-col justify-between max-md:max-h-[100vh] lg:h-full">
                        <div class="overflow-hidden">
                            <div class="chats-container h-full overflow-auto p-8 max-md:max-h-[60vh] max-md:p-4">
                                @foreach ($ticket->messages as $message)
                                    <div @class([
                                        'mb-2 flex flex-wrap lg:max-w-[50%]',
                                        'lg:ms-auto text-end justify-end' => $message->sender == 'user',
                                        'lg:me-auto' => $message->sender != 'user',
                                    ])>
                                        <strong class="mb-1 block w-full text-heading-foreground">
                                            @if ($message->sender == 'user')
                                                {{ $ticket->user?->fullName() }}
                                            @else
                                                {{ $supportSettings->name ?? __('Administrator') }}
                                            @endif
                                        </strong>
                                        <div @class([
                                            'mb-2 rounded-3xl',
                                            'bg-secondary text-secondary-foreground' => $message->sender == 'user',
                                            'bg-clay text-heading-foreground' => $message->sender != 'user',
                                        ])>
                                            <p class="px-6 py-3">
                                                {{ $message->message }}
                                            </p>
                                            @if($message->attachment)
                                                <div class="px-6 pb-3">
                                                    <a href="{{ asset($message->attachment) }}" target="_blank" class="inline-block">
                                                        <img src="{{ asset($message->attachment) }}" alt="Attachment" class="max-w-[200px] max-h-[200px] rounded-lg object-cover cursor-pointer hover:opacity-80">
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                        <p class="w-full text-3xs font-normal opacity-50">
                                            {{ $message->created_at }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <form
                            class="flex items-end gap-3 px-3 pb-3 pt-0"
                            id="support_form"
                            onsubmit="return sendMessage('{{ $ticket->ticket_id }}');"
                            enctype="multipart/form-data"
                        >
                            <x-forms.input
                                class="h-[52px] rounded-3xl pt-3.5"
                                id="message"
                                size="none"
                                container-class="w-full"
                                name="message"
                                type="textarea"
                                rows="1"
                                placeholder="{{ __('Your Message') }}"
                            />
                            <input
                                type="file"
                                id="attachment"
                                name="attachment"
                                accept="image/*"
                                class="hidden"
                                onchange="showFileName(this)"
                            />
                            <button
                                type="button"
                                onclick="document.getElementById('attachment').click()"
                                class="h-[52px] w-[52px] shrink-0 rounded-full inline-flex items-center justify-center bg-primary text-primary-foreground hover:bg-primary/90 transition"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                                </svg>
                            </button>
                            <span id="file_name" class="text-xs text-gray-500 max-w-[100px] truncate"></span>
                            <x-button
                                class="h-[52px] w-[52px] shrink-0 rounded-full"
                                id="send_message_button"
                                size="none"
                                type="submit"
                            >
                                <svg
                                    class="rtl:-scale-x-100"
                                    width="16"
                                    height="14"
                                    viewBox="0 0 16 14"
                                    fill="currentColor"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path d="M0.125 14V8.76172L11.375 7.25L0.125 5.73828V0.5L15.875 7.25L0.125 14Z" />
                                </svg>
                            </x-button>
                        </form>
                    </div>
                </div>

                <div class="flex w-1/4 shrink-0 grow-0 flex-col border-s font-medium text-heading-foreground max-md:w-full">
                    <p class="m-0 flex flex-wrap items-center gap-2 border-b px-5 py-4">
                        <x-tabler-ticket class="size-5" />
                        #{{ $ticket->ticket_id }}
                    </p>
                    <p class="m-0 flex flex-wrap items-center gap-2 border-b px-5 py-4">
                        <x-tabler-pencil-minus class="size-5" />
                        {{ $ticket->subject }}
                    </p>
                    <p class="m-0 flex flex-wrap items-center gap-2 border-b px-5 py-4">
                        <x-tabler-layout-2 class="size-5" />
                        {{ __($ticket->category) }}
                    </p>
                    <p class="m-0 flex flex-wrap items-center gap-2 border-b px-5 py-4">
                        <x-tabler-chart-bubble class="size-5" />
                        {{ __($ticket->status) }}
                    </p>
                </div>
            </div>
        </x-card>
    </div>
@endsection

@push('script')
    <script src="{{ custom_theme_url('/assets/js/panel/support.js') }}?v=2"></script>
    <script>
        function showFileName(input) {
            if (input.files && input.files[0]) {
                document.getElementById('file_name').textContent = input.files[0].name;
            } else {
                document.getElementById('file_name').textContent = '';
            }
        }
    </script>
@endpush
