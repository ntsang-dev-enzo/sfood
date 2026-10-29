<div class="dropdown" wire:poll.30s>
    {{-- Bell Button (Bootstrap Dropdown Toggle) --}}
    <button class="btn btn-light rounded-circle position-relative p-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Thông báo">
        <i class="bi bi-bell fs-5 text-dark"></i>
        @if ($unreadCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                <span class="visually-hidden">thông báo mới</span>
            </span>
        @endif
    </button>

    {{-- Dropdown Menu --}}
    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-0 mt-2" style="width: 320px; max-width: 90vw;">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light rounded-top-4">
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-bell-fill text-warning me-1"></i>Thông báo</h6>
            @if ($unreadCount > 0)
                <button wire:click="markAllAsRead" class="btn btn-link text-success p-0 small text-decoration-none" style="font-size: 0.75rem;">
                    Đã đọc tất cả
                </button>
            @endif
        </div>

        <div class="overflow-y-auto" style="max-height: 340px;">
            @forelse ($notifications as $notification)
                <div class="p-3 border-bottom border-light-subtle {{ $notification->read_at ? 'bg-white' : 'bg-success-subtle bg-opacity-25' }}">
                    <div class="d-flex gap-2">
                        <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                            @if(($notification->data['type'] ?? 'order') === 'order')
                                <i class="bi bi-box-seam"></i>
                            @else
                                <i class="bi bi-bell"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <p class="small fw-bold text-dark mb-1 lh-sm">{{ $notification->data['title'] ?? 'Thông báo' }}</p>
                            <p class="small text-secondary mb-1 lh-sm" style="font-size: 0.8rem;">{{ $notification->data['message'] ?? '' }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted" style="font-size: 0.7rem;">{{ $notification->created_at->diffForHumans() }}</span>
                                <div class="d-flex gap-2">
                                    <button wire:click="markAsRead('{{ $notification->id }}')" class="btn btn-link text-success p-0 text-decoration-none" style="font-size: 0.7rem;">Đã đọc</button>
                                    @if(!empty($notification->data['url']))
                                        <a href="{{ $notification->data['url'] }}" class="btn btn-link text-primary p-0 text-decoration-none" style="font-size: 0.7rem;">Chi tiết</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-bell-slash fs-2 opacity-50 mb-1 d-block"></i>
                    <p class="small mb-0">Chưa có thông báo mới.</p>
                </div>
            @endforelse
        </div>

        @if (Auth::user() && Auth::user()->notifications()->count() > 0)
            <div class="p-2 text-center border-top bg-light rounded-bottom-4">
                <a href="{{ route('notifications.index') }}" class="small fw-semibold text-success text-decoration-none">
                    Xem tất cả thông báo <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        @endif
    </div>
</div>
