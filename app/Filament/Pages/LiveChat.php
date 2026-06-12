<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Collection;

class LiveChat extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string $view = 'filament.pages.live-chat';
    
    protected static ?string $navigationLabel = 'Chat Khách Hàng';
    protected static ?string $title = 'Hỗ trợ trực tuyến';
    protected static ?string $navigationGroup = 'Quản lý khách hàng';

    public static function getNavigationBadge(): ?string
    {
        $unread = ChatMessage::where('sender', 'user')->where('is_read', false)->count();
        return $unread > 0 ? (string) $unread : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function canAccess(): bool
    {
        return auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isCustomerService());
    }

    public ?User $selectedUser = null;
    public string $messageText = '';

    protected $listeners = ['refreshChat' => '$refresh'];

    public function mount()
    {
        // Initialize if needed
    }

    public function selectUser($userId)
    {
        $this->selectedUser = User::find($userId);
    }

    public function sendMessage()
    {
        if (!$this->selectedUser || empty(trim($this->messageText))) {
            return;
        }

        ChatMessage::create([
            'user_id' => $this->selectedUser->id,
            'sender' => 'admin',
            'message' => $this->messageText,
            'is_read' => false, // changed to false because it's unread by user
        ]);

        $this->selectedUser->notify(new \App\Notifications\AdminRepliedChat($this->messageText));

        $this->messageText = '';
    }

    public function getConversationsProperty()
    {
        // Get users who have messages, sorted by latest message
        $userIds = ChatMessage::select('user_id')
            ->distinct()
            ->pluck('user_id');

        return User::whereIn('id', $userIds)
            ->get()
            ->sortByDesc(function ($user) {
                return $user->chatMessages()->latest()->first()?->created_at;
            });
    }

    public function getMessagesProperty()
    {
        if (!$this->selectedUser) {
            return collect();
        }

        // Mark as read when admin opens it
        ChatMessage::where('user_id', $this->selectedUser->id)
            ->where('sender', 'user')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return ChatMessage::where('user_id', $this->selectedUser->id)
            ->oldest()
            ->get();
    }
}
