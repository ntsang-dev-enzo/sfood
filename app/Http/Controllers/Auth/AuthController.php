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
        $loginInput = trim((string) ($request->input('login') ?? $request->input('email') ?? $request->input('phone') ?? ''));

        $request->merge([
            'login' => $loginInput,
            'email' => $loginInput,
        ]);

        $request->validate([
            'login' => 'required',
            'password' => 'required',
        ], [
            'login.required' => 'Vui lòng nhập email hoặc số điện thoại.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);
        $cleanPhone = preg_replace('/[^0-9]/', '', $loginInput);

        if ($isEmail) {
            $user = User::where('email', $loginInput)->first();
        } else {
            $user = User::where('phone', $loginInput);
            if (!empty($cleanPhone)) {
                $user = $user->orWhere('phone', $cleanPhone);
            }
            $user = $user->first();
        }

        // Kiểm tra xem tài khoản có tồn tại trong hệ thống hay chưa
        if (!$user) {
            return back()
                ->withInput($request->only('email', 'login'))
                ->withErrors([
                    'email' => 'Tài khoản chưa tồn tại trên hệ thống. Vui lòng đăng ký tài khoản mới trước khi đăng nhập.',
                    'login' => 'Tài khoản chưa tồn tại trên hệ thống. Vui lòng đăng ký tài khoản mới trước khi đăng nhập.',
                ]);
        }

        // Kiểm tra thông tin đăng nhập
        if (!Hash::check($request->password, $user->password)) {
            return back()
                ->withInput($request->only('email', 'login'))
                ->withErrors([
                    'password' => 'Mật khẩu không chính xác. Vui lòng thử lại hoặc bấm "Quên mật khẩu".',
                ]);
        }

        Auth::login($user, $request->boolean('remember'));
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
        // Chuẩn hóa số điện thoại: loại bỏ khoảng trắng và ký tự không phải số
        if ($request->filled('phone')) {
            $cleanPhone = preg_replace('/[^0-9]/', '', (string) $request->input('phone'));
            $request->merge(['phone' => $cleanPhone]);
        }

        $messages = [
            'name.required' => 'Vui lòng nhập họ tên.',
            'name.max' => 'Họ tên không được quá 255 ký tự.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'email.unique' => 'Email này đã được sử dụng. Vui lòng chọn email khác.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại không hợp lệ (gồm 10 số, bắt đầu bằng số 0).',
            'phone.unique' => 'Số điện thoại này đã được đăng ký tài khoản. Mỗi số điện thoại chỉ được đăng ký 1 tài khoản.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
        ];

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => ['required', 'string', 'regex:/^0[0-9]{9}$/', 'unique:users,phone'],
            'password' => ['required', 'confirmed', 'min:8'],
        ], $messages);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
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
