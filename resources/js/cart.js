const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content;

async function cartRequest(url, body) {
    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf(),
        },
        body: JSON.stringify(body),
    });
    return res.json();
}

function updateHeaderBadge(count) {
    const badge = document.querySelector('[data-cart-count]');
    if (!badge) return;
    badge.textContent = count;
    badge.classList.toggle('hidden', count <= 0);
}

function formatMoney(n) {
    return new Intl.NumberFormat('vi-VN').format(n) + 'đ';
}

export function initCartPage() {
    const page = document.getElementById('cart-page');
    if (!page) return;

    const updateUrl = page.dataset.updateUrl;
    const removeUrl = page.dataset.removeUrl;
    const clearUrl = page.dataset.clearUrl;
    const subtotalEl = document.getElementById('cart-subtotal');
    let debounceTimers = {};

    const refreshSubtotal = (data) => {
        if (subtotalEl && data.subtotal_formatted) {
            subtotalEl.textContent = data.subtotal_formatted;
        }
        if (data.count !== undefined) updateHeaderBadge(data.count);
    };

    const updateItemUI = (row, data) => {
        if (data.item) {
            const sub = row.querySelector('.item-subtotal');
            if (sub) sub.textContent = data.item.subtotal_formatted;
            const qty = row.querySelector('.qty-value');
            if (qty) qty.textContent = data.item.quantity;
        }
    };

    const scheduleUpdate = (row, quantity) => {
        const itemId = row.dataset.itemId;
        clearTimeout(debounceTimers[itemId]);
        debounceTimers[itemId] = setTimeout(async () => {
            const data = await cartRequest(updateUrl, { item_id: parseInt(itemId), quantity });
            refreshSubtotal(data);
            updateItemUI(row, data);
        }, 300);
    };

    page.addEventListener('click', async (e) => {
        const row = e.target.closest('.cart-row');
        if (!row) return;
        const itemId = parseInt(row.dataset.itemId);
        const qtyEl = row.querySelector('.qty-value');
        let qty = parseInt(qtyEl.textContent);

        if (e.target.closest('.qty-minus')) {
            if (qty > 1) {
                qty--;
                qtyEl.textContent = qty;
                scheduleUpdate(row, qty);
            }
        }

        if (e.target.closest('.qty-plus')) {
            qty++;
            qtyEl.textContent = qty;
            scheduleUpdate(row, qty);
        }

        if (e.target.closest('.remove-item')) {
            const data = await cartRequest(removeUrl, { item_id: itemId });
            row.remove();
            refreshSubtotal(data);
            if (data.empty) location.reload();
        }
    });

    const clearBtn = document.getElementById('clear-cart-btn');
    if (clearBtn) {
        clearBtn.addEventListener('click', async () => {
            if (!confirm('Bạn có chắc chắn muốn xóa toàn bộ giỏ hàng?')) return;
            const data = await cartRequest(clearUrl, {});
            refreshSubtotal(data);
            location.reload();
        });
    }
}
