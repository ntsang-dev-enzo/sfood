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

<body class="bg-cream-100 dark:bg-[#121212] text-earth-800 dark:text-gray-300 font-sans antialiased min-h-screen flex flex-col">
    {{-- Top bar --}}
    <div class="bg-earth-800 text-cream-200 text-[11px] sm:text-xs">
        <div class="max-w-7xl mx-auto px-4 py-1.5 flex justify-between items-center">
            <span class="truncate">{{ __('app.free_shipping') }}<span class="sm:hidden">{{ __('app.freeship_short') }}</span></span>
            <div class="flex items-center gap-3 sm:gap-4">
                @auth
                    <a href="{{ route('favorites.index') }}" class="hover:text-white transition hidden sm:inline">Yêu thích</a>
                    <a href="{{ route('orders.index') }}" class="hover:text-white transition hidden sm:inline">{{ __('nav.orders') }}</a>
                @endauth
                
                <div class="flex items-center gap-2 sm:border-l sm:border-earth-600 sm:pl-3 pl-1">
                    {{-- Language Switcher (Small) --}}
                    @if(session('locale', 'vi') === 'en')
                        <a href="{{ route('lang.switch', 'vi') }}" class="hover:scale-110 transition text-sm leading-none" title="Tiếng Việt">🇻🇳</a>
                    @else
                        <a href="{{ route('lang.switch', 'en') }}" class="hover:scale-110 transition text-sm leading-none" title="English">🇺🇸</a>
                    @endif

                    {{-- Dark Mode Toggle (Small) --}}
                    <button type="button" id="theme-toggle" class="hover:text-white transition text-cream-200 ml-0.5" title="Đổi giao diện">
                        <svg id="theme-toggle-light-icon" class="hidden w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"/>
                        </svg>
                        <svg id="theme-toggle-dark-icon" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Header --}}
    <header class="bg-cream-50 dark:bg-[#1e1e1e] border-b border-cream-300 dark:border-gray-800 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center gap-4 py-4">
                <a href="{{ route('home') }}" class="flex items-center gap-3 flex-shrink-0">
                    <img src="{{ asset('/logo.png') }}" alt="SFood Logo" class="w-auto h-12 rounded-xl object-contain shadow-sm border border-cream-200 dark:border-gray-700 bg-white">
                    <div class="hidden sm:block">
                        <p class="font-bold text-xl text-earth-800 dark:text-white leading-tight">SFood</p>
                        <p class="text-[10px] text-leaf-600 dark:text-leaf-400 uppercase tracking-widest font-semibold">Fresh & Organic</p>
                    </div>
                </a>

                <div class="flex-1 max-w-xl hidden md:block">
                    @include('partials.search-box', ['size' => 'md'])
                </div>

                <div class="flex items-center gap-2 ml-auto">



                    @auth
                        <livewire:notification-bell />
                    @endauth
                    <div class="relative" id="cart-dropdown-wrapper">
                        <button type="button" id="cart-dropdown-toggle"
                            class="relative px-4 py-2.5 rounded-2xl bg-leaf-600 text-white hover:bg-leaf-700 hover:shadow-lg transition duration-300 flex items-center gap-2" title="Giỏ hàng">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span class="font-bold text-sm hidden sm:inline">{{ __('app.cart') }}</span>
                            <span data-cart-count
                                class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-bold border-2 border-cream-50 {{ ($cartCount ?? 0) > 0 ? '' : 'hidden' }}">{{ $cartCount ?? 0 }}</span>
                        </button>

                        {{-- Mini Cart Popup --}}
                        <div id="mini-cart-dropdown" class="absolute top-full right-0 mt-2 w-[360px] bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-cream-200 dark:border-gray-700 opacity-0 invisible transition-all duration-300 z-50 transform origin-top-right scale-95">
                            <div class="p-4 border-b border-cream-100 dark:border-gray-700 flex flex-col gap-2">
                                <div class="flex justify-between items-center">
                                    <h4 class="font-bold text-earth-800 dark:text-gray-100">Giỏ hàng của bạn</h4>
                                    <button type="button" id="mini-cart-clear-all" class="text-xs text-red-500 hover:text-red-700 transition font-medium px-2 py-1 bg-red-50 hover:bg-red-100 rounded-md">Xóa tất cả</button>
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer text-sm text-earth-600 dark:text-gray-400 hover:text-earth-800 transition w-max">
                                    <input type="checkbox" id="mini-cart-select-all" class="w-4 h-4 rounded text-leaf-600 focus:ring-leaf-500 border-gray-300">
                                    <span>Chọn tất cả</span>
                                </label>
                            </div>
                            @php
                                $miniCartItems = app(\App\Services\CartService::class)->items();
                                $miniCartSelectedSubtotal = app(\App\Services\CartService::class)->selectedSubtotal();
                            @endphp
                            <div class="max-h-80 overflow-y-auto p-2" id="mini-cart-items-container">
                                @if($miniCartItems->isEmpty())
                                    <div class="p-4 text-center text-sm text-gray-500" id="mini-cart-empty-msg">Giỏ hàng trống</div>
                                @else
                                    <div class="space-y-4 p-2">
                                        @foreach($miniCartItems as $item)
                                            <div class="flex items-start gap-3 mini-cart-item {{ !$item->is_selected ? 'opacity-60' : '' }}" data-id="{{ $item->id }}">
                                                <div class="flex items-center h-14">
                                                    <input type="checkbox" class="mini-cart-item-checkbox w-4 h-4 rounded text-leaf-600 focus:ring-leaf-500 border-gray-300 cursor-pointer" data-id="{{ $item->id }}" {{ $item->is_selected ? 'checked' : '' }}>
                                                </div>
                                                <img src="{{ \App\Support\MediaHelper::productImage($item->productVariant->product->thumbnail) }}" class="w-14 h-14 rounded-lg object-cover border border-cream-200" alt="">
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-bold text-earth-800 dark:text-gray-200 truncate">{{ $item->productVariant->product->name }}</p>
                                                    <p class="text-xs text-gray-500 mb-1">{{ $item->productVariant->name }}</p>
                                                    <div class="flex items-center gap-2">
                                                        <div class="flex items-center border border-cream-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800">
                                                            <button type="button" class="px-2 py-1 text-earth-500 hover:text-leaf-600 transition mini-cart-btn-minus" data-id="{{ $item->id }}">-</button>
                                                            <input type="number" class="w-8 text-center text-xs font-medium border-x border-cream-200 dark:border-gray-700 bg-transparent mini-cart-input-qty focus:outline-none" data-id="{{ $item->id }}" value="{{ $item->quantity }}" min="1">
                                                            <button type="button" class="px-2 py-1 text-earth-500 hover:text-leaf-600 transition mini-cart-btn-plus" data-id="{{ $item->id }}">+</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex flex-col items-end justify-between h-14">
                                                    <button type="button" class="text-gray-400 hover:text-red-500 transition mini-cart-btn-remove" data-id="{{ $item->id }}" title="Xóa">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                    <div class="font-bold text-sm text-leaf-600 mini-cart-item-subtotal" data-id="{{ $item->id }}">
                                                        {{ number_format($item->subtotal()) }}đ
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="p-4 border-t border-cream-100 dark:border-gray-700 bg-cream-50 dark:bg-gray-900 rounded-b-2xl {{ $miniCartItems->isEmpty() ? 'hidden' : '' }}" id="mini-cart-footer">
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-sm font-medium text-earth-700 dark:text-gray-300">Tạm tính:</span>
                                    <span class="font-bold text-lg text-earth-800 dark:text-white" id="mini-cart-subtotal">{{ number_format($miniCartSelectedSubtotal) }}đ</span>
                                </div>
                                <a href="{{ route('checkout.index') }}" id="mini-cart-checkout-btn" class="block w-full text-center bg-leaf-600 hover:bg-leaf-700 text-white font-bold py-2.5 rounded-xl transition shadow-md shadow-leaf-500/30 {{ $miniCartSelectedSubtotal > 0 ? '' : 'opacity-50 pointer-events-none' }}">Tiến hành thanh toán</a>
                            </div>
                        </div>
                    </div>

                    @auth
                        <div class="hidden sm:flex items-center gap-3">
                            <a href="{{ route('profile') }}"
                                class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-cream-200 transition"
                                title="Tài khoản">
                                <img src="{{ \App\Support\MediaHelper::avatar(auth()->user()->avatar, auth()->user()->name) }}"
                                    class="w-8 h-8 rounded-full object-cover border border-cream-200" alt="">
                                <span class="text-xs font-bold text-leaf-700 bg-leaf-100 px-2 py-0.5 rounded-full border border-leaf-200">
                                    {{ number_format(auth()->user()->reward_points) }} điểm
                                </span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="flex items-center">
                                @csrf
                                <button class="text-sm font-medium text-earth-500 hover:text-earth-800 px-2 py-1">Thoát</button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="btn-primary !px-4 !py-2.5 !text-sm hidden sm:inline-flex">Đăng
                            nhập</a>
                    @endauth

                    <button type="button" id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-cream-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
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
            </nav>
        </div>

        {{-- Mobile menu --}}
        <div id="mobile-menu" class="hidden md:hidden border-t border-cream-300 bg-cream-50 px-4 py-3 space-y-1">
            @auth
                <div class="flex items-center gap-3 mb-3 p-2 bg-white rounded-xl border border-cream-200">
                    <img src="{{ \App\Support\MediaHelper::avatar(auth()->user()->avatar, auth()->user()->name) }}"
                        class="w-10 h-10 rounded-full object-cover border border-cream-200" alt="">
                    <div>
                        <p class="font-bold text-earth-800">{{ auth()->user()->name }}</p>
                        <span class="text-[10px] font-bold text-leaf-700 bg-leaf-100 px-2 py-0.5 rounded-full border border-leaf-200">
                            {{ number_format(auth()->user()->reward_points) }} điểm
                        </span>
                    </div>
                </div>
            @endauth
            <a href="{{ route('home') }}" class="block px-3 py-2 rounded-lg hover:bg-cream-200">{{ __('nav.home') }}</a>
            <a href="{{ route('products.index') }}" class="block px-3 py-2 rounded-lg hover:bg-cream-200">{{ __('nav.products') }}</a>
            <a href="{{ route('recipes.index') }}" class="block px-3 py-2 rounded-lg hover:bg-cream-200">{{ __('nav.recipes') }}</a>
            @auth
                <a href="{{ route('favorites.index') }}" class="block px-3 py-2 rounded-lg hover:bg-cream-200">Yêu thích</a>
                <a href="{{ route('orders.index') }}" class="block px-3 py-2 rounded-lg hover:bg-cream-200">{{ __('nav.orders') }}</a>
                <a href="{{ route('profile') }}" class="block px-3 py-2 rounded-lg hover:bg-cream-200">{{ __('nav.account') }}</a>
                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                    @csrf
                    <button class="w-full text-left px-3 py-2 rounded-lg text-red-600 hover:bg-red-50 font-medium">Thoát</button>
                </form>
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
                        <span
                            class="w-10 h-10 rounded-xl bg-leaf-600 flex items-center justify-center text-white font-bold">S</span>
                        <span class="font-bold text-xl text-cream-50">SFood</span>
                    </div>
                    <p class="text-sm text-cream-300 leading-relaxed">Thực phẩm tươi sạch, nguồn gốc rõ ràng. Giao hàng
                        nhanh trong ngày tại TP.HCM và Hà Nội.</p>
                </div>
                <div>
                    <h4 class="font-semibold text-cream-50 mb-4">Danh mục</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('products.index') }}" class="hover:text-leaf-400 transition">Tất cả sản
                                phẩm</a></li>
                        <li><a href="{{ route('recipes.index') }}" class="hover:text-leaf-400 transition">Công thức nấu
                                ăn</a></li>
                        <li><a href="{{ route('products.index', ['category' => 'rau-cu']) }}"
                                class="hover:text-leaf-400 transition">Rau củ</a></li>
                        <li><a href="{{ route('products.index', ['category' => 'thit-ca']) }}"
                                class="hover:text-leaf-400 transition">Thịt cá</a></li>
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
                            <span
                                class="w-8 h-8 rounded-full bg-earth-700 flex items-center justify-center text-xs">FB</span>
                            <span
                                class="w-8 h-8 rounded-full bg-earth-700 flex items-center justify-center text-xs">IG</span>
                            <span
                                class="w-8 h-8 rounded-full bg-earth-700 flex items-center justify-center text-xs">YT</span>
                        </li>
                    </ul>
                </div>
            <div
                class="border-t border-earth-700 mt-10 pt-6 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm text-cream-400">
                <p>© {{ date('Y') }} SFood. Tất cả quyền được bảo lưu.</p>
                <p>Thiết kế cho trải nghiệm mua sắm tươi mỗi ngày 🌿</p>
            </div>
        </div>
    </footer>

    {{-- Messenger/Facebook Button --}}
    <a href="https://m.me/yourpage" target="_blank" rel="noopener noreferrer" class="fixed bottom-[5.5rem] right-6 z-40 w-14 h-14 bg-gradient-to-tr from-blue-500 to-blue-600 text-white rounded-full shadow-2xl flex items-center justify-center hover:scale-110 hover:shadow-blue-500/50 transition-all duration-300 group">
        <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.145 2 11.26c0 2.91 1.503 5.506 3.824 7.24v3.5l3.29-1.808c.91.252 1.87.388 2.886.388 5.523 0 10-4.145 10-9.26C22 6.145 17.523 2 12 2zm1.09 12.3l-2.82-3.02-5.49 3.02 6.03-6.4 2.91 3.02 5.38-3.02-6.01 6.4z"/></svg>
        <span class="absolute right-full mr-4 bg-white text-earth-800 text-xs font-bold px-2 py-1 rounded-lg shadow-md whitespace-nowrap opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity">Chat Messenger</span>
    </a>

    {{-- Live Chatbox --}}
    @auth
    @php
        $unreadChatCount = \App\Models\ChatMessage::where('user_id', auth()->id())->where('sender', 'admin')->where('is_read', false)->count();
    @endphp
    <div id="live-chatbox-container" class="fixed bottom-[9.5rem] right-6 z-50 flex flex-col items-end pointer-events-none">
        <!-- Chat Window -->
        <div id="live-chat-window" class="hidden pointer-events-auto bg-white dark:bg-gray-800 w-80 sm:w-96 rounded-2xl shadow-2xl border border-cream-200 dark:border-gray-700 mb-4 overflow-hidden flex flex-col transition-all duration-300 transform origin-bottom-right scale-95 opacity-0">
            <div class="bg-gradient-to-r from-green-500 to-green-600 p-4 text-white flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">👨‍💻</span>
                    <div>
                        <h4 class="font-bold">Chăm sóc khách hàng</h4>
                        <p class="text-[10px] text-green-100">Chúng tôi luôn sẵn sàng hỗ trợ</p>
                    </div>
                </div>
                <button type="button" id="close-live-chat-btn" class="text-white hover:text-green-100 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div id="live-chat-messages-container" class="flex-1 p-4 h-80 overflow-y-auto bg-cream-50 dark:bg-gray-900 flex flex-col gap-3">
                <div class="text-center text-sm text-gray-500 mt-4" id="live-chat-empty-msg">Bắt đầu trò chuyện với nhân viên hỗ trợ.</div>
            </div>
            <div class="p-3 bg-white dark:bg-gray-800 border-t border-cream-200 dark:border-gray-700 flex items-center gap-2">
                <input type="text" id="live-chat-input-field" class="flex-1 bg-cream-50 dark:bg-gray-900 border border-cream-200 dark:border-gray-700 text-sm rounded-xl px-4 py-2 focus:outline-none focus:border-green-500 text-earth-800 dark:text-gray-300" placeholder="Nhập tin nhắn..." autocomplete="off">
                <button type="button" id="send-live-chat-btn" class="bg-green-600 hover:bg-green-700 text-white rounded-xl px-4 py-2 transition shadow-sm">
                    <svg class="w-4 h-4 transform rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
            </div>
        </div>
        
        <!-- Toggle Button -->
        <button type="button" id="toggle-live-chat-btn" class="pointer-events-auto w-14 h-14 bg-gradient-to-tr from-green-500 to-green-600 text-white rounded-full shadow-2xl flex items-center justify-center hover:scale-110 hover:shadow-green-500/50 transition-all duration-300 focus:outline-none z-50 group relative">
            <svg id="live-chat-icon-msg" class="w-6 h-6 absolute transition-transform duration-300 scale-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
            <svg id="live-chat-icon-close" class="w-6 h-6 absolute transition-transform duration-300 scale-0 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            <span class="absolute right-full mr-4 bg-white text-earth-800 text-xs font-bold px-2 py-1 rounded-lg shadow-md whitespace-nowrap opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity">Chat Nhân Viên</span>
            @if($unreadChatCount > 0)
                <span id="live-chat-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-bold border-2 border-white">{{ $unreadChatCount }}</span>
            @endif
        </button>
    </div>
    @endauth

    {{-- AI Chatbox --}}
    <div id="ai-chatbox-container" class="fixed bottom-6 right-6 z-50 flex flex-col items-end pointer-events-none">
        <!-- Chat Window -->
        <div id="ai-chat-window" class="hidden pointer-events-auto bg-white dark:bg-gray-800 w-80 sm:w-96 rounded-2xl shadow-2xl border border-cream-200 dark:border-gray-700 mb-4 overflow-hidden flex flex-col transition-all duration-300 transform origin-bottom-right scale-95 opacity-0">
            <!-- Header -->
            <div class="bg-gradient-to-r from-leaf-600 to-leaf-500 p-4 text-white flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">🤖</span>
                    <div>
                        <h4 class="font-bold">SFood AI Assistant</h4>
                        <p class="text-[10px] text-leaf-100">Tìm kiếm sản phẩm nhanh chóng</p>
                    </div>
                </div>
                <button type="button" id="close-chat-btn" class="text-white hover:text-cream-200 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <!-- Messages -->
            <div id="ai-chat-messages" class="flex-1 p-4 h-80 overflow-y-auto bg-cream-50 dark:bg-gray-900 flex flex-col gap-3">
                <div class="flex gap-2 w-5/6">
                    <div class="bg-white dark:bg-gray-800 p-3 rounded-2xl rounded-tl-sm shadow-sm border border-cream-100 dark:border-gray-700 text-sm text-earth-800 dark:text-gray-200">
                        Chào bạn! Mình có thể giúp bạn tìm kiếm món gì hôm nay? (Ví dụ: "tôi muốn mua thịt bò")
                    </div>
                </div>
            </div>
            <!-- Input -->
            <div class="p-3 bg-white dark:bg-gray-800 border-t border-cream-200 dark:border-gray-700 flex items-center gap-2">
                <label class="cursor-pointer p-2 text-earth-400 hover:text-leaf-600 transition" title="Gửi hình ảnh">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <input type="file" id="ai-chat-image-input" accept="image/*" class="hidden">
                </label>
                <input type="text" id="ai-chat-input" class="flex-1 bg-cream-50 dark:bg-gray-900 border border-cream-200 dark:border-gray-700 text-sm rounded-xl px-4 py-2 focus:outline-none focus:border-leaf-500 text-earth-800 dark:text-gray-300" placeholder="Nhập tin nhắn..." autocomplete="off">
                <button type="button" id="send-chat-btn" class="bg-leaf-600 hover:bg-leaf-700 text-white rounded-xl px-4 py-2 transition shadow-sm">
                    <svg class="w-4 h-4 transform rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
            </div>
        </div>
        
        <!-- Toggle Button -->
        <button type="button" id="toggle-chat-btn" class="pointer-events-auto w-14 h-14 bg-gradient-to-tr from-leaf-600 to-green-400 text-white rounded-full shadow-2xl flex items-center justify-center hover:scale-110 hover:shadow-leaf-500/50 transition-all duration-300 focus:outline-none z-50 group relative">
            <svg id="chat-icon-msg" class="w-6 h-6 absolute transition-transform duration-300 scale-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
            <svg id="chat-icon-close" class="w-6 h-6 absolute transition-transform duration-300 scale-0 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            <span class="absolute right-full mr-4 bg-white text-earth-800 text-xs font-bold px-2 py-1 rounded-lg shadow-md whitespace-nowrap opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity">Chat AI</span>
        </button>
    </div>

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

    // AI Chatbox Logic
    const toggleChatBtn = document.getElementById('toggle-chat-btn');
    const closeChatBtn = document.getElementById('close-chat-btn');
    const chatWindow = document.getElementById('ai-chat-window');
    const chatIconMsg = document.getElementById('chat-icon-msg');
    const chatIconClose = document.getElementById('chat-icon-close');
    const chatInput = document.getElementById('ai-chat-input');
    const sendChatBtn = document.getElementById('send-chat-btn');
    const chatMessages = document.getElementById('ai-chat-messages');

    let isChatOpen = false;

    function toggleChat() {
        isChatOpen = !isChatOpen;
        if (isChatOpen) {
            chatWindow.classList.remove('hidden');
            // small delay for transition
            setTimeout(() => {
                chatWindow.classList.remove('opacity-0', 'scale-95');
                chatWindow.classList.add('opacity-100', 'scale-100');
            }, 10);
            chatIconMsg.classList.replace('scale-100', 'scale-0');
            chatIconMsg.classList.add('-rotate-90');
            chatIconClose.classList.replace('scale-0', 'scale-100');
            chatIconClose.classList.remove('rotate-90');
            chatInput.focus();
        } else {
            chatWindow.classList.remove('opacity-100', 'scale-100');
            chatWindow.classList.add('opacity-0', 'scale-95');
            setTimeout(() => chatWindow.classList.add('hidden'), 300);
            chatIconMsg.classList.replace('scale-0', 'scale-100');
            chatIconMsg.classList.remove('-rotate-90');
            chatIconClose.classList.replace('scale-100', 'scale-0');
            chatIconClose.classList.add('rotate-90');
        }
    }

    toggleChatBtn.addEventListener('click', toggleChat);
    closeChatBtn.addEventListener('click', toggleChat);

    // Chat History Array
    let chatHistory = [];

    function appendMessage(content, isUser = false) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `flex gap-2 max-w-[85%] ${isUser ? 'self-end justify-end' : 'self-start'}`;
        
        const bubble = document.createElement('div');
        bubble.className = isUser 
            ? 'bg-leaf-600 text-white p-3 rounded-2xl rounded-tr-sm shadow-sm border border-leaf-700 text-sm'
            : 'bg-white dark:bg-gray-800 text-earth-800 dark:text-gray-200 p-3 rounded-2xl rounded-tl-sm shadow-sm border border-cream-100 dark:border-gray-700 text-sm markdown-body';
        
        bubble.innerHTML = content;
        
        msgDiv.appendChild(bubble);
        chatMessages.appendChild(msgDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;

        // Ensure scrolling to bottom after any images finish loading
        const images = bubble.querySelectorAll('img');
        images.forEach(img => {
            img.addEventListener('load', () => {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            });
        });
    }

    async function sendChatMessage() {
        const text = chatInput.value.trim();
        if (!text) return;
        
        appendMessage(text, true);
        chatInput.value = '';
        chatInput.disabled = true;
        sendChatBtn.disabled = true;

        // typing indicator
        const typingId = 'typing-' + Date.now();
        const typingDiv = document.createElement('div');
        typingDiv.id = typingId;
        typingDiv.className = 'flex gap-2 self-start w-5/6';
        typingDiv.innerHTML = `<div class="bg-white dark:bg-gray-800 p-3 rounded-2xl rounded-tl-sm shadow-sm border border-cream-100 dark:border-gray-700 text-sm text-earth-800 dark:text-gray-400 flex items-center gap-1">
            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce"></span>
            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></span>
            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
        </div>`;
        chatMessages.appendChild(typingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;

        try {
            const res = await fetch('{{ route('ai.chat') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ 
                    message: text,
                    history: chatHistory
                })
            });
            const data = await res.json();
            document.getElementById(typingId).remove();
            appendMessage(data.reply, false);

            if (data.redirect) {
                appendMessage('<span class="text-sm italic text-gray-500">Đang chuyển hướng bạn đến trang sản phẩm...</span>', false);
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            }

            // Save to history
            chatHistory.push({ role: 'user', content: text });
            chatHistory.push({ role: 'assistant', content: data.reply });
            
            // Keep history short (e.g. max 10 messages)
            if (chatHistory.length > 10) chatHistory = chatHistory.slice(-10);

        } catch (e) {
            document.getElementById(typingId).remove();
            appendMessage('<span class="text-red-500">Lỗi kết nối.</span>', false);
        } finally {
            chatInput.disabled = false;
            sendChatBtn.disabled = false;
            chatInput.focus();
        }
    }

    sendChatBtn.addEventListener('click', sendChatMessage);
    chatInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            sendChatMessage();
        }
    });

    const chatImageInput = document.getElementById('ai-chat-image-input');
    if(chatImageInput) {
        chatImageInput.addEventListener('change', async function() {
            if (!this.files || !this.files[0]) return;
            
            const file = this.files[0];
            const objectUrl = URL.createObjectURL(file);
            appendMessage(`<img src="${objectUrl}" class="max-w-[200px] rounded-lg border border-leaf-400 object-cover">`, true);

            const typingId = 'typing-' + Date.now();
            const typingDiv = document.createElement('div');
            typingDiv.id = typingId;
            typingDiv.className = 'flex gap-2 self-start w-5/6';
            typingDiv.innerHTML = `<div class="bg-white dark:bg-gray-800 p-3 rounded-2xl rounded-tl-sm shadow-sm border border-cream-100 dark:border-gray-700 text-sm text-earth-800 dark:text-gray-400 flex items-center gap-1">
                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce"></span>
                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></span>
                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
            </div>`;
            chatMessages.appendChild(typingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;

            try {
                const formData = new FormData();
                formData.append('ai_image', file);

                const res = await fetch('{{ route('ai.chat.image') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });
                const data = await res.json();
                document.getElementById(typingId).remove();
                appendMessage(data.reply, false);
                
                chatImageInput.value = '';
            } catch (e) {
                document.getElementById(typingId).remove();
                appendMessage('<span class="text-red-500">Lỗi gửi hình ảnh.</span>', false);
                chatImageInput.value = '';
            }
        });
    }

    // Live Chat Logic
    const toggleLiveChatBtn = document.getElementById('toggle-live-chat-btn');
    const closeLiveChatBtn = document.getElementById('close-live-chat-btn');
    const liveChatWindow = document.getElementById('live-chat-window');
    const liveChatIconMsg = document.getElementById('live-chat-icon-msg');
    const liveChatIconClose = document.getElementById('live-chat-icon-close');
    const liveChatBadge = document.getElementById('live-chat-badge');
    const liveChatMessagesContainer = document.getElementById('live-chat-messages-container');
    const liveChatInputField = document.getElementById('live-chat-input-field');
    const sendLiveChatBtn = document.getElementById('send-live-chat-btn');
    let liveChatInterval = null;

    if (toggleLiveChatBtn && liveChatWindow) {
        const toggleLiveChat = () => {
            const isHidden = liveChatWindow.classList.contains('hidden');
            if (isHidden) {
                liveChatWindow.classList.remove('hidden');
                setTimeout(() => {
                    liveChatWindow.classList.remove('scale-95', 'opacity-0');
                    liveChatWindow.classList.add('scale-100', 'opacity-100');
                }, 10);
                liveChatIconMsg.classList.replace('scale-100', 'scale-0');
                liveChatIconClose.classList.replace('scale-0', 'scale-100');
                liveChatIconClose.classList.replace('rotate-90', 'rotate-0');
                
                if (liveChatBadge) liveChatBadge.style.display = 'none';

                fetchLiveMessages();
                liveChatInterval = setInterval(fetchLiveMessages, 5000);
            } else {
                liveChatWindow.classList.remove('scale-100', 'opacity-100');
                liveChatWindow.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    liveChatWindow.classList.add('hidden');
                }, 300);
                liveChatIconClose.classList.replace('scale-100', 'scale-0');
                liveChatIconClose.classList.replace('rotate-0', 'rotate-90');
                liveChatIconMsg.classList.replace('scale-0', 'scale-100');
                
                if (liveChatInterval) clearInterval(liveChatInterval);
            }
        };

        toggleLiveChatBtn.addEventListener('click', toggleLiveChat);
        closeLiveChatBtn.addEventListener('click', toggleLiveChat);

        const appendLiveMessage = (msg) => {
            const emptyMsg = document.getElementById('live-chat-empty-msg');
            if (emptyMsg) emptyMsg.style.display = 'none';

            const div = document.createElement('div');
            const isUser = msg.sender === 'user';
            div.className = `max-w-[85%] rounded-2xl p-3 text-sm shadow-sm ${isUser ? 'bg-green-600 text-white self-end rounded-tr-sm' : 'bg-white dark:bg-gray-800 text-earth-800 dark:text-gray-300 border border-cream-200 dark:border-gray-700 self-start rounded-tl-sm'}`;
            div.textContent = msg.message;
            liveChatMessagesContainer.appendChild(div);
            liveChatMessagesContainer.scrollTop = liveChatMessagesContainer.scrollHeight;
        };

        const fetchLiveMessages = async () => {
            try {
                const res = await fetch('{{ route('live-chat.fetch') }}');
                const data = await res.json();
                if (data.length > 0) {
                    liveChatMessagesContainer.innerHTML = '';
                    data.forEach(msg => appendLiveMessage(msg));
                }
            } catch (e) { console.error(e); }
        };

        const sendLiveMessage = async () => {
            const text = liveChatInputField.value.trim();
            if (!text) return;
            
            liveChatInputField.value = '';
            appendLiveMessage({ sender: 'user', message: text });

            try {
                await fetch('{{ route('live-chat.send') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ message: text })
                });
            } catch (e) { console.error(e); }
        };

        sendLiveChatBtn.addEventListener('click', sendLiveMessage);
        liveChatInputField.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendLiveMessage();
        });
    }

    // Global Countdown Script
    function updateCountdowns() {
        document.querySelectorAll('[data-countdown]').forEach(el => {
            const endsAt = new Date(el.dataset.countdown).getTime();
            const now = new Date().getTime();
            const distance = endsAt - now;
            const timerEl = el.querySelector('.countdown-timer');

            if (!timerEl) return;

            if (distance < 0) {
                timerEl.textContent = '00:00:00';
                const badge = el.closest('.badge');
                if (badge) badge.style.display = 'none';
                
                const section = el.closest('section');
                if (section && section.classList.contains('flash-sale-section')) {
                    section.style.display = 'none';
                }
                return;
            }

            const hours = Math.floor(distance / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            timerEl.textContent = String(hours).padStart(2, '0') + ':' + 
                                  String(minutes).padStart(2, '0') + ':' + 
                                  String(seconds).padStart(2, '0');
        });
    }
    
    updateCountdowns();
    setInterval(updateCountdowns, 1000);
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const miniCartContainer = document.getElementById('mini-cart-items-container');
    const cartToggleBtn = document.getElementById('cart-dropdown-toggle');
    const cartDropdown = document.getElementById('mini-cart-dropdown');
    const cartWrapper = document.getElementById('cart-dropdown-wrapper');
    const clearAllBtn = document.getElementById('mini-cart-clear-all');

    if (cartToggleBtn && cartDropdown) {
        cartToggleBtn.addEventListener('click', () => {
            const isHidden = cartDropdown.classList.contains('invisible');
            if (isHidden) {
                cartDropdown.classList.remove('invisible', 'opacity-0', 'scale-95');
                cartDropdown.classList.add('visible', 'opacity-100', 'scale-100');
            } else {
                cartDropdown.classList.add('invisible', 'opacity-0', 'scale-95');
                cartDropdown.classList.remove('visible', 'opacity-100', 'scale-100');
            }
        });

        document.addEventListener('click', (e) => {
            if (cartWrapper && !cartWrapper.contains(e.target)) {
                cartDropdown.classList.add('invisible', 'opacity-0', 'scale-95');
                cartDropdown.classList.remove('visible', 'opacity-100', 'scale-100');
            }
        });
    }

    if (miniCartContainer) {
        const fetchApi = async (url, bodyData) => {
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(bodyData)
                });
                if (res.ok) {
                    return await res.json();
                }
            } catch (e) { console.error('Cart API error', e); }
            return null;
        };

        const updateCartItem = async (itemId, quantity) => {
            if (quantity < 1) return;
            const data = await fetchApi('{{ route('cart.update') }}', { item_id: itemId, quantity: quantity });
            if (data) updateMiniCartUI(data, itemId);
        };

        const removeCartItem = async (itemId) => {
            const data = await fetchApi('{{ route('cart.remove') }}', { item_id: itemId });
            if (data) {
                const itemEl = document.querySelector(`.mini-cart-item[data-id="${itemId}"]`);
                if (itemEl) itemEl.remove();
                updateMiniCartUI(data);
            }
        };

        const clearCart = async () => {
            const data = await fetchApi('{{ route('cart.clear') }}', {});
            if (data) {
                document.querySelectorAll('.mini-cart-item').forEach(el => el.remove());
                updateMiniCartUI(data);
            }
        };

        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', () => {
                if (confirm('Bạn có chắc muốn xóa tất cả sản phẩm khỏi giỏ hàng?')) {
                    clearCart();
                }
            });
        }

        const toggleSelect = async (itemId, selected) => {
            const data = await fetchApi('{{ route('cart.toggle_select') }}', { item_id: itemId, selected: selected });
            if (data) updateMiniCartUI(data, itemId);
        };

        const toggleSelectAll = async (selected) => {
            const data = await fetchApi('{{ route('cart.toggle_select_all') }}', { selected: selected });
            if (data) {
                document.querySelectorAll('.mini-cart-item-checkbox').forEach(cb => {
                    cb.checked = selected;
                    const itemEl = cb.closest('.mini-cart-item');
                    if (selected) itemEl.classList.remove('opacity-60');
                    else itemEl.classList.add('opacity-60');
                });
                updateMiniCartUI(data);
            }
        };

        const updateMiniCartUI = (data, updatedItemId = null) => {
            // Update counts
            document.querySelectorAll('[data-cart-count]').forEach(el => {
                el.textContent = data.count;
                if (data.count > 0) el.classList.remove('hidden');
                else el.classList.add('hidden');
            });
            
            // Update subtotal
            const subtotalEl = document.getElementById('mini-cart-subtotal');
            if (subtotalEl) subtotalEl.textContent = data.selected_subtotal_formatted;

            // Checkout button state
            const checkoutBtn = document.getElementById('mini-cart-checkout-btn');
            if (checkoutBtn) {
                if (data.selected_subtotal > 0) checkoutBtn.classList.remove('opacity-50', 'pointer-events-none');
                else checkoutBtn.classList.add('opacity-50', 'pointer-events-none');
            }

            if (data.empty) {
                const emptyMsg = document.getElementById('mini-cart-empty-msg');
                const footer = document.getElementById('mini-cart-footer');
                if (emptyMsg) emptyMsg.classList.remove('hidden');
                else miniCartContainer.innerHTML = '<div class="p-4 text-center text-sm text-gray-500" id="mini-cart-empty-msg">Giỏ hàng trống</div>';
                if (footer) footer.classList.add('hidden');
            } else {
                if (updatedItemId && data.item) {
                    const itemSubtotalEl = document.querySelector(`.mini-cart-item-subtotal[data-id="${updatedItemId}"]`);
                    const itemInput = document.querySelector(`.mini-cart-input-qty[data-id="${updatedItemId}"]`);
                    const itemEl = document.querySelector(`.mini-cart-item[data-id="${updatedItemId}"]`);
                    if (itemSubtotalEl) itemSubtotalEl.textContent = data.item.subtotal_formatted;
                    if (itemInput) itemInput.value = data.item.quantity;
                    if (itemEl) {
                        if (data.item.is_selected) itemEl.classList.remove('opacity-60');
                        else itemEl.classList.add('opacity-60');
                    }
                }
                
                // Update select all checkbox state
                const selectAllCb = document.getElementById('mini-cart-select-all');
                if (selectAllCb) {
                    const allChecked = Array.from(document.querySelectorAll('.mini-cart-item-checkbox')).every(cb => cb.checked);
                    selectAllCb.checked = allChecked && document.querySelectorAll('.mini-cart-item-checkbox').length > 0;
                }
            }
        };

        // Initialize select all state
        const selectAllCb = document.getElementById('mini-cart-select-all');
        if (selectAllCb) {
            const allChecked = Array.from(document.querySelectorAll('.mini-cart-item-checkbox')).every(cb => cb.checked);
            selectAllCb.checked = allChecked && document.querySelectorAll('.mini-cart-item-checkbox').length > 0;
            
            selectAllCb.addEventListener('change', (e) => {
                toggleSelectAll(e.target.checked);
            });
        }

        miniCartContainer.addEventListener('click', (e) => {
            const btnMinus = e.target.closest('.mini-cart-btn-minus');
            const btnPlus = e.target.closest('.mini-cart-btn-plus');
            const btnRemove = e.target.closest('.mini-cart-btn-remove');

            if (btnMinus) {
                const id = btnMinus.dataset.id;
                const input = document.querySelector(`.mini-cart-input-qty[data-id="${id}"]`);
                if (input) updateCartItem(id, parseInt(input.value) - 1);
            } else if (btnPlus) {
                const id = btnPlus.dataset.id;
                const input = document.querySelector(`.mini-cart-input-qty[data-id="${id}"]`);
                if (input) updateCartItem(id, parseInt(input.value) + 1);
            } else if (btnRemove) {
                const id = btnRemove.dataset.id;
                removeCartItem(id);
            }
        });

        miniCartContainer.addEventListener('change', (e) => {
            if (e.target.classList.contains('mini-cart-input-qty')) {
                const id = e.target.dataset.id;
                updateCartItem(id, parseInt(e.target.value));
            } else if (e.target.classList.contains('mini-cart-item-checkbox')) {
                const id = e.target.dataset.id;
                toggleSelect(id, e.target.checked);
            }
        });
    }
});
</script>
@stack('scripts')
</body>
</html>
