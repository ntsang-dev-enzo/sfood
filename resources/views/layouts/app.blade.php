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
                <a href="{{ route('about') }}" class="hover:text-white transition hidden sm:inline">{{ __('app.about') }}</a>
                <a href="{{ route('contact') }}" class="hover:text-white transition hidden sm:inline">{{ __('app.contact') }}</a>
                <a href="{{ route('faq') }}" class="hover:text-white transition hidden sm:inline">{{ __('app.faq') }}</a>
                
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
                        <a href="{{ route('favorites.index') }}"
                            class="p-2.5 rounded-xl hover:bg-cream-200 transition text-earth-700 {{ request()->routeIs('favorites.*') ? 'bg-cream-200' : '' }}"
                            title="Yêu thích">
                            <span class="text-xl leading-none">❤️</span>
                        </a>
                    @endauth
                    <a href="{{ route('cart') }}"
                        class="relative px-4 py-2.5 rounded-2xl bg-leaf-600 text-white hover:bg-leaf-700 hover:shadow-lg hover:-translate-y-0.5 transition duration-300 flex items-center gap-2" title="Giỏ hàng">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="font-bold text-sm hidden sm:inline">{{ __('app.cart') }}</span>
                        <span data-cart-count
                            class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-bold border-2 border-cream-50 {{ ($cartCount ?? 0) > 0 ? '' : 'hidden' }}">{{ $cartCount ?? 0 }}</span>
                    </a>

                    @auth
                        <a href="{{ route('profile') }}"
                            class="hidden sm:flex items-center gap-2 p-1.5 rounded-xl hover:bg-cream-200 transition"
                            title="Tài khoản">
                            <img src="{{ \App\Support\MediaHelper::avatar(auth()->user()->avatar, auth()->user()->name) }}"
                                class="w-8 h-8 rounded-lg object-cover" alt="">
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                            @csrf
                            <button class="text-sm text-earth-500 hover:text-earth-800 px-2 py-1">Thoát</button>
                        </form>
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
                @auth
                    <a href="{{ route('favorites.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('favorites.*') ? 'bg-leaf-600 text-white' : 'text-earth-700 hover:bg-cream-200' }} transition">Yêu thích</a>
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
            </div>
            <div
                class="border-t border-earth-700 mt-10 pt-6 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm text-cream-400">
                <p>© {{ date('Y') }} SFood. Tất cả quyền được bảo lưu.</p>
                <p>Thiết kế cho trải nghiệm mua sắm tươi mỗi ngày 🌿</p>
            </div>
        </div>
    </footer>

    {{-- Lucky Wheel Floating Button --}}
    @auth
        <a href="{{ route('lucky-wheel.index') }}" class="fixed bottom-[5.5rem] right-6 z-40 w-14 h-14 bg-gradient-to-tr from-yellow-400 to-orange-500 text-white rounded-full shadow-2xl flex items-center justify-center hover:scale-110 hover:shadow-orange-500/50 transition-all duration-300 group">
            <span class="text-2xl" style="animation: spin 10s linear infinite;">🎡</span>
            <span class="absolute right-full mr-4 bg-white text-earth-800 text-xs font-bold px-2 py-1 rounded-lg shadow-md whitespace-nowrap opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity">Vòng Quay May Mắn</span>
            @if(auth()->user()->last_free_spin_at === null || auth()->user()->last_free_spin_at->diffInHours(now()) >= 24)
                <span class="absolute top-0 right-0 w-3 h-3 bg-red-500 rounded-full border-2 border-white animate-ping"></span>
                <span class="absolute top-0 right-0 w-3 h-3 bg-red-500 rounded-full border-2 border-white"></span>
            @endif
        </a>
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
        <button type="button" id="toggle-chat-btn" class="pointer-events-auto w-14 h-14 bg-gradient-to-tr from-leaf-600 to-green-400 text-white rounded-full shadow-2xl flex items-center justify-center hover:scale-110 hover:shadow-leaf-500/50 transition-all duration-300 focus:outline-none z-50">
            <svg id="chat-icon-msg" class="w-6 h-6 absolute transition-transform duration-300 scale-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
            <svg id="chat-icon-close" class="w-6 h-6 absolute transition-transform duration-300 scale-0 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
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
@stack('scripts')
</body>
</html>
