@extends('layouts.profile')
@section('profile_content')
@php use App\Support\MediaHelper; @endphp

<div class="d-flex flex-column gap-4">
    {{-- Personal Info Card --}}
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom">
            <i class="bi bi-person-circle text-success me-2"></i>Thông tin cá nhân
        </h5>
        
        @if(session('status') === 'profile-updated' || session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-3 mb-3">
                <i class="bi bi-check-circle me-2"></i>Thông tin cá nhân đã được cập nhật thành công!
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 mb-4">
                <img src="{{ MediaHelper::avatar($user->avatar, $user->name) }}" 
                     class="rounded-circle object-fit-cover border border-3 border-success-subtle shadow-sm" 
                     style="width: 80px; height: 80px;" alt="{{ $user->name }}">
                <div>
                    <label class="form-label small fw-semibold text-dark mb-1">Ảnh đại diện mới</label>
                    <input type="file" name="avatar" accept="image/*" class="form-control form-control-sm">
                    <small class="text-muted">Định dạng JPG, PNG, WEBP tối đa 2MB.</small>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-dark">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-dark">Email đăng nhập</label>
                    <input type="email" value="{{ $user->email }}" disabled class="form-control bg-light">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-dark">Số điện thoại</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control" placeholder="Chưa cập nhật">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-dark">Điểm thưởng hiện có</label>
                    <div class="form-control bg-light fw-bold text-success">
                        <i class="bi bi-coin text-warning me-1"></i>{{ number_format($user->reward_points) }} điểm
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-2">
                <button type="submit" class="btn btn-success rounded-pill px-4 py-2 fw-semibold shadow-sm">
                    <i class="bi bi-save me-1"></i> Lưu thay đổi
                </button>
            </div>
        </form>
    </div>

    {{-- Change Password Card --}}
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom">
            <i class="bi bi-shield-lock text-warning me-2"></i>Đổi mật khẩu
        </h5>

        @if(session('status') === 'password-updated')
            <div class="alert alert-success border-0 shadow-sm rounded-3 mb-3">
                <i class="bi bi-check-circle me-2"></i>Mật khẩu đã được thay đổi thành công!
            </div>
        @endif

        <form method="POST" action="{{ route('profile.password') }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-dark">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                    <input type="password" name="current_password" required class="form-control">
                    @error('current_password') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-dark">Mật khẩu mới <span class="text-danger">*</span></label>
                    <input type="password" name="password" required class="form-control">
                    @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-dark">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirmation" required class="form-control">
                </div>
            </div>
            <div class="mt-4 pt-2">
                <button type="submit" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
                    <i class="bi bi-key me-1"></i> Cập nhật mật khẩu
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
