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
        const previewBox = wrapper.querySelector('[data-product-preview]');
        const backdrop = wrapper.querySelector('[data-preview-backdrop]');
        const url = wrapper.dataset.suggestUrl;

        if (!input || !dropdown || !url) return;

        let debounceTimer = null;
        let hoverTimer = null;
        let closeTimer = null;
        let searchData = { products: [], recipes: [] };

        const hidePreview = () => {
            if (hoverTimer) {
                clearTimeout(hoverTimer);
                hoverTimer = null;
            }
            if (previewBox) {
                previewBox.classList.remove('is-active', 'align-right', 'align-left');
                previewBox.innerHTML = '';
            }
            if (backdrop) {
                backdrop.classList.remove('is-active');
            }
            dropdown.querySelectorAll('.suggest-item-link.is-hovered').forEach(el => el.classList.remove('is-hovered'));
        };

        const hideAll = () => {
            hidePreview();
            dropdown.classList.remove('active');
        };

        const showDropdown = () => dropdown.classList.add('active');

        const scheduleClose = () => {
            if (closeTimer) clearTimeout(closeTimer);
            closeTimer = setTimeout(() => {
                if (window.innerWidth >= 768) {
                    hidePreview();
                }
            }, 240);
        };

        const cancelClose = () => {
            if (closeTimer) {
                clearTimeout(closeTimer);
                closeTimer = null;
            }
        };

        const positionPreview = (itemEl) => {
            if (!previewBox) return;

            const isMobile = window.innerWidth < 768;
            if (isMobile) {
                previewBox.style.left = '';
                previewBox.style.right = '';
                previewBox.style.top = '';
                previewBox.style.bottom = '';
                return;
            }

            const wrapperRect = wrapper.getBoundingClientRect();
            const dropdownRect = dropdown.getBoundingClientRect();
            const itemRect = itemEl.getBoundingClientRect();
            const previewWidth = previewBox.offsetWidth || 330;
            const previewHeight = previewBox.offsetHeight || 380;

            // Space calculation on viewport
            const spaceRight = window.innerWidth - dropdownRect.right;
            const spaceLeft = dropdownRect.left;

            // Place on the right if space allows, or flip to the left
            if (spaceRight >= previewWidth + 12 || spaceRight >= spaceLeft) {
                previewBox.style.left = `${dropdown.offsetLeft + dropdown.offsetWidth + 10}px`;
                previewBox.style.right = 'auto';
                previewBox.classList.remove('align-left');
                previewBox.classList.add('align-right');
            } else {
                previewBox.style.left = 'auto';
                previewBox.style.right = `${wrapper.offsetWidth - dropdown.offsetLeft + 10}px`;
                previewBox.classList.remove('align-right');
                previewBox.classList.add('align-left');
            }

            // Vertical positioning: align with item top, clamped within dropdown & viewport
            const itemTopRelativeToWrapper = itemRect.top - wrapperRect.top;
            let topPos = Math.max(dropdown.offsetTop, itemTopRelativeToWrapper);

            // Clamp if preview extends past bottom of viewport
            const previewBottomInViewport = itemRect.top + previewHeight;
            const maxAllowedY = window.innerHeight - 15;
            if (previewBottomInViewport > maxAllowedY) {
                const overflow = previewBottomInViewport - maxAllowedY;
                topPos = Math.max(dropdown.offsetTop, topPos - overflow);
            }

            previewBox.style.top = `${topPos}px`;
        };

        const renderPreviewContent = (item) => {
            if (!previewBox) return;

            if (item.type === 'product') {
                const fallbackImg = "data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22200%22%20height%3D%22200%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20fill%3D%22%23f8f9fa%22%20width%3D%22200%22%20height%3D%22200%22%2F%3E%3Ctext%20fill%3D%22%236c757d%22%20font-family%3D%22sans-serif%22%20font-size%3D%2214%22%20dy%3D%2210.5%22%20font-weight%3D%22bold%22%20x%3D%2250%25%22%20y%3D%2250%25%22%20text-anchor%3D%22middle%22%3ESFood%20Clean%3C%2Ftext%3E%3C%2Fsvg%3E";
                const imgSrc = item.image || fallbackImg;
                const escapedName = escapeHtml(item.name);
                const escapedCategory = escapeHtml(item.category || 'Nông sản sạch');
                const escapedSku = item.sku ? escapeHtml(item.sku) : '';
                const escapedDesc = item.short_description ? escapeHtml(item.short_description) : '';
                const variantsJson = item.variants_data ? escapeJsonAttr(item.variants_data) : '[]';

                previewBox.innerHTML = `
                    <div class="product-preview-content p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-success-subtle text-success border border-success-subtle fw-semibold rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                                ${escapedCategory}
                            </span>
                            <div class="d-flex align-items-center gap-2">
                                ${escapedSku ? `<span class="text-muted font-monospace" style="font-size: 0.7rem;">SKU: ${escapedSku}</span>` : ''}
                                <button type="button" class="btn-close d-md-none preview-close-btn" aria-label="Đóng" style="font-size: 0.75rem;"></button>
                            </div>
                        </div>

                        <div class="preview-img-wrapper position-relative rounded-3 bg-light border p-2 mb-2.5 d-flex align-items-center justify-content-center overflow-hidden" style="height: 180px;">
                            <img src="${imgSrc}" alt="${escapedName}" class="w-100 h-100 object-fit-contain" loading="lazy" onerror="this.onerror=null;this.src='${fallbackImg}';">
                            ${item.discount_percent ? `<span class="position-absolute top-0 end-0 m-2 badge bg-danger rounded-pill shadow-sm" style="font-size: 0.72rem;">-${item.discount_percent}</span>` : ''}
                            ${!item.in_stock ? `<div class="position-absolute inset-0 bg-dark bg-opacity-50 w-100 h-100 d-flex align-items-center justify-content-center text-white fw-bold small"><i class="bi bi-slash-circle me-1"></i>Tạm hết hàng</div>` : ''}
                        </div>

                        <h6 class="fw-bold text-dark mb-1 lh-sm" style="font-size: 0.95rem;">
                            <a href="${item.url}" class="text-dark text-decoration-none hover-success transition">${escapedName}</a>
                        </h6>

                        <div class="d-flex align-items-baseline gap-2 mb-2">
                            <span class="fw-bold text-success fs-5 lh-1">${item.price_range || item.price}</span>
                            ${item.original_price ? `<span class="text-muted text-decoration-line-through small" style="font-size: 0.8rem;">${item.original_price}</span>` : ''}
                        </div>

                        ${escapedDesc ? `
                            <p class="text-muted small mb-2 lh-sm" style="font-size: 0.78rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                ${escapedDesc}
                            </p>
                        ` : ''}

                        <div class="d-flex align-items-center justify-content-between mb-3 pt-1 border-top" style="font-size: 0.75rem;">
                            <div class="d-flex align-items-center gap-1.5 ${item.in_stock ? 'text-success' : 'text-danger'} fw-semibold">
                                <i class="bi ${item.in_stock ? 'bi-check-circle-fill' : 'bi-x-circle-fill'}"></i>
                                <span>${item.stock_text}${item.stock_count > 0 ? ` (${item.stock_count})` : ''}</span>
                            </div>
                            ${item.has_multiple_variants ? `
                                <span class="text-muted fst-italic">
                                    <i class="bi bi-layers me-1"></i>Nhiều biến thể
                                </span>
                            ` : ''}
                        </div>

                        <div class="d-flex gap-2">
                            ${item.in_stock ? `
                                <button type="button" 
                                    class="btn btn-success btn-sm flex-grow-1 fw-semibold rounded-pill py-2 d-flex align-items-center justify-content-center gap-1.5 shadow-sm btn-quick-cart"
                                    data-action="${item.has_multiple_variants ? 'open-variant-modal' : 'quick-add'}"
                                    data-add-url="${item.add_url}"
                                    data-product-name="${escapedName}"
                                    data-product-img="${imgSrc}"
                                    ${item.has_multiple_variants ? `data-variants='${variantsJson}'` : `data-variant-id="${item.first_variant_id}"`}>
                                    <i class="bi bi-cart-plus"></i>
                                    <span>Thêm giỏ hàng</span>
                                </button>
                            ` : `
                                <button type="button" class="btn btn-secondary btn-sm flex-grow-1 fw-semibold rounded-pill py-2 d-flex align-items-center justify-content-center gap-1.5 disabled" disabled>
                                    <i class="bi bi-x-circle"></i>
                                    <span>Hết hàng</span>
                                </button>
                            `}
                            <a href="${item.url}" class="btn btn-outline-success btn-sm px-3 rounded-pill fw-semibold py-2 d-flex align-items-center justify-content-center gap-1">
                                <span>Chi tiết</span>
                                <i class="bi bi-arrow-right-short fs-6"></i>
                            </a>
                        </div>
                    </div>
                `;
            } else if (item.type === 'recipe') {
                const fallbackRecipeImg = "data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22200%22%20height%3D%22200%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20fill%3D%22%23fff3cd%22%20width%3D%22200%22%20height%3D%22200%22%2F%3E%3Ctext%20fill%3D%22%23856404%22%20font-family%3D%22sans-serif%22%20font-size%3D%2214%22%20dy%3D%2210.5%22%20font-weight%3D%22bold%22%20x%3D%2250%25%22%20y%3D%2250%25%22%20text-anchor%3D%22middle%22%3ESFood%20Recipe%3C%2Ftext%3E%3C%2Fsvg%3E";
                const imgSrc = item.image || fallbackRecipeImg;
                const escapedName = escapeHtml(item.name);
                const escapedDesc = item.short_description ? escapeHtml(item.short_description) : '';

                previewBox.innerHTML = `
                    <div class="product-preview-content p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-semibold rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                                <i class="bi bi-egg-fried me-1"></i>Công thức món ăn
                            </span>
                            <button type="button" class="btn-close d-md-none preview-close-btn" aria-label="Đóng" style="font-size: 0.75rem;"></button>
                        </div>

                        <div class="preview-img-wrapper position-relative rounded-3 bg-light border p-2 mb-2.5 d-flex align-items-center justify-content-center overflow-hidden" style="height: 180px;">
                            <img src="${imgSrc}" alt="${escapedName}" class="w-100 h-100 object-fit-cover rounded-2" loading="lazy" onerror="this.onerror=null;this.src='${fallbackRecipeImg}';">
                            ${item.subtitle ? `<span class="position-absolute bottom-0 start-0 m-2 badge bg-dark bg-opacity-75 rounded-pill"><i class="bi bi-clock me-1"></i>${escapeHtml(item.subtitle)}</span>` : ''}
                        </div>

                        <h6 class="fw-bold text-dark mb-1 lh-sm" style="font-size: 0.95rem;">
                            <a href="${item.url}" class="text-dark text-decoration-none hover-success transition">${escapedName}</a>
                        </h6>

                        ${escapedDesc ? `
                            <p class="text-muted small mb-3 lh-sm" style="font-size: 0.78rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                ${escapedDesc}
                            </p>
                        ` : ''}

                        <a href="${item.url}" class="btn btn-warning w-100 text-dark btn-sm rounded-pill fw-semibold py-2 d-flex align-items-center justify-content-center gap-1 shadow-sm">
                            <i class="bi bi-book"></i>
                            <span>Xem công thức nấu ăn</span>
                        </a>
                    </div>
                `;
            }
        };

        const showPreview = (item, itemEl) => {
            renderPreviewContent(item);

            dropdown.querySelectorAll('.suggest-item-link.is-hovered').forEach(el => el.classList.remove('is-hovered'));
            itemEl.classList.add('is-hovered');

            positionPreview(itemEl);

            if (previewBox) previewBox.classList.add('is-active');
            if (backdrop && window.innerWidth < 768) backdrop.classList.add('is-active');
        };

        const render = (data) => {
            searchData = {
                products: data.products || [],
                recipes: data.recipes || []
            };

            const items = [...searchData.products, ...searchData.recipes];
            if (!items.length) {
                dropdown.innerHTML = '<div class="p-3 text-center text-muted small"><i class="bi bi-search me-1"></i>Không tìm thấy gợi ý phù hợp</div>';
                hidePreview();
                showDropdown();
                return;
            }

            let html = '';
            if (searchData.products.length) {
                html += '<div class="px-3 py-2 bg-light text-uppercase fw-bold text-success border-bottom d-flex align-items-center" style="font-size: 0.72rem; letter-spacing: 0.5px;"><i class="bi bi-box-seam me-1.5"></i>Sản phẩm</div>';
                searchData.products.forEach((item, index) => {
                    html += suggestItem(item, 'bi-basket2', index, 'product');
                });
            }
            if (searchData.recipes.length) {
                html += '<div class="px-3 py-2 bg-light text-uppercase fw-bold text-warning border-top border-bottom d-flex align-items-center" style="font-size: 0.72rem; letter-spacing: 0.5px;"><i class="bi bi-journal-text me-1.5"></i>Công thức món ăn</div>';
                searchData.recipes.forEach((item, index) => {
                    html += suggestItem(item, 'bi-egg-fried', index, 'recipe');
                });
            }

            dropdown.innerHTML = html;
            hidePreview();
            showDropdown();
            attachItemEvents();
        };

        const attachItemEvents = () => {
            dropdown.querySelectorAll('.suggest-item-link').forEach(link => {
                link.addEventListener('mouseenter', () => {
                    cancelClose();
                    if (hoverTimer) clearTimeout(hoverTimer);

                    const type = link.dataset.itemType;
                    const index = parseInt(link.dataset.itemIndex, 10);
                    const item = (type === 'product') ? searchData.products[index] : searchData.recipes[index];
                    if (!item) return;

                    // Hover debounce 60ms
                    hoverTimer = setTimeout(() => {
                        showPreview(item, link);
                    }, 60);
                });

                link.addEventListener('mouseleave', () => {
                    if (hoverTimer) {
                        clearTimeout(hoverTimer);
                        hoverTimer = null;
                    }
                    scheduleClose();
                });
            });
        };

        // Live input handling with 250ms debounce
        input.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            const q = input.value.trim();
            if (q.length < 2) {
                hideAll();
                return;
            }
            debounceTimer = setTimeout(async () => {
                try {
                    const res = await fetch(`${url}?q=${encodeURIComponent(q)}`);
                    render(await res.json());
                } catch {
                    hideAll();
                }
            }, 250);
        });

        input.addEventListener('focus', () => {
            if (input.value.trim().length >= 2) {
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });

        // Hover events on preview box
        if (previewBox) {
            previewBox.addEventListener('mouseenter', () => {
                cancelClose();
            });

            previewBox.addEventListener('mouseleave', () => {
                scheduleClose();
            });

            previewBox.addEventListener('click', (e) => {
                if (e.target.closest('.preview-close-btn')) {
                    hidePreview();
                }
                const btnQuick = e.target.closest('.btn-quick-cart');
                if (btnQuick && btnQuick.dataset.action === 'open-variant-modal') {
                    hidePreview();
                }
            });
        }

        // Dropdown hover & click delegation
        if (dropdown) {
            dropdown.addEventListener('mouseenter', () => {
                cancelClose();
            });

            dropdown.addEventListener('mouseleave', (e) => {
                if (previewBox && previewBox.contains(e.relatedTarget)) {
                    return;
                }
                scheduleClose();
            });

            dropdown.addEventListener('click', (e) => {
                const link = e.target.closest('[data-suggest-link]');
                if (!link) return;

                if (window.innerWidth < 768) {
                    e.preventDefault();
                    const type = link.dataset.itemType;
                    const index = parseInt(link.dataset.itemIndex, 10);
                    const item = (type === 'product') ? searchData.products[index] : searchData.recipes[index];
                    if (item) {
                        showPreview(item, link);
                    }
                } else {
                    window.location.href = link.dataset.suggestLink;
                }
            });
        }

        // Backdrop click on mobile
        if (backdrop) {
            backdrop.addEventListener('click', () => {
                hidePreview();
            });
        }

        // Click outside closes everything
        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target) && (!previewBox || !previewBox.contains(e.target))) {
                hideAll();
            }
        });

        // Escape key closes everything
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                hideAll();
            }
        });
    });
}

