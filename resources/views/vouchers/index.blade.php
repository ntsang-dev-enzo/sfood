@extends('layouts.app')
@section('title', 'Thu thập Voucher - SFood')
@section('content')
    <div class="bg-gradient-to-r from-earth-800 to-earth-700 text-cream-50 py-12">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-3xl font-bold">Trạm Khuyến Mãi</h1>
            <p class="text-cream-300 mt-2">Săn mã giảm giá, mua sắm thả ga</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-12">
        @if ($coupons->count())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($coupons as $coupon)
                    @php
                        $isCollected = in_array($coupon->id, $collectedIds);
                        $isUsedUp = $coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit;
                    @endphp
                    <div class="bg-white rounded-2xl shadow-sm border border-cream-200 overflow-hidden flex flex-col relative {{ $isUsedUp ? 'opacity-70 grayscale' : '' }}">
                        <div class="p-6 flex-1 flex flex-col items-center text-center border-b border-dashed border-cream-300 relative bg-cream-50">
                            <!-- Notch cuts for ticket effect -->
                            <div class="absolute -bottom-3 -left-3 w-6 h-6 bg-cream-100 dark:bg-[#121212] rounded-full border border-cream-200"></div>
                            <div class="absolute -bottom-3 -right-3 w-6 h-6 bg-cream-100 dark:bg-[#121212] rounded-full border border-cream-200"></div>
                            
                            <span class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-leaf-100 text-leaf-600 mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                            </span>
                            
                            <h3 class="font-bold text-xl text-earth-800 mb-2">
                                @if ($coupon->type === 'percent') Giảm {{ $coupon->value }}%
                                @elseif ($coupon->type === 'fixed') Giảm {{ number_format($coupon->value) }}đ
                                @else Miễn phí vận chuyển
                                @endif
                            </h3>
                            
                            @if ($coupon->min_order_amount > 0)
                                <p class="text-sm text-earth-600">Cho đơn từ {{ number_format($coupon->min_order_amount) }}đ</p>
                            @endif
                            <p class="font-mono font-bold text-lg text-leaf-600 mt-3 bg-white px-4 py-1.5 rounded-lg border border-leaf-200 shadow-sm">{{ $coupon->code }}</p>
                        </div>
                        <div class="p-6 bg-white flex flex-col justify-between">
                            <div class="text-xs text-earth-500 mb-4 space-y-1">
                                @if ($coupon->expires_at)
                                    <p>HSD: {{ $coupon->expires_at->format('d/m/Y H:i') }}</p>
                                @endif
                                @if ($coupon->usage_limit)
                                    <p>Đã dùng: {{ $coupon->used_count }}/{{ $coupon->usage_limit }}</p>
                                @endif
                            </div>
                            
                            @if($isUsedUp)
                                <button class="btn-secondary w-full opacity-50 cursor-not-allowed" disabled>Đã hết lượt</button>
                            @elseif($isCollected)
                                <button class="btn-outline w-full bg-cream-100 text-earth-600 border-cream-200 pointer-events-none" disabled>✓ Đã thu thập</button>
                            @else
                                <button onclick="collectVoucher({{ $coupon->id }}, this)" class="btn-primary w-full shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all">Thu thập ngay</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-cream-300">
                <div class="text-6xl mb-4">🎫</div>
                <h2 class="text-2xl font-bold text-earth-800 mb-2">Chưa có mã giảm giá nào</h2>
                <p class="text-earth-500 mb-6">Hãy quay lại sau để nhận những ưu đãi hấp dẫn nhé!</p>
                <a href="{{ route('products.index') }}" class="btn-primary inline-flex">Tiếp tục mua sắm</a>
            </div>
        @endif
    </div>

@push('scripts')
<script>
    async function collectVoucher(id, btn) {
        @if(!Auth::check())
            window.location.href = '{{ route('login') }}';
            return;
        @endif

        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="animate-pulse">Đang lưu...</span>';
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
                btn.className = "btn-outline w-full bg-cream-100 text-earth-600 border-cream-200 pointer-events-none";
                btn.innerHTML = "✓ Đã thu thập";
                // Show a mini toast
                alert(data.message);
            } else {
                btn.innerHTML = originalText;
                btn.disabled = false;
                alert(data.message || 'Có lỗi xảy ra');
            }
        } catch(e) {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('Lỗi kết nối');
        }
    }
</script>
@endpush
@endsection
