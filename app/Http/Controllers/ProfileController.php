<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\ShippingAddress;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    public function address()
    {
        return view('profile.address', [
            'user' => Auth::user(),
        ]);
    }

    public function coupons()
    {
        $user = Auth::user();
        $coupons = \App\Models\Coupon::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get();

        return view('profile.coupons', compact('user', 'coupons'));
    }

    public function storeAddress(Request $request)
    {
        $user = Auth::user();

        if ($user->shippingAddresses()->count() >= 5) {
            return back()->with('error', 'Bạn chỉ được lưu tối đa 5 địa chỉ.');
        }

        $data = $request->validate([
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address_line' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'district' => 'nullable|string|max:100',
            'ward' => 'nullable|string|max:100',
            'is_default' => 'nullable|boolean',
        ]);

        if (!empty($data['is_default'])) {
            $user->shippingAddresses()->update(['is_default' => false]);
        }

        $user->shippingAddresses()->create($data);

        return back()->with('success', 'Đã lưu địa chỉ giao hàng.');
    }

    public function updateAddress(Request $request, ShippingAddress $address)
    {
        $user = Auth::user();
        if ($address->user_id !== $user->id) abort(403);

        $data = $request->validate([
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address_line' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'district' => 'nullable|string|max:100',
            'ward' => 'nullable|string|max:100',
            'is_default' => 'nullable|boolean',
        ]);

        if (!empty($data['is_default'])) {
            $user->shippingAddresses()->update(['is_default' => false]);
        }

        $address->update($data);

        return back()->with('success', 'Đã cập nhật địa chỉ.');
    }

    public function destroyAddress(Request $request, ShippingAddress $address)
    {
        $user = Auth::user();
        if ($address->user_id !== $user->id) abort(403);

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $first = $user->shippingAddresses()->first();
            if ($first) $first->update(['is_default' => true]);
        }

        return back()->with('success', 'Đã xoá địa chỉ.');
    }

    public function setDefaultAddress(Request $request, ShippingAddress $address)
    {
        $user = Auth::user();
        if ($address->user_id !== $user->id) abort(403);

        $user->shippingAddresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('success', 'Đã đặt địa chỉ mặc định.');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar && ! str_starts_with($user->avatar, 'http')) {
                \Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            unset($data['avatar']);
        }

        $user->update($data);

        return back()->with('success', 'Đã cập nhật thông tin tài khoản.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Mật khẩu hiện tại không đúng.');
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Đã đổi mật khẩu thành công.');
    }
}
