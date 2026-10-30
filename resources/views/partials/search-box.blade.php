@props([
    'action' => route('products.index'),
    'value' => '',
    'placeholder' => 'Tìm kiếm rau củ, thịt, hải sản, món ngon...',
    'showVoice' => true,
    'size' => 'md',
    'standalone' => true,
    'name' => 'search',
])

<div class="search-autocomplete position-relative w-100" data-suggest-url="{{ route('search.suggest') }}">
    @if ($standalone)
    <form method="GET" action="{{ $action }}" class="position-relative m-0">
    @endif
        <div class="input-group {{ $size === 'lg' ? 'input-group-lg' : '' }} border rounded-pill overflow-hidden bg-white shadow-none" style="border: 1.5px solid #198754 !important;">
            <span class="input-group-text bg-white border-0 text-muted ps-3 pe-1">
                <i class="bi bi-search text-success"></i>
            </span>
            <input type="text" name="{{ $name }}" value="{{ $value }}" autocomplete="off"
                placeholder="{{ $placeholder }}"
                class="form-control border-0 bg-white shadow-none px-2"
                data-autocomplete>
            @if ($showVoice)
                <button type="button" class="btn border-0 bg-transparent text-secondary voice-search-btn px-2 shadow-none" title="Tìm bằng giọng nói" style="box-shadow: none !important; border: none !important; outline: none !important; background: transparent !important;">
                    <i class="bi bi-mic"></i>
                </button>
            @endif
            @if ($standalone)
                <button type="submit" class="btn btn-success px-4 fw-semibold border-0 rounded-pill my-1 me-1">
                    Tìm kiếm
                </button>
            @endif
        </div>
    @if ($standalone)
    </form>
    @endif
    <div class="suggest-dropdown shadow-sm rounded-3 border bg-white" data-suggest-dropdown></div>
    <div class="product-preview-box shadow-lg rounded-4 border bg-white" data-product-preview></div>
    <div class="product-preview-backdrop d-none" data-preview-backdrop></div>
</div>
