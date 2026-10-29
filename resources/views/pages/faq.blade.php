@extends('layouts.app')
@section('title', 'Hỏi đáp - SFood')

@section('content')
<div class="bg-body-tertiary py-4 min-vh-100">
    <div class="container py-2" style="max-width: 860px;">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-white p-3 rounded-3 shadow-sm border mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-success"><i class="bi bi-house-door me-1"></i>{{ __('app.home_title') }}</a></li>
                <li class="breadcrumb-item active fw-semibold" aria-current="page">Hỏi đáp thường gặp</li>
            </ol>
        </nav>

        <div class="text-center mb-5">
            <span class="badge bg-success-subtle text-success px-3 py-1.5 rounded-pill fw-bold mb-2">Trợ giúp khách hàng</span>
            <h2 class="fw-bold text-dark">Câu Hỏi Thường Gặp (FAQ)</h2>
            <p class="text-muted small">Giải đáp những thắc mắc phổ biến về sản phẩm, dịch vụ giao hàng và tính năng hệ thống</p>
        </div>

        {{-- Bootstrap 5 Accordion (Collapse Component) --}}
        <div class="accordion accordion-flush card border-0 shadow-sm rounded-4 overflow-hidden bg-white p-2 p-md-3" id="faqAccordion">
            @php
                $faqs = [
                    [
                        'q' => 'Làm sao để được miễn phí giao hàng?',
                        'a' => 'Đơn hàng từ 1.000.000đ hoặc đơn giao nội thành Cần Thơ (Quận Ninh Kiều, Cái Răng) sẽ được miễn phí giao hàng hoàn toàn. Ngoài ra, bạn có thể áp dụng các mã voucher freeship từ trang Khuyến Mãi.'
                    ],
                    [
                        'q' => 'SFood giao hàng trong bao lâu?',
                        'a' => 'SFood cam kết giao nông sản tươi trong vòng 2 giờ đối với các đơn nội thành Cần Thơ, TP.HCM và Hà Nội. Các khu vực lân cận được giao trong ngày để đảm bảo độ tươi mới tuyệt đối.'
                    ],
                    [
                        'q' => 'Chức năng thực đơn XML & DTD hoạt động như thế nào?',
                        'a' => 'Website sử dụng tệp breakfast-menu.xml được xác thực bằng breakfast-menu.dtd với quy tắc thuộc tính định danh duy nhất (ID) và cấu trúc cây hợp lệ. Dữ liệu được nạp bằng đối tượng XMLHttpRequest và duyệt cây phần tử bằng firstChild, nextSibling trực tiếp trên trình duyệt mà không cần tải lại trang.'
                    ],
                    [
                        'q' => 'Mua nhanh nguyên liệu nấu ăn là gì?',
                        'a' => 'Tại trang công thức món ngon, bạn chỉ cần bấm nút "Mua nhanh tất cả nguyên liệu", hệ thống sẽ tự động tổng hợp đầy đủ các nguyên liệu tươi sạch cần dùng vào giỏ hàng của bạn.'
                    ],
                    [
                        'q' => 'Hệ thống hỗ trợ những hình thức thanh toán nào?',
                        'a' => 'SFood hỗ trợ hai hình thức thanh toán chính: COD (thanh toán tiền mặt khi nhận hàng) và thanh toán trực tuyến qua cổng PayOS (hỗ trợ quét mã QR ngân hàng tức thì).'
                    ],
                    [
                        'q' => 'Tôi có thể quản lý tệp XML ở đâu?',
                        'a' => 'Quản trị viên có thể truy cập trang quản trị Filament Admin > mục Quản lý Thực đơn XML để kiểm tra tính hợp lệ DTD, thêm món mới với ID duy nhất, chỉnh sửa mã nguồn XML trực tiếp và đồng bộ với cơ sở dữ liệu MySQL.'
                    ],
                ];
            @endphp

            @foreach ($faqs as $index => $faq)
                <div class="accordion-item border-0 border-bottom">
                    <h2 class="accordion-header" id="heading{{ $index }}">
                        <button class="accordion-button fw-bold text-dark {{ $index !== 0 ? 'collapsed' : '' }} py-3" 
                                type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapse{{ $index }}" 
                                aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" 
                                aria-controls="collapse{{ $index }}">
                            {{ $faq['q'] }}
                        </button>
                    </h2>
                    <div id="collapse{{ $index }}" 
                         class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" 
                         aria-labelledby="heading{{ $index }}" 
                         data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-secondary lh-lg small pt-0 pb-3">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
