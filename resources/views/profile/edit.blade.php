@extends('layouts.app')
@section('title', 'Tài khoản - SFood')
@section('content')
@php use App\Support\MediaHelper; @endphp
<div class="bg-earth-800 text-cream-50 py-12">
    <div class="max-w-3xl mx-auto px-4">
        <h1 class="text-3xl font-bold">Tài khoản của tôi</h1>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 py-8 space-y-6">
    <div class="card p-6">
        <h2 class="font-bold text-lg text-earth-800 mb-4">Thông tin cá nhân</h2>
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="flex items-center gap-4">
                <img src="{{ MediaHelper::avatar($user->avatar, $user->name) }}" class="w-20 h-20 rounded-2xl object-cover border-2 border-cream-300" alt="">
                <div>
                    <label class="text-sm font-medium text-earth-700">Ảnh đại diện</label>
                    <input type="file" name="avatar" accept="image/*" class="text-sm mt-1 text-earth-600">
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-earth-700">Họ tên</label>
                <input name="name" value="{{ old('name', $user->name) }}" required class="input-field mt-1">
            </div>
            <div>
                <label class="text-sm font-medium text-earth-700">Email</label>
                <input value="{{ $user->email }}" disabled class="input-field mt-1 bg-cream-100">
            </div>
            <div>
                <label class="text-sm font-medium text-earth-700">Số điện thoại</label>
                <input name="phone" value="{{ old('phone', $user->phone) }}" class="input-field mt-1">
            </div>
            <button type="submit" class="btn-primary">Lưu thay đổi</button>
        </form>
    </div>

    <div class="card p-6">
        <h2 class="font-bold text-lg text-earth-800 mb-4">Đổi mật khẩu</h2>
        <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="text-sm font-medium text-earth-700">Mật khẩu hiện tại</label>
                <input type="password" name="current_password" required class="input-field mt-1">
            </div>
            <div>
                <label class="text-sm font-medium text-earth-700">Mật khẩu mới</label>
                <input type="password" name="password" required class="input-field mt-1">
            </div>
            <div>
                <label class="text-sm font-medium text-earth-700">Xác nhận mật khẩu mới</label>
                <input type="password" name="password_confirmation" required class="input-field mt-1">
            </div>
            <button type="submit" class="btn-secondary">Đổi mật khẩu</button>
        </form>
    </div>

    <div class="card p-6">
        <h2 class="font-bold text-lg text-earth-800 mb-4">Địa chỉ giao hàng</h2>

        @php $addresses = $user->shippingAddresses; @endphp

        @if ($addresses->count())
            <div class="space-y-4 mb-4">
                @foreach ($addresses as $addr)
                    <div class="border p-4 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium">{{ $addr->recipient_name }} — {{ $addr->phone }} @if($addr->is_default) <span class="text-sm text-leaf-700">(Mặc định)</span> @endif</p>
                                <p class="text-sm text-earth-600">{{ $addr->fullAddress() }}</p>
                            </div>
                            <div class="flex gap-2">
                                @if (! $addr->is_default)
                                    <form method="POST" action="{{ route('profile.address.default', $addr) }}">
                                        @csrf
                                        <button type="submit" class="btn-ghost">Đặt làm mặc định</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('profile.address.destroy', $addr) }}" onsubmit="return confirm('Xoá địa chỉ này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-ghost">Xoá</button>
                                </form>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('profile.address.update', $addr) }}" class="mt-3 grid grid-cols-2 gap-2">
                            @csrf
                            @method('PUT')
                            <input name="recipient_name" value="{{ old('recipient_name', $addr->recipient_name) }}" placeholder="Họ tên" class="input-field">
                            <input name="phone" value="{{ old('phone', $addr->phone) }}" placeholder="Số điện thoại" class="input-field">
                            <input name="address_line" value="{{ old('address_line', $addr->address_line) }}" placeholder="Địa chỉ" class="input-field col-span-2">
                            <input name="city" value="{{ old('city', $addr->city) }}" placeholder="Tỉnh/TP" class="input-field">
                            <input name="district" value="{{ old('district', $addr->district) }}" placeholder="Quận/Huyện" class="input-field">
                            <input name="ward" value="{{ old('ward', $addr->ward) }}" placeholder="Phường/Xã" class="input-field">
                            <label class="flex items-center gap-2 mt-2">
                                <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $addr->is_default))>
                                <span class="text-sm">Đặt là địa chỉ mặc định</span>
                            </label>
                            <div class="col-span-2 mt-2">
                                <button type="submit" class="btn-secondary">Lưu</button>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-earth-500 mb-4">Chưa có địa chỉ giao hàng nào.</p>
        @endif

        @if ($addresses->count() < 5)
            <form method="POST" action="{{ route('profile.address.store') }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-2">
                    <input name="recipient_name" value="{{ old('recipient_name') }}" placeholder="Họ tên" class="input-field">
                    <input name="phone" value="{{ old('phone') }}" placeholder="Số điện thoại" class="input-field">
                    <input name="address_line" value="{{ old('address_line') }}" placeholder="Địa chỉ" class="input-field col-span-2">
                    <input name="city" value="{{ old('city') }}" placeholder="Tỉnh/TP" class="input-field">
                    <input name="district" value="{{ old('district') }}" placeholder="Quận/Huyện" class="input-field">
                    <input name="ward" value="{{ old('ward') }}" placeholder="Phường/Xã" class="input-field">
                </div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_default" value="1" @checked(old('is_default'))>
                    <span class="text-sm">Đặt là địa chỉ mặc định</span>
                </label>
                <button type="submit" class="btn-primary">Thêm địa chỉ</button>
            </form>
        @else
            <p class="text-sm text-earth-500">Bạn đã lưu 5 địa chỉ - xóa bớt để thêm địa chỉ mới.</p>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const API_BASE = 'https://provinces.open-api.vn/api';

    async function fetchJSON(url) {
        try {
            const r = await fetch(url);
            if (!r.ok) throw new Error('Network');
            return await r.json();
        } catch (e) {
            console.error('Province API error', e);
            return null;
        }
    }

    async function loadProvincesFor(selectEl, oldValue) {
        const data = await fetchJSON(`${API_BASE}/p/`);
        if (!data) return;
        selectEl.innerHTML = '<option value="">Chọn Tỉnh/TP</option>';
        data.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.name;
            opt.dataset.code = p.code;
            opt.textContent = p.name;
            selectEl.appendChild(opt);
        });
        if (oldValue) selectEl.value = oldValue;
    }

    async function loadDistrictsFor(selectEl, provinceCode, oldValue) {
        selectEl.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
        if (!provinceCode) return;
        const data = await fetchJSON(`${API_BASE}/p/${provinceCode}?depth=2`);
        if (!data || !data.districts) return;
        data.districts.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d.name;
            opt.dataset.code = d.code;
            opt.textContent = d.name;
            selectEl.appendChild(opt);
        });
        if (oldValue) selectEl.value = oldValue;
    }

    async function loadWardsFor(selectEl, districtCode, oldValue) {
        selectEl.innerHTML = '<option value="">Chọn Phường/Xã</option>';
        if (!districtCode) return;
        const data = await fetchJSON(`${API_BASE}/d/${districtCode}?depth=2`);
        if (!data || !data.wards) return;
        data.wards.forEach(w => {
            const opt = document.createElement('option');
            opt.value = w.name;
            opt.textContent = w.name;
            selectEl.appendChild(opt);
        });
        if (oldValue) selectEl.value = oldValue;
    }

    // For every form that contains city input, replace city/district/ward inputs with selects
    document.querySelectorAll('form').forEach(function(form) {
        const cityInput = form.querySelector('input[name="city"]');
        const districtInput = form.querySelector('input[name="district"]');
        const wardInput = form.querySelector('input[name="ward"]');

        if (!cityInput) return;

        // create selects
        const provinceSelect = document.createElement('select');
        provinceSelect.name = 'city';
        provinceSelect.className = cityInput.className || '';

        const districtSelect = document.createElement('select');
        districtSelect.name = 'district';
        districtSelect.className = districtInput?.className || '';

        const wardSelect = document.createElement('select');
        wardSelect.name = 'ward';
        wardSelect.className = wardInput?.className || '';

        // replace inputs with selects
        cityInput.parentNode.replaceChild(provinceSelect, cityInput);
        if (districtInput) districtInput.parentNode.replaceChild(districtSelect, districtInput);
        if (wardInput) wardInput.parentNode.replaceChild(wardSelect, wardInput);

        const oldCity = cityInput.value || '';
        const oldDistrict = districtInput?.value || '';
        const oldWard = wardInput?.value || '';

        loadProvincesFor(provinceSelect, oldCity).then(async () => {
            const provOpt = Array.from(provinceSelect.options).find(o => o.value === oldCity);
            if (provOpt && provOpt.dataset.code) {
                await loadDistrictsFor(districtSelect, provOpt.dataset.code, oldDistrict);
                const distOpt = Array.from(districtSelect.options).find(o => o.value === oldDistrict);
                if (distOpt && distOpt.dataset.code) {
                    await loadWardsFor(wardSelect, distOpt.dataset.code, oldWard);
                }
            }
        });

        provinceSelect.addEventListener('change', function() {
            const code = this.selectedOptions[0]?.dataset?.code;
            loadDistrictsFor(districtSelect, code);
            // clear wards when province changes
            wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
        });

        districtSelect.addEventListener('change', function() {
            const code = this.selectedOptions[0]?.dataset?.code;
            loadWardsFor(wardSelect, code);
        });
    });
});
</script>
@endpush
