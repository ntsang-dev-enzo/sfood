@extends('layouts.app')
@section('title', 'Thông báo - SFood')
@section('content')
    <div class="bg-earth-800 text-cream-50 py-12">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-3xl font-bold">Thông báo của bạn</h1>
            <p class="text-cream-300 mt-2">Cập nhật những tin tức mới nhất từ SFood</p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-12">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-earth-800">Tất cả thông báo</h2>
            @if(auth()->user()->unreadNotifications->count() > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-leaf-600 hover:text-leaf-700">Đánh dấu tất cả đã
                        đọc</button>
                </form>
            @endif
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
                        @if(!empty($notification->data['url']))
                            <a href="{{ $notification->data['url'] }}" class="btn-primary !py-2 !px-4 !text-sm">Xem chi tiết</a>
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