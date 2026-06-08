@extends('layouts.app')
@section('title', 'Chính sách - SFood')
@section('content')
<div class="bg-earth-800 text-cream-50 py-16">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold mb-4">Chính sách</h1>
        <p class="text-cream-300">Cam kết minh bạch với khách hàng</p>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 py-14 prose prose-stone">
    <div class="card p-8 mb-6">
        <h2 class="text-xl font-bold text-earth-800 mb-3">Chính sách giao hàng</h2>
        <ul class="text-earth-600 text-sm space-y-2 list-disc pl-5">
            <li>Giao trong 2 giờ (nội thành), 24h (ngoại thành)</li>
            <li>Miễn phí ship đơn từ 300.000đ</li>
            <li>Phí ship cố định 30.000đ cho đơn dưới 300.000đ</li>
        </ul>
    </div>
    <div class="card p-8 mb-6">
        <h2 class="text-xl font-bold text-earth-800 mb-3">Chính sách đổi trả</h2>
        <ul class="text-earth-600 text-sm space-y-2 list-disc pl-5">
            <li>Đổi trả trong 24h nếu sản phẩm không đúng chất lượng</li>
            <li>Hoàn tiền 100% nếu giao sai hoặc hư hỏng</li>
            <li>Liên hệ hotline 1900 1234 để được hỗ trợ</li>
        </ul>
    </div>
    <div class="card p-8">
        <h2 class="text-xl font-bold text-earth-800 mb-3">Bảo mật thông tin</h2>
        <p class="text-earth-600 text-sm leading-relaxed">
            SFood cam kết bảo mật thông tin cá nhân của khách hàng. Dữ liệu chỉ được sử dụng để xử lý đơn hàng và cải thiện dịch vụ. Chúng tôi không chia sẻ thông tin cho bên thứ ba mà không có sự đồng ý của bạn.
        </p>
    </div>
</div>
@endsection
