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
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

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
