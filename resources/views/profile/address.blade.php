@extends('layouts.profile')
@section('profile_content')
<div class="d-flex flex-column gap-4">
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-3">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom d-flex align-items-center justify-content-between">
            <span><i class="bi bi-geo-alt-fill text-danger me-2"></i>Sổ địa chỉ nhận hàng</span>
            @php $addresses = $user->shippingAddresses; @endphp
            <span class="badge bg-secondary-subtle text-secondary rounded-pill small">{{ $addresses->count() }}/5 địa chỉ</span>
        </h5>

        @if ($addresses->count())
            <div class="d-flex flex-column gap-3 mb-4">
                @foreach ($addresses as $addr)
                    <div class="border rounded-3 p-3 bg-light">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <h6 class="fw-bold text-dark mb-1">
                                    {{ $addr->recipient_name }} — {{ $addr->phone }}
                                    @if($addr->is_default)
                                        <span class="badge bg-success-subtle text-success ms-2">Mặc định</span>
                                    @endif
                                </h6>
                                <p class="text-secondary small mb-0">{{ $addr->fullAddress() }}</p>
                            </div>
                            <div class="d-flex gap-2">
                                @if (! $addr->is_default)
                                    <form method="POST" action="{{ route('profile.address.default', $addr) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 small">Mặc định</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('profile.address.destroy', $addr) }}" onsubmit="return confirm('Xoá địa chỉ này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1 small">Xoá</button>
                                </form>
                            </div>
                        </div>

                        <details class="mt-2">
                            <summary class="small text-success cursor-pointer fw-semibold">Chỉnh sửa địa chỉ này</summary>
                            <form method="POST" action="{{ route('profile.address.update', $addr) }}" class="mt-3 row g-2">
                                @csrf
                                @method('PUT')
                                <div class="col-md-6">
                                    <input name="recipient_name" value="{{ old('recipient_name', $addr->recipient_name) }}" placeholder="Họ tên" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-6">
                                    <input name="phone" value="{{ old('phone', $addr->phone) }}" placeholder="Số điện thoại" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-12">
                                    <input name="address_line" value="{{ old('address_line', $addr->address_line) }}" placeholder="Địa chỉ chi tiết (Số nhà, đường)" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-4">
                                    <input name="city" value="{{ old('city', $addr->city) }}" placeholder="Tỉnh/TP" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-4">
                                    <input name="district" value="{{ old('district', $addr->district) }}" placeholder="Quận/Huyện" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-4">
                                    <input name="ward" value="{{ old('ward', $addr->ward) }}" placeholder="Phường/Xã" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_default" id="def-{{ $addr->id }}" value="1" @checked(old('is_default', $addr->is_default))>
                                        <label class="form-check-label small" for="def-{{ $addr->id }}">Đặt làm địa chỉ nhận hàng mặc định</label>
                                    </div>
                                </div>
                                <div class="col-12 mt-2">
                                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">Lưu thay đổi</button>
                                </div>
                            </form>
                        </details>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-muted small mb-4">Bạn chưa lưu địa chỉ giao hàng nào.</p>
        @endif

        @if ($addresses->count() < 5)
            <div class="border-top pt-4">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-plus-circle text-success me-1"></i>Thêm địa chỉ giao hàng mới</h6>
                <form method="POST" action="{{ route('profile.address.store') }}" class="row g-2">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Họ tên người nhận <span class="text-danger">*</span></label>
                        <input name="recipient_name" value="{{ old('recipient_name') }}" placeholder="Ví dụ: Nguyễn Văn A" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                        <input name="phone" value="{{ old('phone') }}" placeholder="Ví dụ: 0912345678" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Địa chỉ chi tiết <span class="text-danger">*</span></label>
                        <input name="address_line" value="{{ old('address_line') }}" placeholder="Số nhà, ngõ ngách, tên đường..." class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Tỉnh/TP <span class="text-danger">*</span></label>
                        <input name="city" value="{{ old('city') }}" placeholder="Tỉnh/TP" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Quận/Huyện <span class="text-danger">*</span></label>
                        <input name="district" value="{{ old('district') }}" placeholder="Quận/Huyện" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Phường/Xã <span class="text-danger">*</span></label>
                        <input name="ward" value="{{ old('ward') }}" placeholder="Phường/Xã" class="form-control" required>
                    </div>
                    <div class="col-12 mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_default" id="new-is-default" value="1" @checked(old('is_default'))>
                            <label class="form-check-label small" for="new-is-default">Đặt làm địa chỉ nhận hàng mặc định</label>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <button type="submit" class="btn btn-success rounded-pill px-4 py-2 fw-semibold">Thêm địa chỉ</button>
                    </div>
                </form>
            </div>
        @else
            <div class="alert alert-info small border-0 rounded-3 mb-0">
                <i class="bi bi-info-circle me-1"></i>Bạn đã lưu tối đa 5 địa chỉ. Hãy xóa bớt nếu cần thêm địa chỉ mới.
            </div>
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

    document.querySelectorAll('form').forEach(function(form) {
        const cityInput = form.querySelector('input[name="city"]');
        const districtInput = form.querySelector('input[name="district"]');
        const wardInput = form.querySelector('input[name="ward"]');

        if (!cityInput) return;

        const provinceSelect = document.createElement('select');
        provinceSelect.name = 'city';
        provinceSelect.className = cityInput.className || 'form-select';

        const districtSelect = document.createElement('select');
        districtSelect.name = 'district';
        districtSelect.className = districtInput?.className || 'form-select';

        const wardSelect = document.createElement('select');
        wardSelect.name = 'ward';
        wardSelect.className = wardInput?.className || 'form-select';

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
