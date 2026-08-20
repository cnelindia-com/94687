@extends('panel.layout.settings', ['layout' => 'wide'])

@section('title', __('Default Backgrounds'))

@section('titlebar_subtitle', __('Manage your default AI backgrounds.'))

@section('settings')
    <div class="space-y-6">
        {{-- Header --}}
        <div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Default Backgrounds') }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Manage your default AI backgrounds') }}</p>
        </div>

        {{-- Tab Buttons --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <div class="flex gap-8">
                <button onclick="showTab('default')" id="tab-default"
                    class="pb-3 px-1 text-base font-semibold border-b-2 border-primary text-primary transition-all">
                    {{ __('Default Backgrounds') }}
                </button>
                <button onclick="showTab('create')" id="tab-create"
                    class="pb-3 px-1 text-base font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-all">
                    {{ __('Create New Background') }}
                </button>
            </div>
        </div>

        {{-- Default Backgrounds Tab --}}
        <div id="tab-content-default">
            <p class="text-xs text-gray-400 dark:text-gray-500 mb-4 text-center">
                💡 {{ __('Drag and drop backgrounds to reorder. Order saves automatically to database.') }}
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="backgrounds-grid">
                @forelse($backgrounds as $background)
                    <div class="background-card group bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm cursor-grab select-none"
                        data-id="{{ $background->id }}" draggable="true">
                        <div class="relative">
                            <button onclick="deleteBackground({{ $background->id }}, this)"
                                class="absolute top-2 right-2 z-10 bg-red-500 hover:bg-red-600 text-white rounded-full p-1.5 shadow-lg transition-all opacity-0 group-hover:opacity-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="aspect-[3/4] overflow-hidden pointer-events-none">
                            <img src="{{ $background->thumbnail ?? $background->image_url }}" alt="{{ $background->name }}"
                                class="w-full h-full object-cover">
                        </div>
                        <div class="p-4 pointer-events-none">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $background->name }}</h4>
                            <p class="text-sm text-gray-500">{{ $background->category }}</p>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-10 text-gray-500">{{ __('No backgrounds found') }}</div>
                @endforelse
            </div>
        </div>

        {{-- Create Tab --}}
        <div id="tab-content-create" class="hidden">
            <form id="create-background-form" method="POST" action="{{ route('dashboard.user.fashion-studio.default-backgrounds.save-custom-background') }}"
                enctype="multipart/form-data">
                @csrf
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 space-y-6">
                        {{-- Background Name --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Background Name') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="background-name" placeholder="{{ __('e.g., Beach Sunset') }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                required>
                        </div>

                        {{-- Category --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Category') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="category" id="category" placeholder="{{ __('e.g., Outdoor, Studio, City') }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                required>
                        </div>

                        {{-- Upload Photos --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Upload Photos') }} <span class="text-red-500">*</span>
                            </label>
                            <div id="drop-zone-create"
                                class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-10 text-center cursor-pointer hover:border-primary hover:bg-primary/5 transition-all">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-gray-700 dark:text-gray-300 font-medium text-lg">{{ __('Drag & drop images here') }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('or click to browse') }}</p>
                                    </div>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ __('PNG, JPG up to 25MB each') }}</p>
                                </div>
                            </div>
                            <input type="file" id="create-upload-input" name="images[]" accept="image/*" class="hidden" required>
                            <div id="create-preview" class="mt-6 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 hidden"></div>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2 text-center">{{ __('💡 Drag and drop images to reorder them') }}</p>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" id="submit-btn"
                            class="w-full py-3.5 bg-primary hover:bg-primary-dark text-white font-semibold rounded-xl transition-all duration-200 flex items-center justify-center gap-2 shadow-sm text-base">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            <span id="submit-btn-text">{{ __('Create New Background') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TOAST --}}
    <div id="toast"
        class="fixed top-5 right-5 z-[9999] hidden items-center gap-3 px-5 py-4 rounded-xl shadow-2xl text-white text-sm font-medium min-w-[270px] max-w-xs">
        <div id="toast-icon" class="shrink-0"></div>
        <span id="toast-message"></span>
    </div>

    <style>
        .aspect-\[3\/4\] {
            aspect-ratio: 3/4;
        }

        .preview-item {
            transition: all 0.2s ease;
            cursor: grab;
        }

        .preview-item:active {
            cursor: grabbing;
        }

        .background-card {
            transition: opacity 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }

        .background-card:active {
            cursor: grabbing;
        }

        .background-card.dragging {
            opacity: 0.35;
            transform: scale(0.96);
        }

        .background-card.drag-over {
            box-shadow: 0 0 0 3px #10b981;
            transform: scale(1.02);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(80px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(80px);
            }
        }

        #toast.show {
            display: flex;
            animation: slideIn 0.35s ease forwards;
        }

        #toast.hide {
            animation: slideOut 0.35s ease forwards;
        }
    </style>

    <script>
        let toastTimer = null;

        function showToast(message, type) {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toast-icon');
            const msgEl = document.getElementById('toast-message');

            clearTimeout(toastTimer);
            msgEl.textContent = message;

            if (type === 'success') {
                toast.style.backgroundColor = '#10b981';
                icon.innerHTML = `<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>`;
            } else {
                toast.style.backgroundColor = '#ef4444';
                icon.innerHTML = `<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>`;
            }

            toast.classList.remove('hidden', 'hide');
            toast.classList.add('show');

            toastTimer = setTimeout(() => {
                toast.classList.remove('show');
                toast.classList.add('hide');
                setTimeout(() => toast.classList.add('hidden'), 350);
            }, 3000);
        }

        const grid = document.getElementById('backgrounds-grid');
        let dragSrc = null;

        function deleteBackground(id, btn) {
            if (!confirm('Are you sure you want to delete this background?')) return;
            const card = btn.closest('.background-card');
            fetch(`{{ route('dashboard.user.fashion-studio.default-backgrounds.delete-background', '') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        card.remove();
                        showToast('Background deleted!', 'success');
                    } else {
                        showToast(data.message || 'Delete failed.', 'error');
                    }
                })
                .catch(() => showToast('Network error.', 'error'));
        }

        function initDragDrop() {
            if (!grid) return;
            grid.querySelectorAll('.background-card').forEach(card => {
                card.addEventListener('dragstart', onDragStart);
                card.addEventListener('dragend', onDragEnd);
                card.addEventListener('dragover', onDragOver);
                card.addEventListener('dragenter', onDragEnter);
                card.addEventListener('dragleave', onDragLeave);
                card.addEventListener('drop', onDrop);
            });
        }

        function onDragStart(e) {
            dragSrc = this;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        }

        function onDragEnd() {
            this.classList.remove('dragging');
            if (grid) {
                grid.querySelectorAll('.background-card').forEach(c => c.classList.remove('drag-over'));
            }
            dragSrc = null;
        }

        function onDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
        }

        function onDragEnter(e) {
            e.preventDefault();
            if (this !== dragSrc) this.classList.add('drag-over');
        }

        function onDragLeave() {
            this.classList.remove('drag-over');
        }

        function onDrop(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            if (!dragSrc || dragSrc === this) return;

            const allCards = [...grid.querySelectorAll('.background-card')];
            const srcIdx = allCards.indexOf(dragSrc);
            const destIdx = allCards.indexOf(this);

            if (srcIdx < destIdx) {
                grid.insertBefore(dragSrc, this.nextSibling);
            } else {
                grid.insertBefore(dragSrc, this);
            }

            saveRankToDatabase();
        }

        function saveRankToDatabase() {
            const ranks = [...grid.querySelectorAll('.background-card')].map((card, index) => ({
                id: parseInt(card.getAttribute('data-id')),
                rank: index + 1
            }));

            fetch('{{ route("dashboard.user.fashion-studio.default-backgrounds.update-rank") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ ranks }),
                })
                .then(res => res.json())
                .then(data => {
                    showToast(data.success ? 'Order saved!' : (data.message || 'Failed to save order.'), data.success ? 'success' : 'error');
                })
                .catch(() => showToast('Network error. Order not saved.', 'error'));
        }

        if (grid) {
            initDragDrop();
        }

        const dropZone = document.getElementById('drop-zone-create');
        const fileInput = document.getElementById('create-upload-input');
        const preview = document.getElementById('create-preview');

        if (dropZone) {
            dropZone.addEventListener('click', () => fileInput.click());
            dropZone.addEventListener('dragover', e => {
                e.preventDefault();
                dropZone.classList.add('border-primary', 'bg-primary/5');
            });
            dropZone.addEventListener('dragleave', e => {
                e.preventDefault();
                dropZone.classList.remove('border-primary', 'bg-primary/5');
            });
            dropZone.addEventListener('drop', e => {
                e.preventDefault();
                dropZone.classList.remove('border-primary', 'bg-primary/5');
                if (e.dataTransfer.files.length > 0) {
                    fileInput.files = e.dataTransfer.files;
                    showPreview(e.dataTransfer.files);
                }
            });
        }

        if (fileInput) {
            fileInput.addEventListener('change', function() {
                showPreview(this.files);
            });
        }

        function showPreview(files) {
            if (!preview) return;
            preview.innerHTML = '';
            if (!files || files.length === 0) {
                preview.classList.add('hidden');
                return;
            }
            preview.classList.remove('hidden');

            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    const div = document.createElement('div');
                    div.className = 'preview-item relative rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm';
                    div.innerHTML = `<img src="${e.target.result}" class="w-full h-40 object-cover">`;
                    preview.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }

        const form = document.getElementById('create-background-form');
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const submitBtn = document.getElementById('submit-btn');
                const btnText = document.getElementById('submit-btn-text');

                if (!fileInput.files || fileInput.files.length === 0) {
                    showToast('Please upload at least one photo.', 'error');
                    return;
                }

                submitBtn.disabled = true;
                btnText.textContent = 'Uploading...';

                try {
                    const response = await fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: new FormData(this),
                    });

                    const data = await response.json().catch(() => null);

                    if (response.ok) {
                        showToast(data?.message || 'Background created successfully!', 'success');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        let errMsg = 'Something went wrong. Please try again.';
                        if (data?.message) errMsg = data.message;
                        else if (data?.errors) errMsg = Object.values(data.errors).flat().join(' ');
                        showToast(errMsg, 'error');
                        submitBtn.disabled = false;
                        btnText.textContent = 'Create New Background';
                    }
                } catch (err) {
                    showToast('Network error. Please try again.', 'error');
                    console.error(err);
                    submitBtn.disabled = false;
                    btnText.textContent = 'Create New Background';
                }
            });
        }

        function showTab(tab) {
            const defaultContent = document.getElementById('tab-content-default');
            const createContent = document.getElementById('tab-content-create');
            const tabDefaultBtn = document.getElementById('tab-default');
            const tabCreateBtn = document.getElementById('tab-create');

            if (tab === 'default') {
                defaultContent.classList.remove('hidden');
                createContent.classList.add('hidden');
                tabDefaultBtn.classList.add('border-primary', 'text-primary');
                tabDefaultBtn.classList.remove('text-gray-500');
                tabCreateBtn.classList.remove('border-primary', 'text-primary');
                tabCreateBtn.classList.add('text-gray-500');
            } else {
                createContent.classList.remove('hidden');
                defaultContent.classList.add('hidden');
                tabCreateBtn.classList.add('border-primary', 'text-primary');
                tabCreateBtn.classList.remove('text-gray-500');
                tabDefaultBtn.classList.remove('border-primary', 'text-primary');
                tabDefaultBtn.classList.add('text-gray-500');
            }
        }
    </script>
@endsection