@extends('layouts.app')
@section('title', 'Thanh toán - SFood')
@section('content')
<div class="bg-earth-800 text-cream-50 py-12">
    <div class="max-w-4xl mx-auto px-4">
        <h1 class="text-3xl font-bold">Thanh toán</h1>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 py-8">
    <form method="POST" action="{{ route('checkout.store') }}">
        @csrf
        <div class="grid md:grid-cols-2 gap-6">
            <div class="card p-6">
                <h2 class="font-bold text-lg text-earth-800 mb-4">📍 Địa chỉ giao hàng</h2>

                @if ($addresses->count())
                    <div class="space-y-2 mb-4">
                        @foreach ($addresses as $addr)
                            <label class="flex items-start gap-3 p-4 border border-cream-300 rounded-xl cursor-pointer hover:border-leaf-500 transition has-[:checked]:border-leaf-600 has-[:checked]:bg-leaf-50">
                                <input type="radio" name="shipping_address_id" value="{{ $addr->id }}" class="mt-1 accent-leaf-600"
                                    @checked(old('shipping_address_id', $addresses->where('is_default', true)->first()?->id) == $addr->id)>
                                <div>
                                    <p class="font-medium text-earth-800">{{ $addr->recipient_name }} — {{ $addr->phone }}</p>
                                    <p class="text-sm text-earth-500">{{ $addr->fullAddress() }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-sm text-earth-500 mb-3">Hoặc nhập địa chỉ mới:</p>
                @endif

                <div class="space-y-3">
                    <input name="recipient_name" value="{{ old('recipient_name') }}" placeholder="Họ tên người nhận" class="input-field">
                    <input name="phone" value="{{ old('phone') }}" placeholder="Số điện thoại" class="input-field">
                    <input name="address_line" value="{{ old('address_line') }}" placeholder="Địa chỉ" class="input-field">
                    <div class="grid grid-cols-3 gap-2">
                        <select id="province-select" name="city" class="input-field">
                            <option value="">Chọn Tỉnh/TP</option>
                        </select>

                        <select id="district-select" name="district" class="input-field">
                            <option value="">Chọn Quận/Huyện</option>
                        </select>

                        <select id="ward-select" name="ward" class="input-field">
                            <option value="">Chọn Phường/Xã</option>
                        </select>
                    </div>
                    <textarea name="notes" placeholder="Ghi chú đơn hàng" class="input-field" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="card p-6">
                <h2 class="font-bold text-lg text-earth-800 mb-4">💳 Thanh toán</h2>

                <div class="space-y-2 text-sm mb-5 p-4 bg-cream-100 rounded-xl" id="order-totals">
                    <div class="flex justify-between text-earth-700"><span>Tạm tính</span><span id="subtotal-display">{{ number_format($subtotal) }}đ</span></div>
                    <div class="flex justify-between text-earth-700"><span>Phí giao hàng</span><span id="shipping-display">{{ $shipping == 0 ? 'Miễn phí' : number_format($shipping).'đ' }}</span></div>
                    <div id="discount-row" class="flex justify-between text-earth-700" style="display:none"><span>Giảm</span><span id="discount-display">0đ</span></div>
                    @if ($subtotal < 300000)
                        <p class="text-xs text-earth-500">Miễn phí ship đơn từ 300.000đ</p>
                    @endif
                    <div class="flex justify-between font-bold text-lg text-earth-800 pt-2 border-t border-cream-300">
                        <span>Tổng</span><span class="text-leaf-700" id="total-display">{{ number_format($total) }}đ</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-sm font-medium text-earth-700">Mã giảm giá</label>

                    @if (!empty($availableCoupons) && $availableCoupons->isNotEmpty())
                        <div class="mt-2">
                            <select id="coupon-select" class="input-field">
                                <option value="">Chọn mã giảm giá</option>
                                @foreach ($availableCoupons as $c)
                                    @php
                                        $cats = !empty($c->category_names) ? implode(', ', array_map('e', $c->category_names)) : '';
                                    @endphp
                                    <option value="{{ $c->code }}"
                                        data-type="{{ $c->type }}"
                                        data-value="{{ $c->value }}"
                                        data-min="{{ $c->min_order_amount }}"
                                        data-starts="{{ optional($c->starts_at)->toDateTimeString() }}"
                                        data-expires="{{ optional($c->expires_at)->toDateTimeString() }}"
                                        data-categories="{{ $cats }}"
                                    >
                                        {{ $c->code }} — @if($c->type==='percent') Giảm {{ $c->value }}% @elseif($c->type==='fixed') Giảm {{ number_format($c->value) }}đ @else Miễn phí ship @endif @if($c->min_order_amount>0) (Đơn ≥ {{ number_format($c->min_order_amount) }}đ) @endif
                                    </option>
                                @endforeach
                            </select>
                            <div id="coupon-details" class="mt-2 text-sm text-earth-600" style="display:none"></div>
                        </div>
                    @else
                        <div class="mt-2">
                            <p class="text-sm text-earth-500">Không có mã khả dụng cho giỏ hàng của bạn.</p>
                        </div>
                    @endif

                    <div class="flex gap-2 mt-3">
                        <input id="coupon-code-input" name="coupon_code" value="{{ old('coupon_code') }}" placeholder="Nhập mã khác (tuỳ chọn)" class="input-field">
                        <button type="button" id="apply-coupon-btn" class="btn-secondary" style="display:none">Áp dụng</button>
                        <button type="button" id="remove-coupon-btn" class="btn-ghost" style="display:none">Bỏ</button>
                    </div>
                    <p id="coupon-message" class="text-sm text-red-600 mt-2" style="display:none"></p>
                </div>

                <div class="space-y-2 mb-6">
                    <label class="text-sm font-medium text-earth-700">Phương thức</label>
                    @foreach (['cod' => 'COD — Thanh toán khi nhận', 'payos' => 'PayOS (QR / Ngân hàng)', 'vnpay' => 'VNPay', 'momo' => 'MoMo'] as $val => $label)
                        <label class="flex items-center gap-2 p-3 border border-cream-300 rounded-xl cursor-pointer hover:border-leaf-500 has-[:checked]:border-leaf-600 has-[:checked]:bg-leaf-50">
                            <input type="radio" name="payment_gateway" value="{{ $val }}" class="accent-leaf-600" @checked(old('payment_gateway', 'cod') === $val)>
                            <span class="text-sm text-earth-800">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                <button type="submit" class="btn-primary w-full text-lg">Đặt hàng ngay</button>
            </div>
        </div>
    </form>
</div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const provinceSelect = document.getElementById('province-select');
            const districtSelect = document.getElementById('district-select');
            const wardSelect = document.getElementById('ward-select');

            const oldProvince = {!! json_encode(old('city')) !!} || '';
            const oldDistrict = {!! json_encode(old('district')) !!} || '';
            const oldWard = {!! json_encode(old('ward')) !!} || '';

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

            async function loadProvinces() {
                const data = await fetchJSON(`${API_BASE}/p/`);
                if (!data) return;
                provinceSelect.innerHTML = '<option value="">Chọn Tỉnh/TP</option>';
                data.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.name;
                    opt.dataset.code = p.code;
                    opt.textContent = p.name;
                    provinceSelect.appendChild(opt);
                });
                if (oldProvince) {
                    provinceSelect.value = oldProvince;
                    const sel = Array.from(provinceSelect.options).find(o => o.value === oldProvince);
                    if (sel) loadDistricts(sel.dataset.code, true);
                }
            }

            async function loadDistricts(provinceCode, setOld = false) {
                districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
                wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
                const data = await fetchJSON(`${API_BASE}/p/${provinceCode}?depth=2`);
                if (!data || !data.districts) return;
                data.districts.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.name;
                    opt.dataset.code = d.code;
                    opt.textContent = d.name;
                    districtSelect.appendChild(opt);
                });
                if (setOld && oldDistrict) {
                    districtSelect.value = oldDistrict;
                    const sel = Array.from(districtSelect.options).find(o => o.value === oldDistrict);
                    if (sel) loadWards(sel.dataset.code, true);
                }
            }

            async function loadWards(districtCode, setOld = false) {
                wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
                const data = await fetchJSON(`${API_BASE}/d/${districtCode}?depth=2`);
                if (!data || !data.wards) return;
                data.wards.forEach(w => {
                    const opt = document.createElement('option');
                    opt.value = w.name;
                    opt.textContent = w.name;
                    wardSelect.appendChild(opt);
                });
                if (setOld && oldWard) wardSelect.value = oldWard;
            }

            provinceSelect.addEventListener('change', function (e) {
                const code = e.target.selectedOptions[0]?.dataset?.code;
                if (code) loadDistricts(code);
                else {
                    districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
                    wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
                }
            });

            districtSelect.addEventListener('change', function (e) {
                const code = e.target.selectedOptions[0]?.dataset?.code;
                if (code) loadWards(code);
                else wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
            });

            loadProvinces();

            // auto-apply coupon from session if present
            const appliedCoupon = {!! json_encode($appliedCoupon ?? '') !!};
            if (appliedCoupon) {
                couponInput.value = appliedCoupon;
                // small delay to ensure handlers attached
                setTimeout(() => applyBtn?.click(), 200);
            }

                // Coupon apply/remove logic
                const applyBtn = document.getElementById('apply-coupon-btn');
                const removeBtn = document.getElementById('remove-coupon-btn');
                const couponInput = document.getElementById('coupon-code-input');
                const couponMessage = document.getElementById('coupon-message');

                function formatVnd(amount) {
                    return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
                }

                    applyBtn?.addEventListener('click', async function () {
                    const code = couponInput.value.trim();
                    if (!code) {
                        couponMessage.style.display = 'block';
                        couponMessage.textContent = 'Vui lòng nhập mã.';
                        return;
                    }

                    couponMessage.style.display = 'none';

                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    try {
                        const res = await fetch('{{ route('coupon.validate') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ code }),
                        });

                        const json = await res.json();
                        if (! res.ok) {
                            couponMessage.style.display = 'block';
                            couponMessage.textContent = json.message || 'Mã không hợp lệ';
                            return;
                        }

                        // update UI
                        document.getElementById('discount-display').textContent = formatVnd(json.discount);
                        document.getElementById('total-display').textContent = formatVnd(json.total);
                        document.getElementById('subtotal-display').textContent = formatVnd({{ $subtotal }});
                        if (json.shipping === 0) document.getElementById('shipping-display').textContent = 'Miễn phí';
                        else document.getElementById('shipping-display').textContent = formatVnd(json.shipping);

                        document.getElementById('discount-row').style.display = 'flex';
                        removeBtn.style.display = 'inline-block';
                        applyBtn.style.display = 'none';
                    } catch (e) {
                        couponMessage.style.display = 'block';
                        couponMessage.textContent = 'Lỗi khi kiểm tra mã';
                    }
                });

                removeBtn?.addEventListener('click', function () {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    fetch('{{ route('coupon.remove') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                        },
                    }).catch(() => {});

                    couponInput.value = '';
                    document.getElementById('discount-display').textContent = '0đ';
                    document.getElementById('total-display').textContent = formatVnd({{ $total }});
                    document.getElementById('shipping-display').textContent = '{{ $shipping == 0 ? 'Miễn phí' : number_format($shipping)."đ" }}';
                    document.getElementById('discount-row').style.display = 'none';
                    removeBtn.style.display = 'none';
                    applyBtn.style.display = 'inline-block';
                    couponMessage.style.display = 'none';
                });

                // auto-apply when selecting from the coupon select
                const couponSelect = document.getElementById('coupon-select');
                couponSelect?.addEventListener('change', function (ev) {
                    const code = ev.target.value;
                    if (!code) return;
                    // render coupon details
                    const opt = ev.target.selectedOptions[0];
                    if (opt) {
                        const type = opt.dataset.type || '';
                        const value = opt.dataset.value || '';
                        const min = opt.dataset.min || '';
                        const starts = opt.dataset.starts || '';
                        const expires = opt.dataset.expires || '';
                        const cats = opt.dataset.categories || '';

                        let lines = [];
                        if (type === 'percent') lines.push(`Giảm ${value}%`);
                        else if (type === 'fixed') lines.push(`Giảm ${new Intl.NumberFormat('vi-VN').format(value)}đ`);
                        else if (type === 'free_shipping') lines.push('Miễn phí ship');
                        if (min && Number(min) > 0) lines.push(`Yêu cầu đơn tối thiểu ${new Intl.NumberFormat('vi-VN').format(min)}đ`);
                        if (cats) lines.push(`Áp dụng cho: ${cats}`);
                        if (starts) lines.push(`Bắt đầu: ${starts}`);
                        if (expires) lines.push(`Hạn dùng: ${expires}`);

                        const detailEl = document.getElementById('coupon-details');
                        detailEl.innerHTML = lines.map(l => `<div>${l}</div>`).join('');
                        detailEl.style.display = 'block';
                    }
                    // directly call validation/apply flow
                    couponInput.value = code;
                    // reuse apply logic but call fetch directly to avoid clicking hidden button
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    (async function () {
                        try {
                            const res = await fetch('{{ route('coupon.validate') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({ code }),
                            });
                            const json = await res.json();
                            if (!res.ok) {
                                couponMessage.style.display = 'block';
                                couponMessage.textContent = json.message || 'Mã không hợp lệ';
                                return;
                            }

                            document.getElementById('discount-display').textContent = formatVnd(json.discount);
                            document.getElementById('total-display').textContent = formatVnd(json.total);
                            if (json.shipping === 0) document.getElementById('shipping-display').textContent = 'Miễn phí';
                            else document.getElementById('shipping-display').textContent = formatVnd(json.shipping);

                            document.getElementById('discount-row').style.display = 'flex';
                            removeBtn.style.display = 'inline-block';
                            applyBtn.style.display = 'none';
                            couponMessage.style.display = 'none';
                        } catch (e) {
                            couponMessage.style.display = 'block';
                            couponMessage.textContent = 'Lỗi khi kiểm tra mã';
                        }
                    })();
                });
        });
    </script>
@endsection
