<x-filament-panels::page>
    <div class="flex h-[600px] border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden bg-white dark:bg-gray-900 shadow-sm" wire:poll.3s>
        <!-- Sidebar -->
        <div class="w-1/3 border-r border-gray-200 dark:border-gray-800 overflow-y-auto bg-gray-50 dark:bg-gray-800/50">
            <div class="p-4 border-b border-gray-200 dark:border-gray-800 sticky top-0 bg-gray-50/90 dark:bg-gray-800/90 backdrop-blur z-10">
                <h3 class="font-bold text-lg text-gray-800 dark:text-gray-100">Khách hàng</h3>
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($this->conversations as $user)
                    <li>
                        <button wire:click="selectUser({{ $user->id }})" class="w-full text-left px-4 py-3 hover:bg-white dark:hover:bg-gray-800 transition flex items-center gap-3 {{ $selectedUser?->id === $user->id ? 'bg-white dark:bg-gray-800 shadow-sm border-l-4 border-primary-500' : '' }}">
                            <div class="w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900/50 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-lg shrink-0">
                                {{ mb_substr($user->name, 0, 1) }}
                            </div>
                            <div class="flex-1 overflow-hidden">
                                <div class="font-bold truncate text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                @php
                                    $latest = $user->chatMessages()->latest()->first();
                                    $unread = $user->chatMessages()->where('sender', 'user')->where('is_read', false)->count();
                                @endphp
                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                    {{ $latest?->sender === 'admin' ? 'Bạn: ' : '' }}{{ $latest?->message ?? '' }}
                                </div>
                            </div>
                            @if($unread > 0)
                                <div class="w-6 h-6 bg-danger-500 text-white rounded-full flex items-center justify-center text-xs font-bold shrink-0 shadow-sm">
                                    {{ $unread }}
                                </div>
                            @endif
                        </button>
                    </li>
                @empty
                    <li class="p-8 text-gray-500 text-center flex flex-col items-center justify-center gap-2">
                        <svg class="w-12 h-12 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
                        <span>Chưa có tin nhắn nào.</span>
                    </li>
                @endforelse
            </ul>
        </div>

        <!-- Chat Area -->
        <div class="flex-1 flex flex-col bg-[#f0f2f5] dark:bg-gray-900 relative">
            @if($selectedUser)
                <!-- Header -->
                <div class="p-4 bg-white dark:bg-gray-800 flex items-center gap-4 shadow-sm z-10 sticky top-0">
                    <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/50 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-lg">
                        {{ mb_substr($selectedUser->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="font-bold text-lg text-gray-900 dark:text-gray-100">{{ $selectedUser->name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2">
                            <span>Email: {{ $selectedUser->email }}</span>
                            @if($selectedUser->phone)
                                <span>• SĐT: {{ $selectedUser->phone }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <div class="flex-1 overflow-y-auto p-6 space-y-6" id="chat-messages-container">
                    @foreach($this->messages as $msg)
                        <div class="flex {{ $msg->sender === 'admin' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[70%] {{ $msg->sender === 'admin' ? 'bg-primary-600 text-white rounded-tr-none shadow-md shadow-primary-500/20' : 'bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-gray-800 dark:text-gray-200 rounded-tl-none shadow-sm' }} px-4 py-3 rounded-2xl relative group">
                                <div class="text-sm leading-relaxed">{!! nl2br(e($msg->message)) !!}</div>
                                <div class="text-[10px] mt-1.5 {{ $msg->sender === 'admin' ? 'text-primary-200' : 'text-gray-400 dark:text-gray-500' }} text-right font-medium">
                                    {{ $msg->created_at->format('H:i') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Input -->
                <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                    <form wire:submit.prevent="sendMessage" class="flex gap-3">
                        <input type="text" wire:model.defer="messageText" class="flex-1 bg-gray-100 dark:bg-gray-900 border-transparent focus:bg-white dark:focus:bg-gray-800 focus:border-primary-500 focus:ring-primary-500 rounded-full px-6 py-3 text-sm transition shadow-inner" placeholder="Nhập tin nhắn để trả lời {{ $selectedUser->name }}..." required autocomplete="off">
                        <button type="submit" class="bg-primary-600 hover:bg-primary-500 text-white w-12 h-12 rounded-full flex items-center justify-center transition shadow-md shadow-primary-500/30 shrink-0 group">
                            <svg class="w-5 h-5 transform rotate-45 group-hover:scale-110 transition -mt-1 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        </button>
                    </form>
                </div>
            @else
                <div class="flex-1 flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                    <div class="w-24 h-24 rounded-full bg-gray-200 dark:bg-gray-800 flex items-center justify-center mb-4">
                        <svg class="w-12 h-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <p class="font-medium text-lg">Hỗ trợ trực tuyến</p>
                    <p class="text-sm mt-1">Chọn một cuộc trò chuyện từ danh sách bên trái để bắt đầu</p>
                </div>
            @endif
        </div>
    </div>
    
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Cuộn xuống cuối chat mỗi khi cập nhật component
            Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
                succeed(({ snapshot, effect }) => {
                    queueMicrotask(() => {
                        const container = document.getElementById('chat-messages-container');
                        if (container) {
                            container.scrollTop = container.scrollHeight;
                        }
                    });
                });
            });
            
            // Cuộn lần đầu
            const container = document.getElementById('chat-messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });
    </script>
</x-filament-panels::page>
