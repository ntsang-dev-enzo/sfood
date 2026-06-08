@extends('layouts.app')
@section('title', 'Hỏi đáp - SFood')
@section('content')
<div class="bg-earth-800 text-cream-50 py-16">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold mb-4">Hỏi đáp</h1>
        <p class="text-cream-300">Giải đáp thắc mắc thường gặp</p>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 py-14 space-y-4">
    @foreach ([
        ['Làm sao để được miễn phí giao hàng?', 'Đơn hàng từ 300.000đ được miễn phí giao hàng nội thành. Bạn cũng có thể dùng mã FREESHIP cho đơn từ 50.000đ.'],
        ['SFood giao hàng trong bao lâu?', 'Chúng tôi giao trong vòng 2 giờ tại TP.HCM và Hà Nội (nội thành). Đơn ngoại thành giao trong 24h.'],
        ['Tìm kiếm bằng hình ảnh hoạt động thế nào?', 'Tải ảnh thực phẩm lên trang Sản phẩm, AI CLIP sẽ nhận diện và gợi ý sản phẩm tương tự trong cửa hàng.'],
        ['Mua nhanh nguyên liệu là gì?', 'Ở trang công thức nấu ăn, nhấn "Mua nhanh" để thêm toàn bộ nguyên liệu của món ăn vào giỏ hàng chỉ với 1 nút.'],
        ['Thanh toán những hình thức nào?', 'Hỗ trợ COD (thanh toán khi nhận), VNPay và MoMo. Bạn chọn phương thức khi đặt hàng.'],
        ['Giỏ hàng khách có được lưu không?', 'Có. Khi đăng nhập, giỏ hàng khách sẽ tự động gộp vào tài khoản của bạn.'],
    ] as $faq)
        <details class="card group">
            <summary class="p-5 cursor-pointer font-semibold text-earth-800 flex justify-between items-center list-none">
                {{ $faq[0] }}
                <span class="text-leaf-600 group-open:rotate-180 transition">▼</span>
            </summary>
            <div class="px-5 pb-5 text-earth-600 text-sm leading-relaxed border-t border-cream-200 pt-4">
                {{ $faq[1] }}
            </div>
        </details>
    @endforeach
</div>
@endsection
