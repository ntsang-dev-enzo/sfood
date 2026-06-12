<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function about()
    {
        return view('pages.about');
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function sendContact(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        $contact = \App\Models\Contact::create([
            'user_id' => auth()->id(),
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'message' => "Chủ đề: {$data['subject']}\n\nNội dung: {$data['message']}",
            'status' => 'pending',
        ]);

        // Send Filament Notification to Admins
        $admins = \App\Models\User::whereIn('role', ['admin', 'manager', 'customer_service'])->get();
        \Filament\Notifications\Notification::make()
            ->title('Liên hệ mới từ khách hàng')
            ->body("{$data['name']} vừa gửi một yêu cầu liên hệ.")
            ->icon('heroicon-o-envelope')
            ->success()
            ->sendToDatabase($admins);

        return back()->with('success', 'Cảm ơn bạn! Chúng tôi sẽ phản hồi trong vòng 24 giờ.');
    }

    public function faq()
    {
        return view('pages.faq');
    }

    public function policy()
    {
        return view('pages.policy');
    }
}
