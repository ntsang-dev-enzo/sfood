const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content;

function setFavoriteState(btn, favorited) {
    btn.dataset.favorited = favorited ? 'true' : 'false';
    btn.classList.toggle('bg-red-50', favorited);
    btn.classList.toggle('border-red-300', favorited);
    btn.classList.toggle('text-red-500', favorited);
    btn.classList.toggle('border-gray-200', !favorited);
    btn.classList.toggle('bg-white', !favorited);
    btn.classList.toggle('hover:bg-gray-50', !favorited);

    const icon = btn.querySelector('.favorite-icon');
    if (icon) {
        icon.textContent = favorited ? '❤️' : '🤍';
    }
}

export function initFavoriteButton() {
    const btn = document.getElementById('favorite-btn');
    if (!btn) return;

    btn.addEventListener('click', async () => {
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
    });
}
