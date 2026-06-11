@props([
    'action' => route('products.index'),
    'value' => '',
    'placeholder' => 'Tìm sản phẩm, món ăn...',
    'showVoice' => true,
    'size' => 'md',
    'standalone' => true,
    'name' => 'search',
])

@php
    $sizeClass = $size === 'lg' ? 'py-4 text-base' : 'py-3 text-sm';
@endphp

<div class="search-autocomplete relative" data-suggest-url="{{ route('search.suggest') }}">
    @if ($standalone)
    <form method="GET" action="{{ $action }}" class="relative">
    @endif
        <div class="relative flex items-center">
            <svg class="absolute left-4 w-5 h-5 text-earth-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="{{ $name }}" value="{{ $value }}" autocomplete="off"
                placeholder="{{ $placeholder }}"
                class="search-input input-field pl-12 pr-24 {{ $sizeClass }}"
                data-autocomplete>
            <div class="absolute right-2 flex items-center gap-1">
                @if ($showVoice)
                    <button type="button" class="voice-search-btn p-1.5 rounded-lg text-leaf-600 hover:bg-leaf-50 transition" title="Tìm bằng giọng nói">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm5-3c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
                    </button>
                    <label class="cursor-pointer p-1.5 rounded-lg text-leaf-600 hover:bg-leaf-50 transition" title="Tìm bằng hình ảnh">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <input type="file" accept="image/*" class="hidden" onchange="submitGlobalImageSearch(this)">
                    </label>
                @endif
                @if ($standalone)
                <button type="submit" class="bg-leaf-600 hover:bg-leaf-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    Tìm
                </button>
                @endif
            </div>
        </div>
    @if ($standalone)
    </form>
    @endif
    <div class="suggest-dropdown" data-suggest-dropdown></div>
</div>

<script>
    function submitGlobalImageSearch(input) {
        if (!input.files || !input.files[0]) return;
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("products.ai-search") }}';
        form.enctype = 'multipart/form-data';
        form.style.display = 'none';
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrf);

        const newFileInput = input.cloneNode(true);
        newFileInput.name = 'ai_image';
        
        // Use DataTransfer to copy the file to the cloned input
        const dt = new DataTransfer();
        dt.items.add(input.files[0]);
        newFileInput.files = dt.files;
        
        form.appendChild(newFileInput);
        document.body.appendChild(form);
        
        // Show loading indicator
        const btn = input.parentElement;
        btn.innerHTML = '<svg class="animate-spin w-5 h-5 text-leaf-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        
        form.submit();
    }
</script>