function suggestItem(item, fallbackIcon, index, type) {
    const fallbackImg = "data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2240%22%20height%3D%2240%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20fill%3D%22%23f8f9fa%22%20width%3D%2240%22%20height%3D%2240%22%2F%3E%3Ctext%20fill%3D%22%23198754%22%20font-family%3D%22sans-serif%22%20font-size%3D%2212%22%20dy%3D%224%22%20x%3D%2250%25%22%20y%3D%2250%25%22%20text-anchor%3D%22middle%22%3ES%3C%2Ftext%3E%3C%2Fsvg%3E";
    const imgHtml = item.image
        ? `<img src="${item.image}" alt="${escapeHtml(item.name)}" class="rounded-2 object-fit-cover flex-shrink-0" style="width: 40px; height: 40px;" onerror="this.onerror=null;this.src='${fallbackImg}';">`
        : `<div class="rounded-2 bg-light border d-flex align-items-center justify-content-center text-success flex-shrink-0" style="width: 40px; height: 40px;"><i class="bi ${fallbackIcon} fs-5"></i></div>`;

    const escapedName = escapeHtml(item.name);
    const escapedSubtitle = item.subtitle ? escapeHtml(item.subtitle) : '';

    return `<a href="${item.url}" data-suggest-link="${item.url}" data-item-type="${type}" data-item-index="${index}"
        class="suggest-item-link d-flex align-items-center gap-3 px-3 py-2 text-decoration-none text-dark border-bottom transition">
        ${imgHtml}
        <div class="flex-grow-1 min-w-0" style="min-width: 0;">
            <div class="suggest-item-name fw-semibold text-truncate small mb-0">${escapedName}</div>
            ${escapedSubtitle ? `<div class="text-muted text-truncate" style="font-size: 0.75rem;">${escapedSubtitle}</div>` : ''}
        </div>
        ${item.price ? `
            <div class="text-end flex-shrink-0 ms-2">
                <div class="fw-bold text-success small">${item.price}</div>
                ${item.original_price ? `<div class="text-muted text-decoration-line-through" style="font-size: 0.7rem;">${item.original_price}</div>` : ''}
            </div>` : ''}
    </a>`;
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function escapeJsonAttr(data) {
    if (!data) return '[]';
    return JSON.stringify(data).replace(/"/g, '&quot;');
}
