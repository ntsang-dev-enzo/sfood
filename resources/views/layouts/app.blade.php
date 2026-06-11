<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SFood - Thực phẩm tươi sống')</title>
    <meta name="description" content="SFood - Cửa hàng thực phẩm tươi sống, giao hàng nhanh, AI tìm kiếm thông minh">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=be-vietnam-pro:400,500,600,700" rel="stylesheet" />
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-cream-100 text-earth-800 font-sans antialiased min-h-screen flex flex-col">
    {{-- Top bar --}}
    <div class="bg-earth-800 text-cream-200 text-xs hidden sm:block">
        <div class="max-w-7xl mx-auto px-4 py-2 flex justify-between items-center">
            <span>🚚 Miễn phí giao hàng đơn từ 300.000đ</span>
            <div class="flex gap-4">
                <a href="{{ route('about') }}" class="hover:text-white transition">Giới thiệu</a>
                <a href="{{ route('contact') }}" class="hover:text-white transition">Liên hệ</a>
                <a href="{{ route('faq') }}" class="hover:text-white transition">Hỏi đáp</a>
            </div>
        </div>
    </div>

    {{-- Header --}}
    <header class="bg-cream-50 border-b border-cream-300 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center gap-4 py-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2 flex-shrink-0">
                    <span class="w-10 h-10 rounded-xl bg-leaf-600 flex items-center justify-center text-white text-xl font-bold">S</span>
                    <div class="hidden sm:block">
                        <p class="font-bold text-lg text-earth-800 leading-tight">SFood</p>
                        <p class="text-[10px] text-earth-500 uppercase tracking-widest">Fresh & Organic</p>
                    </div>
                </a>

                <div class="flex-1 max-w-xl hidden md:block">
                    @include('partials.search-box', ['size' => 'md'])
                </div>

                <div class="flex items-center gap-2 ml-auto">
                    {{-- Language Switcher --}}
                    @if(session('locale', 'vi') === 'en')
                        <a href="{{ route('lang.switch', 'vi') }}" class="p-2 rounded-xl hover:bg-cream-200 transition text-lg" title="Tiếng Việt">🇻🇳</a>
                    @else
                        <a href="{{ route('lang.switch', 'en') }}" class="p-2 rounded-xl hover:bg-cream-200 transition text-lg" title="English">🇺🇸</a>
                    @endif

                    {{-- Dark Mode Toggle --}}
                    <button type="button" id="theme-toggle" class="p-2.5 rounded-xl hover:bg-cream-200 transition text-earth-700" title="Đổi giao diện">
                        {{-- Sun icon (hidden in light mode, shown in dark mode) --}}
                        <svg id="theme-toggle-light-icon" class="hidden w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"/>
                        </svg>
                        {{-- Moon icon (shown in light mode, hidden in dark mode) --}}
                        <svg id="theme-toggle-dark-icon" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                        </svg>
                    </button>

                    <a href="{{ route('cart') }}" class="relative p-2.5 rounded-xl hover:bg-cream-200 transition text-earth-700" title="Giỏ hàng">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span data-cart-count class="absolute -top-0.5 -right-0.5 bg-earth-600 text-cream-50 text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-bold {{ ($cartCount ?? 0) > 0 ? '' : 'hidden' }}">{{ $cartCount ?? 0 }}</span>
                    </a>

                    @auth
                        <a href="{{ route('profile') }}" class="hidden sm:flex items-center gap-2 p-1.5 rounded-xl hover:bg-cream-200 transition" title="Tài khoản">
                            <img src="{{ \App\Support\MediaHelper::avatar(auth()->user()->avatar, auth()->user()->name) }}" class="w-8 h-8 rounded-lg object-cover" alt="">
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                            @csrf
                            <button class="text-sm text-earth-500 hover:text-earth-800 px-2 py-1">Thoát</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn-primary !px-4 !py-2.5 !text-sm hidden sm:inline-flex">Đăng nhập</a>
                    @endauth

                    <button type="button" id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-cream-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Mobile search --}}
            <div class="md:hidden pb-4">
                @include('partials.search-box', ['size' => 'md'])
            </div>

            {{-- Nav --}}
            <nav class="hidden md:flex items-center gap-1 pb-3 border-t border-cream-200 pt-3 -mt-1">
                <a href="{{ route('home') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('home') ? 'bg-leaf-600 text-white' : 'text-earth-700 hover:bg-cream-200' }} transition">{{ __('nav.home') }}</a>
                <a href="{{ route('products.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('products.*') ? 'bg-leaf-600 text-white' : 'text-earth-700 hover:bg-cream-200' }} transition">{{ __('nav.products') }}</a>
                <a href="{{ route('recipes.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('recipes.*') ? 'bg-leaf-600 text-white' : 'text-earth-700 hover:bg-cream-200' }} transition">{{ __('nav.recipes') }}</a>
                <a href="{{ route('about') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('about') ? 'bg-leaf-600 text-white' : 'text-earth-700 hover:bg-cream-200' }} transition">{{ __('nav.about') }}</a>
                <a href="{{ route('contact') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('contact') ? 'bg-leaf-600 text-white' : 'text-earth-700 hover:bg-cream-200' }} transition">{{ __('nav.contact') }}</a>
                @auth
                    <a href="{{ route('orders.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('orders.*') ? 'bg-leaf-600 text-white' : 'text-earth-700 hover:bg-cream-200' }} transition">{{ __('nav.orders') }}</a>
                @endauth
            </nav>
        </div>

        {{-- Mobile menu --}}
        <div id="mobile-menu" class="hidden md:hidden border-t border-cream-300 bg-cream-50 px-4 py-3 space-y-1">
            <a href="{{ route('home') }}" class="block px-3 py-2 rounded-lg hover:bg-cream-200">{{ __('nav.home') }}</a>
            <a href="{{ route('products.index') }}" class="block px-3 py-2 rounded-lg hover:bg-cream-200">{{ __('nav.products') }}</a>
            <a href="{{ route('recipes.index') }}" class="block px-3 py-2 rounded-lg hover:bg-cream-200">{{ __('nav.recipes') }}</a>
            <a href="{{ route('about') }}" class="block px-3 py-2 rounded-lg hover:bg-cream-200">{{ __('nav.about') }}</a>
            <a href="{{ route('contact') }}" class="block px-3 py-2 rounded-lg hover:bg-cream-200">{{ __('nav.contact') }}</a>
            @auth
                <a href="{{ route('profile') }}" class="block px-3 py-2 rounded-lg hover:bg-cream-200">{{ __('nav.account') }}</a>
                <a href="{{ route('orders.index') }}" class="block px-3 py-2 rounded-lg hover:bg-cream-200">{{ __('nav.orders') }}</a>
            @else
                <a href="{{ route('login') }}" class="block px-3 py-2 rounded-lg bg-leaf-600 text-white text-center mt-2">{{ __('nav.login') }}</a>
            @endauth
        </div>
    </header>

    @if (session('success'))
        <div class="max-w-7xl mx-auto px-4 mt-4 w-full">
            <div class="bg-leaf-100 border border-leaf-500/30 text-leaf-800 px-4 py-3 rounded-xl flex items-center gap-2">
                <span>✓</span> {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="max-w-7xl mx-auto px-4 mt-4 w-full">
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">{{ session('error') }}</div>
        </div>
    @endif

    <main class="flex-1">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-earth-800 text-cream-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-10 h-10 rounded-xl bg-leaf-600 flex items-center justify-center text-white font-bold">S</span>
                        <span class="font-bold text-xl text-cream-50">SFood</span>
                    </div>
                    <p class="text-sm text-cream-300 leading-relaxed">Thực phẩm tươi sạch, nguồn gốc rõ ràng. Giao hàng nhanh trong ngày tại TP.HCM và Hà Nội.</p>
                </div>
                <div>
                    <h4 class="font-semibold text-cream-50 mb-4">Danh mục</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('products.index') }}" class="hover:text-leaf-400 transition">Tất cả sản phẩm</a></li>
                        <li><a href="{{ route('recipes.index') }}" class="hover:text-leaf-400 transition">Công thức nấu ăn</a></li>
                        <li><a href="{{ route('products.index', ['category' => 'rau-cu']) }}" class="hover:text-leaf-400 transition">Rau củ</a></li>
                        <li><a href="{{ route('products.index', ['category' => 'thit-ca']) }}" class="hover:text-leaf-400 transition">Thịt cá</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-cream-50 mb-4">Hỗ trợ</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('about') }}" class="hover:text-leaf-400 transition">Giới thiệu</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-leaf-400 transition">Liên hệ</a></li>
                        <li><a href="{{ route('faq') }}" class="hover:text-leaf-400 transition">Hỏi đáp</a></li>
                        <li><a href="{{ route('policy') }}" class="hover:text-leaf-400 transition">Chính sách</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-cream-50 mb-4">Liên hệ</h4>
                    <ul class="space-y-2 text-sm text-cream-300">
                        <li>📞 1900 1234</li>
                        <li>✉️ support@sfood.vn</li>
                        <li>📍 123 Đường Xanh, Q.1, TP.HCM</li>
                        <li class="pt-2 flex gap-3">
                            <span class="w-8 h-8 rounded-full bg-earth-700 flex items-center justify-center text-xs">FB</span>
                            <span class="w-8 h-8 rounded-full bg-earth-700 flex items-center justify-center text-xs">IG</span>
                            <span class="w-8 h-8 rounded-full bg-earth-700 flex items-center justify-center text-xs">YT</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-earth-700 mt-10 pt-6 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm text-cream-400">
                <p>© {{ date('Y') }} SFood. Tất cả quyền được bảo lưu.</p>
                <p>Thiết kế cho trải nghiệm mua sắm tươi mỗi ngày 🌿</p>
            </div>
        </div>
    </footer>
</body>
<script>
    var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
    var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        themeToggleLightIcon.classList.remove('hidden');
        themeToggleDarkIcon.classList.add('hidden');
    } else {
        themeToggleDarkIcon.classList.remove('hidden');
        themeToggleLightIcon.classList.add('hidden');
    }

    var themeToggleBtn = document.getElementById('theme-toggle');

    themeToggleBtn.addEventListener('click', function() {
        themeToggleDarkIcon.classList.toggle('hidden');
        themeToggleLightIcon.classList.toggle('hidden');

        if (localStorage.getItem('theme')) {
            if (localStorage.getItem('theme') === 'light') {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            }
        } else {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }
    });
</script>
@stack('scripts')
</html>
