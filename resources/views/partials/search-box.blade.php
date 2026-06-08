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
                    <button type="button" class="voice-search-btn p-2 rounded-lg text-leaf-600 hover:bg-leaf-50 transition" title="Tìm bằng giọng nói">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm5-3c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
                    </button>
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
