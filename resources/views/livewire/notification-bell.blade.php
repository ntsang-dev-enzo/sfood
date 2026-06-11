<div class="relative" x-data="{ open: false }" @click.away="open = false" wire:poll.30s>
    {{-- Bell Button --}}
    <button @click="open = !open" class="relative p-2.5 rounded-xl hover:bg-cream-200 transition text-earth-700"
        title="Thông báo">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if ($unreadCount > 0)
            <span
                class="absolute top-1.5 right-1.5 bg-red-500 text-white text-[9px] w-4 h-4 rounded-full flex items-center justify-center font-bold border-2 border-cream-50">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-2xl border border-cream-200 z-50 overflow-hidden"
        style="display: none;">

        <div class="p-4 border-b border-cream-100 flex justify-between items-center bg-cream-50/50">
            <h3 class="font-bold text-earth-800">Thông báo</h3>
            @if ($unreadCount > 0)
                <button wire:click="markAllAsRead"
                    class="text-xs text-leaf-600 hover:text-leaf-700 font-medium transition">Đánh dấu tất cả đã đọc</button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto">
            @forelse ($notifications as $notification)
                <div class="p-4 border-b border-cream-50 hover:bg-cream-50/50 transition relative group">
                    <div class="flex gap-3">
                        <div
                            class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center {{ ($notification->data['type'] ?? 'order') === 'order' ? 'bg-leaf-100 text-leaf-600' : 'bg-blue-100 text-blue-600' }}">
                            @if(($notification->data['type'] ?? 'order') === 'order')
                                📦
                            @else
                                🔔
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-earth-800 leading-tight mb-1">
                                {{ $notification->data['title'] ?? 'Thông báo' }}
                            </p>
                            <p class="text-xs text-earth-600 line-clamp-2 mb-2">{{ $notification->data['message'] ?? '' }}
                            </p>
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-[10px] text-earth-400">{{ $notification->created_at->diffForHumans() }}</span>
                                <div class="flex gap-2">
                                    <button wire:click="markAsRead('{{ $notification->id }}')"
                                        class="text-[10px] text-leaf-600 opacity-0 group-hover:opacity-100 transition font-medium">Đã
                                        đọc</button>
                                    @if(!empty($notification->data['url']))
                                        <a href="{{ $notification->data['url'] }}"
                                            class="text-[10px] text-earth-500 font-medium">Chi tiết</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <div class="text-4xl mb-3">🌿</div>
                    <p class="text-sm text-earth-500">Hiện tại bạn chưa có thông báo mới.</p>
                </div>
            @endforelse
        </div>

        @if (Auth::user() && Auth::user()->notifications()->count() > 0)
            <a href="{{ route('notifications.index') }}"
                class="block p-3 text-center text-sm font-semibold text-earth-600 hover:bg-cream-50 border-t border-cream-100 transition">
                Xem tất cả thông báo
            </a>
        @endif
    </div>
</div>