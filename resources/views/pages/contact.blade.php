@extends('layouts.app')
@section('title', 'Liên hệ - SFood')

@section('content')
<div class="bg-body-tertiary py-4 min-vh-100">
    <div class="container py-2">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-white p-3 rounded-3 shadow-sm border mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-success"><i class="bi bi-house-door me-1"></i>{{ __('app.home_title') }}</a></li>
                <li class="breadcrumb-item active fw-semibold" aria-current="page">Liên hệ với chúng tôi</li>
            </ol>
        </nav>

        <div class="row g-4">
            {{-- Contact Information --}}
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white h-100">
                    <h4 class="fw-bold text-dark mb-4 pb-2 border-bottom">
                        <i class="bi bi-geo-alt-fill text-danger me-2"></i>Thông Tin Liên Hệ
                    </h4>
                    
                    <div class="d-flex flex-column gap-3 mb-4">
                        <div class="d-flex gap-3 align-items-start p-3 bg-light rounded-3">
                            <i class="bi bi-building fs-4 text-success"></i>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Trụ sở chính</h6>
                                <p class="small text-secondary mb-0">123 Đường 30 Tháng 4, Phường Hưng Lợi, Quận Ninh Kiều, TP. Cần Thơ</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-start p-3 bg-light rounded-3">
                            <i class="bi bi-telephone fs-4 text-primary"></i>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Hotline CSKH</h6>
                                <p class="small text-secondary mb-0">1900 1234 (Hỗ trợ 7:00 - 22:00 tất cả các ngày)</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-start p-3 bg-light rounded-3">
                            <i class="bi bi-envelope fs-4 text-warning"></i>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Email hỗ trợ</h6>
                                <p class="small text-secondary mb-0">contact@sfood.vn / hotro@sfood.vn</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-start p-3 bg-light rounded-3">
                            <i class="bi bi-chat-dots fs-4 text-info"></i>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Hỗ trợ trực tuyến</h6>
                                <p class="small text-secondary mb-0">LiveChat trực tiếp tại góc phải màn hình 24/7</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact Form --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                    <h4 class="fw-bold text-dark mb-4 pb-2 border-bottom">
                        <i class="bi bi-envelope-paper text-success me-2"></i>Gửi Tin Nhắn Cho SFood
                    </h4>

                    @if(session('success'))
                        <div class="alert alert-success border-0 rounded-3 mb-4">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.send') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark">Họ và tên <span class="text-danger">*</span></label>
                                <input name="name" value="{{ old('name') }}" required class="form-control" placeholder="Nguyễn Văn A">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark">Địa chỉ Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" value="{{ old('email') }}" required class="form-control" placeholder="email@example.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark">Số điện thoại</label>
                                <input name="phone" value="{{ old('phone') }}" class="form-control" placeholder="0912345678">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark">Chủ đề <span class="text-danger">*</span></label>
                                <input name="subject" value="{{ old('subject') }}" required class="form-control" placeholder="Ví dụ: Hợp tác nông trại...">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold text-dark">Nội dung tin nhắn <span class="text-danger">*</span></label>
                                <textarea name="message" rows="5" required class="form-control" placeholder="Hãy cho chúng tôi biết bạn cần hỗ trợ điều gì...">{{ old('message') }}</textarea>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-success rounded-pill px-5 py-2.5 fw-semibold shadow-sm">
                                    <i class="bi bi-send me-1"></i> Gửi liên hệ
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
