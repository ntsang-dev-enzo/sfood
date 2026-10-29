<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('home')->with('info', 'Bạn đã đăng nhập vào hệ thống.');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        // Kiểm tra xem tài khoản có tồn tại trong hệ thống hay chưa
        $user = User::where('email', $credentials['email'])->first();
        if (!$user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Tài khoản chưa tồn tại trên hệ thống. Vui lòng đăng ký tài khoản mới trước khi đăng nhập.',
                ]);
        }

        // Kiểm tra thông tin đăng nhập
        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'password' => 'Mật khẩu không chính xác. Vui lòng thử lại hoặc bấm "Quên mật khẩu".',
                ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('home')->with('info', 'Bạn đã đăng nhập, không thể truy cập trang đăng ký.');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $messages = [
            'name.required' => 'Vui lòng nhập họ tên.',
            'name.max' => 'Họ tên không được quá 255 ký tự.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'email.unique' => 'Email này đã được sử dụng. Vui lòng chọn email khác.',
            'phone.max' => 'Số điện thoại không được quá 20 ký tự.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
        ];

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', 'min:8'],
        ], $messages);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => 'user',
        ]);

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'Đăng ký và tự động đăng nhập thành công! Chào mừng bạn đến với SFood.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
