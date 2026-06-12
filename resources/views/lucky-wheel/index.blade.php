@extends('layouts.app')
@section('title', 'Vòng Quay May Mắn - SFood')
@section('content')
<div class="bg-earth-800 text-cream-50 py-12">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold mb-2">🎡 Vòng Quay May Mắn</h1>
        <p class="text-earth-200">Quay mỗi ngày để nhận ngàn phần quà hấp dẫn</p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 py-12 text-center" x-data="luckyWheel()">
    <div class="mb-8">
        <p class="text-xl font-bold text-earth-800">Lượt quay hiện tại của bạn: <span class="text-leaf-600 text-3xl ml-2" x-text="spins"></span></p>
        <p class="text-earth-600 mt-2 text-sm">Mua đơn hàng trên 500k để nhận thêm lượt hoặc đăng nhập mỗi 24h để nhận 1 lượt miễn phí.</p>
    </div>

    <!-- The Wheel -->
    <div class="relative w-80 h-80 mx-auto mb-8 drop-shadow-xl">
        <!-- Pointer -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 -mt-4 z-30 w-8 h-12 bg-red-600" style="clip-path: polygon(50% 100%, 0 0, 100% 0);"></div>
        
        <!-- Wheel Graphic -->
        @php
            $colors = ['#f87171', '#fbbf24', '#34d399', '#60a5fa', '#a78bfa', '#f472b6', '#fb923c', '#4ade80'];
            $stops = [];
            foreach ($prizes as $index => $prize) {
                $start = $index * 45;
                $end = ($index + 1) * 45;
                $stops[] = "{$colors[$index % 8]} {$start}deg {$end}deg";
            }
            $conicGradient = "conic-gradient(" . implode(', ', $stops) . ")";
        @endphp
        <div class="w-full h-full rounded-full border-8 border-earth-800 overflow-hidden relative transition-transform" 
             style="background: {{ $conicGradient }}; transition-timing-function: cubic-bezier(0.25, 1, 0.5, 1); transition-duration: 5s;"
             x-ref="wheel">
            
            <!-- Prize Texts -->
            @foreach($prizes as $index => $prize)
                @php
                    // Góc quay cho chữ nằm giữa ô (góc giữa 2 đường phân chia là start + 22.5)
                    // Conic-gradient bắt đầu từ hướng 12 giờ (0deg)
                    $angle = $index * 45 + 22.5; 
                @endphp
                <div class="absolute inset-0 w-full h-full text-center text-white font-bold text-sm tracking-wider flex justify-center pt-6 z-10"
                     style="transform: rotate({{ $angle }}deg); text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">
                    <span class="inline-block w-24 leading-tight break-words" style="transform: rotate(90deg); transform-origin: 50% 30px;">
                        {{ Str::limit($prize['name'], 20) }}
                    </span>
                </div>
            @endforeach

            <!-- Center Dot -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white z-20 border-4 border-earth-800 shadow-md flex items-center justify-center">
                <div class="w-4 h-4 bg-earth-800 rounded-full"></div>
            </div>
        </div>
    </div>

    <button @click="spin()" class="btn-primary text-xl px-12 py-4 rounded-full shadow-lg hover:scale-105 transition transform disabled:opacity-50 disabled:hover:scale-100" :disabled="spinning || spins <= 0">
        <span x-show="!spinning">QUAY NGAY!</span>
        <span x-show="spinning">Đang quay...</span>
    </button>

    <!-- Modal Prize -->
    <div x-show="showPrize" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" x-transition.opacity style="display: none;">
        <div class="bg-white rounded-3xl p-8 max-w-sm w-full text-center relative" @click.away="showPrize = false">
            <div class="text-6xl mb-4" x-text="couponCode ? '🎉' : '😢'"></div>
            <h3 class="text-2xl font-bold text-earth-800 mb-2" x-text="couponCode ? 'Chúc mừng!' : 'Ôi không!'"></h3>
            <p class="text-earth-600 mb-4" x-text="prizeMessage"></p>
            
            <template x-if="couponCode">
                <div class="bg-cream-100 border border-leaf-200 rounded-xl p-4 mb-6 text-left">
                    <p class="text-sm text-earth-600 mb-1 text-center">Mã của bạn:</p>
                    <p class="font-mono font-bold text-3xl text-leaf-700 text-center mb-4" x-text="couponCode"></p>
                    <div class="space-y-1 text-sm text-earth-700 bg-white p-3 rounded-lg border border-cream-200">
                        <p><strong>🎁 Ưu đãi:</strong> <span x-text="prizeName"></span></p>
                        <template x-if="minOrderAmount > 0">
                            <p><strong>💰 Điều kiện:</strong> Đơn từ <span x-text="new Intl.NumberFormat('vi-VN').format(minOrderAmount) + 'đ'"></span></p>
                        </template>
                        <template x-if="conditionText">
                            <p><strong>🎯 Áp dụng cho:</strong> <span x-text="conditionText"></span></p>
                        </template>
                        <p><strong>⏱ Hạn dùng:</strong> <span x-text="expiresAt"></span></p>
                    </div>
                </div>
            </template>
            
            <div class="space-y-2">
                <template x-if="couponCode">
                    <button onclick="window.location.href='{{ route('profile.coupons') }}'" class="btn-primary w-full">Vào ví Coupon</button>
                </template>
                <button @click="showPrize = false" class="btn-secondary w-full">Quay tiếp</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function luckyWheel() {
    return {
        spins: {{ $user->lucky_spins ?? 0 }},
        spinning: false,
        showPrize: false,
        prizeMessage: '',
        couponCode: '',
        prizeName: '',
        minOrderAmount: 0,
        conditionText: '',
        expiresAt: '',
        currentRotation: 0,
        
        async spin() {
            if (this.spins <= 0 || this.spinning) return;
            
            this.spinning = true;

            try {
                // Call API
                const response = await fetch('{{ route('lucky-wheel.spin') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (!data.success) {
                    alert(data.message);
                    this.spinning = false;
                    return;
                }

                this.spins--;

                // Visual spin
                const prizeId = data.prize.id;
                
                // Vì có 8 ô trên giao diện, mỗi ô 45 độ.
                // Trượt ngẫu nhiên góc tùy theo ID để luôn rớt vào 1 ô nhất định ứng với ID đó (để nhất quán nếu quay trúng ID cũ).
                const sectorIndex = prizeId % 8; 
                const prizeAngle = sectorIndex * 45;
                
                // Add 5-10 extra spins
                const extraSpins = Math.floor(Math.random() * 5) + 5;
                const newRotation = this.currentRotation + (360 * extraSpins) + (360 - (this.currentRotation % 360)) + prizeAngle;
                
                this.$refs.wheel.style.transform = `rotate(${newRotation}deg)`;
                this.currentRotation = newRotation;

                // Build condition text
                let condText = 'Tất cả sản phẩm';
                if (data.prize.name.includes('cho')) {
                    condText = data.prize.name.split('cho ')[1];
                }

                // Wait for animation to finish (5 seconds)
                setTimeout(() => {
                    this.spinning = false;
                    this.prizeMessage = data.message;
                    
                    if (data.coupon) {
                        this.couponCode = data.coupon.code;
                        this.prizeName = data.prize.name;
                        this.minOrderAmount = data.prize.min_order_amount;
                        this.conditionText = condText;
                        
                        // Format date
                        const expDate = new Date(data.coupon.expires_at);
                        this.expiresAt = expDate.toLocaleDateString('vi-VN') + ' ' + expDate.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'});
                    } else {
                        this.couponCode = '';
                    }

                    this.showPrize = true;
                }, 5200);

            } catch (error) {
                console.error(error);
                alert('Có lỗi xảy ra!');
                this.spinning = false;
            }
        }
    }
}
</script>
@endpush
