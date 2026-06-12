<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type');
        
        $query = Auth::user()->notifications();
        
        if ($type) {
            $query->where('data->type', $type);
        }

        $notifications = $query->latest()->paginate(10)->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
            'currentType' => $type,
        ]);
    }

    public function readAll()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
    }
}
