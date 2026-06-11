import './bootstrap';
import { initCartPage } from './cart';
import { initFavoriteButton } from './favorites';

document.addEventListener('DOMContentLoaded', () => {
    initMobileMenu();
    initVoiceSearch();
    initSearchAutocomplete();
    initCartPage();
    initFavoriteButton();
});

function initMobileMenu() {
    const btn = document.getElementById('mobile-menu-btn');
    const menu = document.getElementById('mobile-menu');
    if (!btn || !menu) return;
    btn.addEventListener('click', () => menu.classList.toggle('hidden'));
}

function initVoiceSearch() {
    document.querySelectorAll('.voice-search-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SpeechRecognition) {
                alert('Trình duyệt không hỗ trợ tìm kiếm giọng nói.');
                return;
            }
            const input = btn.closest('.search-autocomplete')?.querySelector('[data-autocomplete]');
            const recognition = new SpeechRecognition();
            recognition.lang = 'vi-VN';
            recognition.onresult = (e) => {
                const transcript = e.results[0][0].transcript.trim();
                if (input) {
                    input.value = transcript;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.closest('form')?.submit();
                }
            };
            recognition.start();
        });
    });
}

function initSearchAutocomplete() {
    document.querySelectorAll('.search-autocomplete').forEach(wrapper => {
        const input = wrapper.querySelector('[data-autocomplete]');
        const dropdown = wrapper.querySelector('[data-suggest-dropdown]');
        const url = wrapper.dataset.suggestUrl;
        if (!input || !dropdown || !url) return;

        let debounceTimer;

        const hide = () => dropdown.classList.remove('active');
        const show = () => dropdown.classList.add('active');

        const render = (data) => {
            const items = [...(data.products || []), ...(data.recipes || [])];
            if (!items.length) {
                dropdown.innerHTML = '<div class="px-4 py-3 text-sm text-earth-500">Không tìm thấy gợi ý</div>';
                show();
                return;
            }

            let html = '';
            if (data.products?.length) {
                html += '<div class="px-3 py-2 text-xs font-semibold text-earth-500 uppercase tracking-wide">Sản phẩm</div>';
                data.products.forEach(item => { html += suggestItem(item, '🛒'); });
            }
            if (data.recipes?.length) {
                html += '<div class="px-3 py-2 text-xs font-semibold text-earth-500 uppercase tracking-wide border-t border-cream-200">Món ăn</div>';
                data.recipes.forEach(item => { html += suggestItem(item, '🍳'); });
            }
            dropdown.innerHTML = html;
            show();
        };

        input.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            const q = input.value.trim();
            if (q.length < 2) { hide(); return; }
            debounceTimer = setTimeout(async () => {
                try {
                    const res = await fetch(`${url}?q=${encodeURIComponent(q)}`);
                    render(await res.json());
                } catch { hide(); }
            }, 250);
        });

        input.addEventListener('focus', () => {
            if (input.value.trim().length >= 2) input.dispatchEvent(new Event('input', { bubbles: true }));
        });

        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) hide();
        });

        dropdown.addEventListener('click', (e) => {
            const link = e.target.closest('[data-suggest-link]');
            if (link) window.location.href = link.dataset.suggestLink;
        });
    });
}

function suggestItem(item, icon) {
    return `<a href="${item.url}" data-suggest-link="${item.url}"
        class="flex items-center gap-3 px-4 py-2.5 hover:bg-cream-100 transition cursor-pointer">
        <span class="w-8 h-8 rounded-lg bg-cream-200 flex items-center justify-center text-sm">${icon}</span>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-earth-800 truncate">${item.name}</p>
            <p class="text-xs text-earth-500">${item.subtitle || ''}</p>
        </div>
        ${item.price ? `<span class="text-sm font-semibold text-leaf-700">${item.price}</span>` : ''}
    </a>`;
}
