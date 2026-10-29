@extends('layouts.profile')
@section('title', 'Thông báo - SFood')

@section('profile_content')
<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <h5 class="fw-bold text-dark mb-0">
            <i class="bi bi-bell-fill text-warning me-2"></i>Tất cả thông báo
        </h5>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3">
                    <i class="bi bi-check-all me-1"></i>Đánh dấu tất cả đã đọc
                </button>
            </form>
        @endif
    </div>

    {{-- Filter tabs --}}
    <div class="d-flex gap-2 mb-4 overflow-x-auto pb-1 scrollbar-none">
        <a href="{{ route('notifications.index') }}"
            class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ !($currentType ?? false) ? 'btn-success' : 'btn-light border' }}">
            Tất cả
        </a>
        <a href="{{ route('notifications.index', ['type' => 'account']) }}"
            class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ ($currentType ?? '') === 'account' ? 'btn-success' : 'btn-light border' }}">
            Tài khoản
        </a>
        <a href="{{ route('notifications.index', ['type' => 'order']) }}"
            class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ ($currentType ?? '') === 'order' ? 'btn-success' : 'btn-light border' }}">
            Đơn hàng
        </a>
        <a href="{{ route('notifications.index', ['type' => 'promotion']) }}"
            class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ ($currentType ?? '') === 'promotion' ? 'btn-success' : 'btn-light border' }}">
            Khuyến mãi
        </a>
    </div>

    <div class="d-flex flex-column gap-3">
        @forelse($notifications as $notification)
            <div class="p-3 rounded-3 border {{ $notification->read_at ? 'bg-light text-muted' : 'bg-white border-start border-4 border-success shadow-sm' }}">
                <div class="d-flex gap-3 align-items-start">
                    <div class="rounded-3 p-2 bg-success-subtle text-success fs-4 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        @if(($notification->data['type'] ?? 'order') === 'order')
                            <i class="bi bi-box-seam"></i>
                        @else
                            <i class="bi bi-bell"></i>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="fw-bold text-dark mb-0">{{ $notification->data['title'] ?? 'Thông báo' }}</h6>
                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="text-secondary small mb-2">{{ $notification->data['message'] ?? '' }}</p>
                        @if(($notification->data['action'] ?? '') === 'review_order')
                            <a href="{{ $notification->data['url'] ?? route('orders.index', ['status' => 'delivered']) }}"
                                class="btn btn-sm btn-warning rounded-pill px-3 py-1 fw-bold text-dark">
                                <i class="bi bi-star-fill me-1"></i>Đánh giá ngay
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <i class="bi bi-bell-slash display-3 text-muted opacity-50 mb-3"></i>
                <h5 class="fw-bold text-dark">Bạn chưa có thông báo nào</h5>
                <p class="text-muted small">Mọi cập nhật về đơn hàng và chương trình khuyến mãi sẽ hiển thị tại đây.</p>
                <div class="mt-2">
                    <a href="{{ route('home') }}" class="btn btn-outline-success rounded-pill px-4 py-2 fw-semibold">
                        Quay lại trang chủ
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
</div>
@endsection
