@extends('layouts.app')
@section('title', 'Giới thiệu - SFood')
@section('content')
<div class="bg-earth-800 text-cream-50 py-16">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold mb-4">Về SFood</h1>
        <p class="text-cream-300 max-w-2xl mx-auto">Hành trình mang thực phẩm tươi sạch đến từng căn bếp Việt, kết hợp công nghệ AI để mua sắm thông minh hơn.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-14">
    <div class="grid md:grid-cols-2 gap-12 items-center mb-16">
        <div>
            <h2 class="section-title mb-4">Sứ mệnh của chúng tôi</h2>
            <p class="text-earth-600 leading-relaxed mb-4">
                SFood ra đời với mong muốn giúp mọi gia đình Việt dễ dàng tiếp cận thực phẩm tươi sạch, nguồn gốc minh bạch. Chúng tôi kết nối trực tiếp với nông trại và nhà cung cấp uy tín, đảm bảo sản phẩm đến tay bạn trong vòng 2 giờ.
            </p>
            <p class="text-earth-600 leading-relaxed">
                Với công nghệ AI nhận diện hình ảnh và tìm kiếm giọng nói, bạn có thể tìm đúng sản phẩm chỉ bằng một bức ảnh hoặc câu nói — tiện lợi như chưa từng có.
            </p>
        </div>
        <div class="grid grid-cols-2 gap-4">
            @foreach ([['500+', 'Sản phẩm'], ['10K+', 'Khách hàng'], ['2h', 'Giao hàng'], ['4.8★', 'Đánh giá']] as $stat)
                <div class="card p-6 text-center">
                    <p class="text-3xl font-bold text-leaf-700">{{ $stat[0] }}</p>
                    <p class="text-sm text-earth-500 mt-1">{{ $stat[1] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <h2 class="section-title text-center mb-10">Tại sao chọn SFood?</h2>
    <div class="grid md:grid-cols-3 gap-6">
        @foreach ([
            ['🌱', 'Nguồn gốc rõ ràng', 'Mỗi sản phẩm đều có thông tin xuất xứ, ngày thu hoạch và chứng nhận chất lượng.'],
            ['🤖', 'Công nghệ AI', 'Tìm kiếm bằng hình ảnh CLIP, giọng nói tiếng Việt và gợi ý món ăn thông minh.'],
            ['⚡', 'Mua nhanh nguyên liệu', 'Xem video nấu ăn, thêm toàn bộ nguyên liệu vào giỏ chỉ với một nút bấm.'],
        ] as $item)
            <div class="card p-6">
                <span class="text-4xl mb-4 block">{{ $item[0] }}</span>
                <h3 class="font-bold text-earth-800 text-lg mb-2">{{ $item[1] }}</h3>
                <p class="text-earth-600 text-sm leading-relaxed">{{ $item[2] }}</p>
            </div>
        @endforeach
    </div>
</div>
@endsection
