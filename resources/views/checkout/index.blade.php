@extends('layouts.app')
@section('title', 'Thanh toán - SFood')

@section('content')
<div class="bg-body-tertiary py-4 min-vh-100">
    <div class="container py-2">

        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-white p-3 rounded-3 shadow-sm border mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-success"><i class="bi bi-house-door me-1"></i>{{ __('app.home_title') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('cart.index') }}" class="text-decoration-none text-success">Giỏ hàng</a></li>
                <li class="breadcrumb-item active fw-semibold" aria-current="page">Thanh toán đơn hàng</li>
            </ol>
        </nav>

        <form method="POST" action="{{ route('checkout.store') }}">
            @csrf
            <div class="row g-4">
                {{-- LEFT COLUMN: Shipping Address --}}
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                        <h4 class="fw-bold text-dark fs-5 mb-3 d-flex align-items-center">
                            <i class="bi bi-geo-alt-fill text-danger me-2"></i>1. Địa chỉ giao hàng
                        </h4>

                        @if ($addresses->count())
                            <div class="d-flex flex-column gap-2 mb-3">
                                @foreach ($addresses as $addr)
                                    <div class="form-check p-3 border rounded-3 bg-light">
                                        <input class="form-check-input ms-0 me-2" type="radio" name="shipping_address_id" 
                                               id="addr-{{ $addr->id }}"
                                               value="{{ $addr->id }}"
                                               data-city="{{ $addr->city }}"
                                               data-district="{{ $addr->district }}"
                                               data-ward="{{ $addr->ward }}"
                                               data-recipient="{{ $addr->recipient_name }}"
                                               data-phone="{{ $addr->phone }}"
                                               data-address="{{ $addr->address_line }}"
                                               @checked(old('shipping_address_id', $addresses->where('is_default', true)->first()?->id) == $addr->id)>
                                        <label class="form-check-label ps-2 w-100 cursor-pointer" for="addr-{{ $addr->id }}">
                                            <div class="fw-bold text-dark">{{ $addr->recipient_name }} — {{ $addr->phone }}</div>
                                            <div class="small text-muted">{{ $addr->fullAddress() }}</div>
                                        </label>
                                    </div>
                                @endforeach

                                <div class="form-check p-3 border border-dashed rounded-3">
                                    <input class="form-check-input ms-0 me-2" type="radio" name="shipping_address_id" id="addr-new" value="" 
                                           @checked(old('shipping_address_id', $addresses->where('is_default', true)->first()?->id) == '')>
                                    <label class="form-check-label ps-2 cursor-pointer" for="addr-new">
                                        <div class="fw-bold text-dark">Nhập địa chỉ mới</div>
                                        <div class="small text-muted">Sử dụng biểu mẫu bên dưới để nhập địa chỉ giao hàng mới</div>
                                    </label>
                                </div>
                            </div>
                            <div class="text-muted small fw-semibold mb-2">Thông tin địa chỉ chi tiết:</div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Họ tên người nhận <span class="text-danger">*</span></label>
                                <input name="recipient_name" value="{{ old('recipient_name') }}" placeholder="Ví dụ: Nguyễn Văn A" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                                <input name="phone" value="{{ old('phone') }}" placeholder="Ví dụ: 0912345678" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Địa chỉ cụ thể (Số nhà, đường) <span class="text-danger">*</span></label>
                                <input name="address_line" value="{{ old('address_line') }}" placeholder="Ví dụ: 123 Đường 30/4" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Tỉnh / Thành phố <span class="text-danger">*</span></label>
                                <select id="province-select" name="city" class="form-select" required>
                                    <option value="">Chọn Tỉnh/TP</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Quận / Huyện <span class="text-danger">*</span></label>
                                <select id="district-select" name="district" class="form-select" required>
                                    <option value="">Chọn Quận/Huyện</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Phường / Xã <span class="text-danger">*</span></label>
                                <select id="ward-select" name="ward" class="form-select" required>
                                    <option value="">Chọn Phường/Xã</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Ghi chú đơn hàng (Tuỳ chọn)</label>
                                <textarea name="notes" placeholder="Lời dặn cho shipper (ví dụ: giao giờ hành chính, gọi trước khi đến...)" class="form-control" rows="2">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN: Payment & Summary --}}
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white position-sticky" style="top: 90px;">
                        <h4 class="fw-bold text-dark fs-5 mb-3 d-flex align-items-center">
                            <i class="bi bi-receipt-cutoff text-success me-2"></i>2. Thông tin thanh toán
                        </h4>

                        {{-- TOTALS SUMMARY BOX --}}
                        <div class="p-3 bg-light rounded-3 mb-4" id="order-totals">
                            <div class="d-flex justify-content-between text-secondary mb-2">
                                <span>Tạm tính</span>
                                <span class="fw-semibold text-dark" id="subtotal-display">{{ number_format($subtotal) }}đ</span>
                            </div>
                            <div class="d-flex justify-content-between text-secondary mb-2">
                                <span>Phí giao hàng</span>
                                <span class="fw-semibold text-dark" id="shipping-display">{{ $shipping == 0 ? 'Miễn phí' : number_format($shipping) . 'đ' }}</span>
                            </div>
                            <div id="discount-row" class="d-flex justify-content-between text-success mb-2" style="display:none !important">
                                <span>Giảm giá voucher</span>
                                <span class="fw-bold" id="discount-display">0đ</span>
                            </div>
                            <div id="points-discount-row" class="d-flex justify-content-between text-warning-emphasis mb-2" style="display:none !important">
                                <span>Dùng điểm thưởng</span>
                                <span class="fw-bold" id="points-discount-total">0đ</span>
                            </div>
                            @if ($subtotal < 1000000)
                                <div class="small text-muted border-top pt-2 mt-2">
                                    <i class="bi bi-info-circle me-1 text-success"></i>Miễn phí ship nội thành Cần Thơ hoặc đơn từ 1.000.000đ
                                </div>
                            @endif
                            <div class="d-flex justify-content-between align-items-center fw-bold fs-5 text-dark border-top pt-3 mt-2">
                                <span>Tổng thanh toán</span>
                                <span class="fs-4 text-danger fw-black" id="total-display">{{ number_format($total) }}đ</span>
                            </div>
                        </div>

                        {{-- VOUCHER SECTION --}}
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-dark d-flex align-items-center gap-1">
                                <i class="bi bi-ticket-perforated-fill text-danger"></i> Mã giảm giá
                            </label>

                            @if (!empty($availableCoupons) && $availableCoupons->isNotEmpty())
                                <div class="mb-2">
                                    <select id="coupon-select" class="form-select form-select-sm">
                                        <option value="">-- Chọn mã giảm giá có sẵn --</option>
                                        @foreach ($availableCoupons as $c)
                                            @php
                                                $cats = !empty($c->category_names)
                                                    ? implode(', ', array_map('e', $c->category_names))
                                                    : '';
                                            @endphp
                                            <option value="{{ $c->code }}" data-type="{{ $c->type }}"
                                                data-value="{{ $c->value }}" data-min="{{ $c->min_order_amount }}"
                                                data-starts="{{ optional($c->starts_at)->toDateTimeString() }}"
                                                data-expires="{{ optional($c->expires_at)->toDateTimeString() }}"
                                                data-categories="{{ $cats }}">
                                                {{ $c->code }} — @if ($c->type === 'percent')
                                                    Giảm {{ $c->value }}%
                                                @elseif($c->type === 'fixed')
                                                    Giảm {{ number_format($c->value) }}đ
                                                @else
                                                    Miễn phí ship
                                                @endif @if ($c->min_order_amount > 0)
                                                    (Đơn ≥ {{ number_format($c->min_order_amount) }}đ)
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="coupon-details" class="mt-2 small text-muted p-2 bg-light rounded" style="display:none"></div>
                                </div>
                            @endif

                            <div class="input-group">
                                <input id="coupon-code-input" name="coupon_code" value="{{ old('coupon_code') }}"
                                    placeholder="Nhập mã ưu đãi..." class="form-control form-control-sm">
                                <button type="button" id="apply-coupon-btn" class="btn btn-sm btn-outline-success px-3">Áp dụng</button>
                                <button type="button" id="remove-coupon-btn" class="btn btn-sm btn-outline-danger px-3" style="display:none">Bỏ mã</button>
                            </div>
                            <p id="coupon-message" class="text-danger small mt-1 mb-0" style="display:none"></p>
                        </div>

                        {{-- REWARD POINTS SECTION --}}
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-dark d-flex align-items-center gap-1">
                                <i class="bi bi-coin text-warning"></i> Điểm thưởng SFood
                            </label>
                            @if(Auth::user()->reward_points > 0)
                                <div class="form-check form-switch p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fw-semibold text-dark small">Dùng <span class="fw-bold text-success" id="available-points" data-points="{{ Auth::user()->reward_points }}">{{ number_format(Auth::user()->reward_points) }}</span> điểm</div>
                                        <div class="text-muted small">Quy đổi: -<span id="points-discount-preview">0</span>đ</div>
                                    </div>
                                    <input class="form-check-input fs-5 ms-0" type="checkbox" role="switch" id="use-points-toggle" name="use_points" value="1" @checked(old('use_points'))>
                                </div>
                            @else
                                <div class="small text-muted">Bạn hiện có 0 điểm thưởng.</div>
                            @endif
                        </div>

                        {{-- PAYMENT GATEWAY --}}
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-dark d-flex align-items-center gap-1">
                                <i class="bi bi-credit-card-2-front text-primary"></i> Phương thức thanh toán
                            </label>
                            <div class="d-flex flex-column gap-2">
                                @foreach (['cod' => 'COD — Thanh toán tiền mặt khi nhận hàng', 'payos' => 'PayOS — Chuyển khoản QR Ngân hàng tức thì'] as $val => $label)
                                    <div class="form-check p-3 border rounded-3 bg-light">
                                        <input class="form-check-input ms-0 me-2" type="radio" name="payment_gateway" id="pg-{{ $val }}" value="{{ $val }}"
                                               @checked(old('payment_gateway', 'cod') === $val)>
                                        <label class="form-check-label ps-2 cursor-pointer small fw-semibold text-dark" for="pg-{{ $val }}">
                                            {{ $label }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <input type="hidden" name="shipping_fee" id="shipping-fee-input" value="{{ $shipping }}">
                        <button type="submit" class="btn btn-success w-100 py-3 fw-bold rounded-pill shadow-sm fs-5 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-bag-check-fill"></i>
                            <span>Xác nhận Đặt hàng</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const provinceSelect = document.getElementById('province-select');
        const districtSelect = document.getElementById('district-select');
        const wardSelect = document.getElementById('ward-select');

        const shippingDisplay = document.getElementById('shipping-display');
        const totalDisplay = document.getElementById('total-display');

        const oldProvince = {!! json_encode(old('city')) !!} || '';
        const oldDistrict = {!! json_encode(old('district')) !!} || '';
        const oldWard = {!! json_encode(old('ward')) !!} || '';

        const subtotal = {{ $subtotal }};
        const defaultShipping = {{ $shipping }};

        let currentShipping = defaultShipping;

        const API_BASE = 'https://provinces.open-api.vn/api';

        function formatVnd(amount) {
            return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
        }

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

        function updateShippingFee() {
            const province = provinceSelect.selectedOptions[0]?.textContent || provinceSelect.value || '';
            const district = districtSelect.selectedOptions[0]?.textContent || districtSelect.value || '';

            currentShipping = defaultShipping;

            // Free ship nội thành Cần Thơ
            if (
                province.includes('Cần Thơ') &&
                (district.includes('Ninh Kiều') || district.includes('Cái Răng'))
            ) {
                currentShipping = 0;
            }

            shippingDisplay.textContent = currentShipping === 0 ? 'Miễn phí' : formatVnd(currentShipping);
            document.getElementById('shipping-fee-input').value = currentShipping;

            let discount = 0;
            const discountText = document.getElementById('discount-display')?.textContent || '0';
            discount = Number(discountText.replace(/[^\d]/g, '')) || 0;

            let pointsDiscount = 0;
            const usePointsToggle = document.getElementById('use-points-toggle');
            if (usePointsToggle && usePointsToggle.checked) {
                const maxPoints = parseInt(document.getElementById('available-points').dataset.points);
                const totalBeforePoints = subtotal + currentShipping - discount;
                pointsDiscount = Math.min(maxPoints, totalBeforePoints);
                
                document.getElementById('points-discount-preview').textContent = new Intl.NumberFormat('vi-VN').format(pointsDiscount);
                document.getElementById('points-discount-row').style.removeProperty('display');
                document.getElementById('points-discount-total').textContent = '-' + formatVnd(pointsDiscount);
            } else {
                if (document.getElementById('points-discount-preview')) document.getElementById('points-discount-preview').textContent = '0';
                if (document.getElementById('points-discount-row')) document.getElementById('points-discount-row').style.setProperty('display', 'none', 'important');
            }

            const total = subtotal + currentShipping - discount - pointsDiscount;
            totalDisplay.textContent = formatVnd(total);
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
                if (sel) {
                    loadDistricts(sel.dataset.code, true);
                }
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
                if (sel) {
                    loadWards(sel.dataset.code, true);
                    updateShippingFee();
                }
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

            if (setOld && oldWard) {
                wardSelect.value = oldWard;
            }
        }

        provinceSelect.addEventListener('change', function(e) {
            const code = e.target.selectedOptions[0]?.dataset?.code;
            if (code) {
                loadDistricts(code);
            } else {
                districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
                wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
            }
            updateShippingFee();
        });

        districtSelect.addEventListener('change', function(e) {
            const code = e.target.selectedOptions[0]?.dataset?.code;
            if (code) {
                loadWards(code);
            } else {
                wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
            }
            updateShippingFee();
        });

        const usePointsToggleEl = document.getElementById('use-points-toggle');
        if (usePointsToggleEl) {
            usePointsToggleEl.addEventListener('change', updateShippingFee);
        }

        (async function() {
            await loadProvinces();

            function selectOptionByMatch(selectEl, match) {
                if (!match) return false;
                const opts = Array.from(selectEl.options);
                const m = match.toLowerCase();
                let found = opts.find(o => o.dataset.code && o.dataset.code == match);
                if (!found) found = opts.find(o => o.value.toLowerCase().includes(m));
                if (!found) found = opts.find(o => o.textContent.toLowerCase().includes(m));
                if (found) {
                    selectEl.value = found.value;
                    return true;
                }
                return false;
            }

            document.querySelectorAll('input[name="shipping_address_id"]').forEach(function(r) {
                r.addEventListener('change', async function() {
                    const city = this.dataset.city || '';
                    const district = this.dataset.district || '';
                    const ward = this.dataset.ward || '';
                    const recipient = this.dataset.recipient || '';
                    const phone = this.dataset.phone || '';
                    const addressLine = this.dataset.address || '';

                    function findOption(selectEl, match) {
                        if (!match) return null;
                        const opts = Array.from(selectEl.options);
                        const m = match.toLowerCase();
                        let found = opts.find(o => o.dataset.code && o.dataset.code == match);
                        if (!found) found = opts.find(o => o.value.toLowerCase().includes(m));
                        if (!found) found = opts.find(o => o.textContent.toLowerCase().includes(m));
                        return found;
                    }

                    if (city) {
                        const provOpt = findOption(provinceSelect, city);
                        if (provOpt) {
                            provinceSelect.value = provOpt.value;
                            if (provOpt.dataset.code) {
                                await loadDistricts(provOpt.dataset.code);
                            }
                        }
                    }

                    if (district) {
                        const distOpt = findOption(districtSelect, district);
                        if (distOpt) {
                            districtSelect.value = distOpt.value;
                            if (distOpt.dataset.code) {
                                await loadWards(distOpt.dataset.code);
                            }
                        }
                    }

                    if (ward) {
                        const wardOpt = findOption(wardSelect, ward);
                        if (wardOpt) wardSelect.value = wardOpt.value;
                    }

                    const nameInput = document.querySelector('input[name="recipient_name"]');
                    const phoneInput = document.querySelector('input[name="phone"]');
                    const addrInput = document.querySelector('input[name="address_line"]');
                    if (nameInput) nameInput.value = recipient;
                    if (phoneInput) phoneInput.value = phone;
                    if (addrInput) addrInput.value = addressLine;

                    updateShippingFee();
                });
            });

            const manualRadio = Array.from(document.querySelectorAll('input[name="shipping_address_id"]')).find(x => x.value === '');
            if (manualRadio) {
                manualRadio.addEventListener('change', function() {
                    if (this.checked) {
                        const nameInput = document.querySelector('input[name="recipient_name"]');
                        const phoneInput = document.querySelector('input[name="phone"]');
                        const addrInput = document.querySelector('input[name="address_line"]');
                        if (nameInput) nameInput.value = '';
                        if (phoneInput) phoneInput.value = '';
                        if (addrInput) addrInput.value = '';
                        if (provinceSelect) provinceSelect.value = '';
                        if (districtSelect) districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
                        if (wardSelect) wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
                        updateShippingFee();
                    }
                });
            }

            const preChecked = document.querySelector('input[name="shipping_address_id"]:checked');
            if (preChecked) {
                const city = preChecked.dataset.city || '';
                const district = preChecked.dataset.district || '';
                const ward = preChecked.dataset.ward || '';
                const recipient = preChecked.dataset.recipient || '';
                const phone = preChecked.dataset.phone || '';
                const addressLine = preChecked.dataset.address || '';

                if (city) {
                    selectOptionByMatch(provinceSelect, city);
                    const provOpt = Array.from(provinceSelect.options).find(o => o.value === provinceSelect.value);
                    if (provOpt && provOpt.dataset.code) {
                        await loadDistricts(provOpt.dataset.code);
                    }
                }

                if (district) {
                    selectOptionByMatch(districtSelect, district);
                    const distOpt = Array.from(districtSelect.options).find(o => o.value === districtSelect.value);
                    if (distOpt && distOpt.dataset.code) {
                        await loadWards(distOpt.dataset.code);
                    }
                }

                if (ward) selectOptionByMatch(wardSelect, ward);

                const nameInput = document.querySelector('input[name="recipient_name"]');
                const phoneInput = document.querySelector('input[name="phone"]');
                const addrInput = document.querySelector('input[name="address_line"]');
                if (nameInput) nameInput.value = recipient;
                if (phoneInput) phoneInput.value = phone;
                if (addrInput) addrInput.value = addressLine;

                updateShippingFee();
            }
        })();

        // Coupon apply/remove logic
        const applyBtn = document.getElementById('apply-coupon-btn');
        const removeBtn = document.getElementById('remove-coupon-btn');
        const couponInput = document.getElementById('coupon-code-input');
        const couponMessage = document.getElementById('coupon-message');

        const appliedCoupon = {!! json_encode($appliedCoupon ?? '') !!};
        if (appliedCoupon && couponInput) {
            couponInput.value = appliedCoupon;
            setTimeout(() => applyBtn?.click(), 200);
        }

        applyBtn?.addEventListener('click', async function() {
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
                    body: JSON.stringify({
                        code,
                        shipping_fee: currentShipping
                    }),
                });

                const json = await res.json();
                if (!res.ok) {
                    couponMessage.style.display = 'block';
                    couponMessage.textContent = json.message || 'Mã không hợp lệ';
                    return;
                }

                document.getElementById('discount-display').textContent = formatVnd(json.discount);
                
                let finalTotal = json.total;
                let pointsDiscount = 0;
                const usePointsToggle = document.getElementById('use-points-toggle');
                if (usePointsToggle && usePointsToggle.checked) {
                    const maxPoints = parseInt(document.getElementById('available-points').dataset.points);
                    pointsDiscount = Math.min(maxPoints, json.total);
                    document.getElementById('points-discount-preview').textContent = new Intl.NumberFormat('vi-VN').format(pointsDiscount);
                    document.getElementById('points-discount-row').style.removeProperty('display');
                    document.getElementById('points-discount-total').textContent = '-' + formatVnd(pointsDiscount);
                    finalTotal -= pointsDiscount;
                }
                
                totalDisplay.textContent = formatVnd(finalTotal);
                document.getElementById('subtotal-display').textContent = formatVnd({{ $subtotal }});
                if (json.shipping === 0) {
                    shippingDisplay.textContent = 'Miễn phí';
                } else {
                    shippingDisplay.textContent = formatVnd(json.shipping);
                }
                document.getElementById('shipping-fee-input').value = json.shipping;

                document.getElementById('discount-row').style.removeProperty('display');
                removeBtn.style.display = 'inline-block';
                applyBtn.style.display = 'none';
            } catch (e) {
                couponMessage.style.display = 'block';
                couponMessage.textContent = 'Lỗi khi kiểm tra mã';
            }
        });

        removeBtn?.addEventListener('click', function() {
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
            document.getElementById('discount-row').style.setProperty('display', 'none', 'important');
            removeBtn.style.display = 'none';
            applyBtn.style.display = 'inline-block';
            couponMessage.style.display = 'none';

            updateShippingFee();
        });

        const couponSelect = document.getElementById('coupon-select');
        couponSelect?.addEventListener('change', function(ev) {
            const code = ev.target.value;
            if (!code) return;
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
            couponInput.value = code;
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            (async function() {
                try {
                    const res = await fetch('{{ route('coupon.validate') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            code,
                            shipping_fee: currentShipping
                        }),
                    });
                    const json = await res.json();
                    if (!res.ok) {
                        couponMessage.style.display = 'block';
                        couponMessage.textContent = json.message || 'Mã không hợp lệ';
                        return;
                    }
                    document.getElementById('discount-display').textContent = formatVnd(json.discount);
                    
                    let finalTotal = json.total;
                    let pointsDiscount = 0;
                    const usePointsToggle = document.getElementById('use-points-toggle');
                    if (usePointsToggle && usePointsToggle.checked) {
                        const maxPoints = parseInt(document.getElementById('available-points').dataset.points);
                        pointsDiscount = Math.min(maxPoints, json.total);
                        document.getElementById('points-discount-preview').textContent = new Intl.NumberFormat('vi-VN').format(pointsDiscount);
                        document.getElementById('points-discount-row').style.removeProperty('display');
                        document.getElementById('points-discount-total').textContent = '-' + formatVnd(pointsDiscount);
                        finalTotal -= pointsDiscount;
                    }
                    
                    totalDisplay.textContent = formatVnd(finalTotal);
                    if (json.shipping === 0) {
                        shippingDisplay.textContent = 'Miễn phí';
                    } else {
                        shippingDisplay.textContent = formatVnd(json.shipping);
                    }
                    document.getElementById('shipping-fee-input').value = json.shipping;

                    document.getElementById('discount-row').style.removeProperty('display');
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
