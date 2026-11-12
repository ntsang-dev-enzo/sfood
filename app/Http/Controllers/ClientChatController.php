<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Services\ChatAutoReplyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientChatController extends Controller
{
    public function __construct(
        protected ChatAutoReplyService $autoReply,
    ) {
    }

    public function index()
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để chat với nhân viên.');
        }

        return view('pages.client-chat');
    }

    public function fetchMessages()
    {
        if (! Auth::check()) {
            return response()->json([]);
        }

        ChatMessage::where('user_id', Auth::id())
            ->where('sender', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = ChatMessage::where('user_id', Auth::id())
            ->oldest()
            ->get()
            ->map(fn (ChatMessage $m) => [
                'id' => $m->id,
                'sender' => $m->sender,
                'message' => $m->message,
                'is_auto' => (bool) $m->is_auto,
                'time' => $m->created_at?->format('H:i'),
            ]);

        return response()->json($messages);
    }

    /**
     * Danh sách câu hỏi gợi ý cho khung chat.
     */
    public function suggestions()
    {
        return response()->json(
            $this->autoReply->suggestions(6)->map(fn ($r) => [
                'code' => $r->code,
                'question' => $r->question,
            ])->values()
        );
    }

    public function sendMessage(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $text = trim($validated['message']);

        $message = ChatMessage::create([
            'user_id' => $user->id,
            'sender' => 'user',
            'message' => $text,
            'is_read' => false,
            'is_auto' => false,
        ]);

        // Tầng trả lời tự động
        $result = $this->autoReply->respond($user, $text);
        $botMessage = null;

        if (! empty($result['answer'])) {
            $botMessage = ChatMessage::create([
                'user_id' => $user->id,
                'sender' => 'bot',
                'message' => $result['answer'],
                'is_read' => true,
                'is_auto' => true,
            ]);
        }

        // Chỉ đánh động nhân viên khi bot không trả lời được hoặc khách yêu cầu gặp người thật
        if ($result['fallback'] || $result['need_human']) {
            $staff = \App\Models\User::whereIn('role', ['admin', 'manager', 'customer_service'])->get();

            \Filament\Notifications\Notification::make()
                ->title('Tin nhắn mới từ khách hàng')
                ->body($user->name.' vừa gửi tin nhắn cần hỗ trợ trực tiếp.')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->success()
                ->sendToDatabase($staff);
        }

        return response()->json([
            'user_message' => [
                'id' => $message->id,
                'sender' => 'user',
                'message' => $message->message,
                'is_auto' => false,
                'time' => $message->created_at?->format('H:i'),
            ],
            'bot_message' => $botMessage ? [
                'id' => $botMessage->id,
                'sender' => 'bot',
                'message' => $botMessage->message,
                'is_auto' => true,
                'time' => $botMessage->created_at?->format('H:i'),
            ] : null,
            'fallback' => $result['fallback'],
            'need_human' => $result['need_human'],
        ]);
    }
}
