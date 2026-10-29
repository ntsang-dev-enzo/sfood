const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content;

function setFavoriteState(btn, favorited) {
    btn.dataset.favorited = favorited ? 'true' : 'false';
    btn.classList.toggle('btn-danger', favorited);
    btn.classList.toggle('text-white', favorited);
    btn.classList.toggle('btn-outline-danger', !favorited);

    const icon = btn.querySelector('.favorite-icon');
    if (icon) {
        icon.innerHTML = favorited ? '<i class="bi bi-heart-fill text-danger"></i>' : '<i class="bi bi-heart"></i>';
    }
}

async function handleFavoriteClick(btn) {
    if (btn.dataset.authenticated !== 'true') {
        window.location.href = btn.dataset.loginUrl;
        return;
    }

    btn.disabled = true;

    try {
        const res = await fetch(btn.dataset.url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf(),
            },
        });

        if (res.status === 401) {
            window.location.href = btn.dataset.loginUrl;
            return;
        }

        if (!res.ok) {
            throw new Error('Request failed');
        }

        const data = await res.json();
        setFavoriteState(btn, data.favorited);
    } catch {
        alert('Không thể cập nhật yêu thích. Vui lòng thử lại.');
    } finally {
        btn.disabled = false;
    }
}

export function initFavoriteButton() {
    document.querySelectorAll('.favorite-btn').forEach((btn) => {
        if (btn.dataset.favoriteBound === 'true') {
            return;
        }

        btn.dataset.favoriteBound = 'true';
        btn.addEventListener('click', () => handleFavoriteClick(btn));
    });
}
