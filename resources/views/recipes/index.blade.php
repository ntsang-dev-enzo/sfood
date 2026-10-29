@extends('layouts.app')
@section('title', 'Công Thức Nấu Ăn & Thực Đơn Món Ngon - SFood')

@section('content')
<div class="bg-success text-white py-4 shadow-sm" style="background: linear-gradient(135deg, #2b2d42 0%, #3d2c1e 100%);">
    <div class="container">
        <h1 class="h3 fw-bold mb-1"><i class="bi bi-journal-richtext me-2"></i>Công Thức Món Ngon Mỗi Ngày</h1>
        <p class="text-white-50 mb-0 small">Học nấu ăn ngon và đặt mua nhanh nguyên liệu tươi sạch chỉ với 1 chạm</p>
    </div>
</div>

<div class="container py-4">
    {{-- Search Bar --}}
    <div class="row mb-4">
        <div class="col-lg-6">
            @include('partials.search-box', [
                'action' => route('recipes.index'),
                'value' => $search,
                'placeholder' => 'Tìm kiếm công thức, món ăn gia đình...',
                'showVoice' => true,
            ])
        </div>
    </div>

    {{-- Bộ lọc công thức (Bootstrap 5 Form) --}}
    <div class="card border-0 shadow-sm p-3 mb-4 rounded-3 bg-white">
        <form method="GET" action="{{ route('recipes.index') }}">
            @if($search)
                <input type="hidden" name="search" value="{{ $search }}">
            @endif
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label small fw-bold text-muted mb-1">Danh mục món</label>
                    <select name="category" class="form-select form-select-sm shadow-none">
                        <option value="">-- Tất cả danh mục --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->slug }}" {{ $categorySlug === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4 col-lg-4">
                    <label class="form-label small fw-bold text-muted mb-1">Thời gian nấu (phút)</label>
                    <div class="input-group input-group-sm">
                        <input type="number" name="min_time" value="{{ $minTime }}" placeholder="Từ" class="form-control" min="0">
                        <span class="input-group-text bg-light border-start-0 border-end-0">-</span>
                        <input type="number" name="max_time" value="{{ $maxTime }}" placeholder="Đến" class="form-control" min="0">
                    </div>
                </div>

                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label small fw-bold text-muted mb-1">Sắp xếp</label>
                    <select name="sort" class="form-select form-select-sm shadow-none">
                        <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Mới nhất</option>
                        <option value="shortest" {{ $sort === 'shortest' ? 'selected' : '' }}>Nấu nhanh nhất</option>
                        <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>Phổ biến nhất</option>
                    </select>
                </div>

                <div class="col-12 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-success fw-bold flex-grow-1">Lọc món</button>
                    <a href="{{ route('recipes.index') }}" class="btn btn-sm btn-outline-secondary">Xóa</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Main Content: Danh sách công thức & Sidebar Voucher --}}
    <div class="row g-4">
        {{-- Danh sách bài viết công thức (Col 8/9) --}}
        <div class="col-lg-8 col-xl-9">
            @if ($recipes->count() > 0)
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 g-md-4">
                    @foreach ($recipes as $recipe)
                        <div class="col">
                            @include('partials.recipe-card', ['recipe' => $recipe])
                        </div>
                    @endforeach
                </div>

                @if($recipes->hasPages())
                    <div class="d-flex justify-content-center mt-5">
                        {{ $recipes->links() }}
                    </div>
                @endif
            @else
                <div class="card border-0 shadow-sm rounded-4 bg-white text-center py-5 px-3 my-2 w-100">
                    <div class="card-body py-5">
                        <div class="mb-4">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light border p-4 shadow-sm" style="width: 96px; height: 96px;">
                                <i class="bi bi-journal-x text-secondary" style="font-size: 2.75rem;"></i>
                            </span>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">Chưa có công thức phù hợp</h4>
                        <p class="text-muted mx-auto mb-4" style="max-width: 520px; font-size: 0.95rem;">
                            Hãy thử tìm kiếm với từ khóa khác hoặc xóa bớt bộ lọc thời gian để khám phá thêm nhiều món ngon.
                        </p>
                        <a href="{{ route('recipes.index') }}" class="btn btn-success fw-bold px-4 py-2.5 rounded-pill shadow-sm">
                            Xem tất cả công thức
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar Trạm Khuyến Mãi (Col 4/3) --}}
        <div class="col-lg-4 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #fff3cd 0%, #ffecb5 100%);">
                <div class="card-header bg-warning border-0 py-3 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-ticket-perforated-fill fs-5"></i>
                    <h6 class="fw-bold mb-0">Trạm Khuyến Mãi</h6>
                </div>
                <div class="card-body p-3">
                    <p class="small text-muted mb-3">Lưu ngay mã giảm giá để mua nguyên liệu nấu ăn tiết kiệm hơn:</p>
                    <div class="d-flex flex-column gap-3">
                        @forelse ($coupons as $coupon)
                            @php
                                $isCollected = in_array($coupon->id, $collectedIds);
                                $isUsedUp = $coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit;
                            @endphp
                            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 {{ $isUsedUp ? 'opacity-50' : '' }}">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-danger">
                                        @if ($coupon->type === 'percent') Giảm {{ $coupon->value }}%
                                        @elseif ($coupon->type === 'fixed') Giảm {{ number_format($coupon->value) }}₫
                                        @else Freeship
                                        @endif
                                    </span>
                                    <code class="fw-bold text-success">{{ $coupon->code }}</code>
                                </div>
                                @if ($coupon->min_order_amount > 0)
                                    <small class="text-muted">Đơn từ {{ number_format($coupon->min_order_amount) }}₫</small>
                                @endif
                                <div class="mt-2">
                                    @if($isUsedUp)
                                        <button class="btn btn-sm btn-secondary w-100 disabled py-1" style="font-size: 0.75rem;">Hết lượt</button>
                                    @elseif($isCollected)
                                        <button class="btn btn-sm btn-outline-success w-100 disabled py-1" style="font-size: 0.75rem;"><i class="bi bi-check-circle me-1"></i>Đã lưu</button>
                                    @else
                                        <button onclick="collectVoucher({{ $coupon->id }}, this)" class="btn btn-sm btn-success w-100 py-1" style="font-size: 0.75rem;">Thu thập ngay</button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="small text-muted mb-0">Hiện chưa có mã giảm giá nào.</p>
                        @endforelse
                    </div>
                    <a href="{{ route('vouchers.index') }}" class="btn btn-outline-dark btn-sm w-100 mt-3 fw-semibold">
                        Xem tất cả mã giảm giá <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    async function collectVoucher(id, btn) {
        @if(!Auth::check())
            window.location.href = '{{ route('login') }}';
            return;
        @endif

        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Đang lưu...';
        btn.disabled = true;

        try {
            const res = await fetch(`/vouchers/${id}/collect`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            
            if (res.ok) {
                btn.className = "btn btn-sm btn-outline-success w-100 disabled py-1";
                btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Đã lưu';
                alert(data.message || 'Lưu mã giảm giá thành công!');
            } else {
                btn.innerHTML = originalText;
                btn.disabled = false;
                alert(data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
            }
        } catch (e) {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
</script>
@endsection
