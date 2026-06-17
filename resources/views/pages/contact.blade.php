@extends('layouts.app')
@section('title', 'Liên hệ - SFood')
@section('content')
<div class="bg-earth-800 text-cream-50 py-16">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold mb-4">Liên hệ</h1>
        <p class="text-cream-300">Chúng tôi luôn sẵn sàng hỗ trợ bạn</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-14">
    <div class="grid lg:grid-cols-2 gap-10">
        <div>
            <h2 class="section-title mb-6">Thông tin liên hệ</h2>
            <div class="space-y-5">
                @foreach ([
                    ['📍', 'Địa chỉ', ' Đường Nguyễn Văn Cừ nối dài, Q.Ninh Kiều, TP. Cần Thơ'],
                    ['📞', 'Hotline', '1900 1234 (7:00 - 22:00)'],
                    ['✉️', 'Email', 'support@sfood.vn'],
                    ['💬', 'Zalo / Facebook', '@sfood.vn'],
                ] as $info)
                    <div class="flex gap-4 p-4 bg-cream-50 rounded-xl border border-cream-300">
                        <span class="text-2xl">{{ $info[0] }}</span>
                        <div>
                            <p class="font-semibold text-earth-800">{{ $info[1] }}</p>
                            <p class="text-earth-600 text-sm">{{ $info[2] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card p-8">
            <h2 class="font-bold text-xl text-earth-800 mb-6">Gửi tin nhắn</h2>
            <form method="POST" action="{{ route('contact.send') }}" class="space-y-4">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-earth-700 mb-1 block">Họ tên *</label>
                        <input name="name" value="{{ old('name') }}" required class="input-field">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-earth-700 mb-1 block">Email *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="input-field">
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-earth-700 mb-1 block">Số điện thoại</label>
                    <input name="phone" value="{{ old('phone') }}" class="input-field">
                </div>
                <div>
                    <label class="text-sm font-medium text-earth-700 mb-1 block">Chủ đề *</label>
                    <input name="subject" value="{{ old('subject') }}" required class="input-field">
                </div>
                <div>
                    <label class="text-sm font-medium text-earth-700 mb-1 block">Nội dung *</label>
                    <textarea name="message" rows="5" required class="input-field">{{ old('message') }}</textarea>
                </div>
                <button type="submit" class="btn-primary w-full">Gửi liên hệ</button>
            </form>
        </div>
    </div>
</div>
@endsection
