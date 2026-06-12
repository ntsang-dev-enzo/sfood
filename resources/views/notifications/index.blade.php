@extends('layouts.profile')
@section('title', 'Thông báo - SFood')
@section('profile_content')
    <div class="card p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-earth-800">Tất cả thông báo</h2>
            @if(auth()->user()->unreadNotifications->count() > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-leaf-600 hover:text-leaf-700">Đánh dấu tất cả đã
                        đọc</button>
                </form>
            @endif
        </div>

        <div class="flex gap-2 mb-6 overflow-x-auto scrollbar-hide pb-2">
            <a href="{{ route('notifications.index') }}"
                class="px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap transition {{ !($currentType ?? false) ? 'bg-earth-800 text-white' : 'bg-cream-100 text-earth-600 hover:bg-cream-200' }}">Tất
                cả</a>
            <a href="{{ route('notifications.index', ['type' => 'account']) }}"
                class="px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap transition {{ ($currentType ?? '') === 'account' ? 'bg-earth-800 text-white' : 'bg-cream-100 text-earth-600 hover:bg-cream-200' }}">Tài
                khoản</a>
            <a href="{{ route('notifications.index', ['type' => 'order']) }}"
                class="px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap transition {{ ($currentType ?? '') === 'order' ? 'bg-earth-800 text-white' : 'bg-cream-100 text-earth-600 hover:bg-cream-200' }}">Đơn
                hàng</a>
            <a href="{{ route('notifications.index', ['type' => 'promotion']) }}"
                class="px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap transition {{ ($currentType ?? '') === 'promotion' ? 'bg-earth-800 text-white' : 'bg-cream-100 text-earth-600 hover:bg-cream-200' }}">Khuyến
                mãi</a>
        </div>

        <div class="space-y-4">
            @forelse($notifications as $notification)
                <div
                    class="card p-6 flex gap-4 items-start {{ $notification->read_at ? 'opacity-70' : 'border-l-4 border-l-leaf-500 bg-white' }}">
                    <div
                        class="w-12 h-12 rounded-2xl flex-shrink-0 flex items-center justify-center {{ ($notification->data['type'] ?? 'order') === 'order' ? 'bg-leaf-100 text-leaf-600' : 'bg-blue-100 text-blue-600' }}">
                        @if(($notification->data['type'] ?? 'order') === 'order')
                            📦
                        @else
                            🔔
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start mb-1">
                            <h3 class="font-bold text-earth-800">{{ $notification->data['title'] ?? 'Thông báo' }}</h3>
                            <span class="text-xs text-earth-400">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-earth-600 mb-4">{{ $notification->data['message'] ?? '' }}</p>
                        @if(($notification->data['action'] ?? '') === 'review_order')
                            <a href="{{ $notification->data['url'] ?? route('orders.index', ['status' => 'delivered']) }}"
                                class="btn-primary !py-2 !px-4 !text-sm !bg-yellow-500 hover:!bg-yellow-600 !border-yellow-500 !text-white">Đánh
                                giá ngay</a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-20 bg-cream-50 rounded-3xl border border-dashed border-cream-300">
                    <div class="text-6xl mb-4">🥥</div>
                    <p class="text-earth-500 font-medium">Bạn chưa có thông báo nào.</p>
                    <a href="{{ route('home') }}" class="btn-outline !py-2 mt-6">Quay lại trang chủ</a>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $notifications->links() }}
        </div>
    </div>
@endsection