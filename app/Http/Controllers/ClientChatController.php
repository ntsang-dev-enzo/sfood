<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientChatController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để chat với nhân viên.');
        }

        return view('pages.client-chat');
    }

    public function fetchMessages()
    {
        if (!Auth::check()) return response()->json([]);

        ChatMessage::where('user_id', Auth::id())
            ->where('sender', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = ChatMessage::where('user_id', Auth::id())
            ->oldest()
            ->get();

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        if (!Auth::check()) return response()->json(['error' => 'Unauthorized'], 401);

        $request->validate(['message' => 'required|string']);

        $msg = ChatMessage::create([
            'user_id' => Auth::id(),
            'sender' => 'user',
            'message' => $request->message,
            'is_read' => false,
        ]);

        // Notify admins
        $admins = \App\Models\User::whereIn('role', ['admin', 'manager', 'customer_service'])->get();
        \Filament\Notifications\Notification::make()
            ->title('Tin nhắn mới từ khách hàng')
            ->body(Auth::user()->name . ' vừa gửi tin nhắn.')
            ->icon('heroicon-o-chat-bubble-left-right')
            ->success()
            ->sendToDatabase($admins);

        return response()->json($msg);
    }
}
