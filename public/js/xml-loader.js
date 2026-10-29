/**
 * XML DOM Loader & Parser using XMLHttpRequest & DOM Node traversal (firstChild, nextSibling)
 * SFood Organic - BTL Thiết kế & Lập trình Web
 */

document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.getElementById('xml-menu-tbody');
    if (!tableBody) return;

    let cachedFoodItems = [];
    let restaurantMeta = {};

    const categorySelect = document.getElementById('xml-category-filter');
    const searchInput = document.getElementById('xml-search-input');
    const reloadBtn = document.getElementById('xml-reload-btn');

    // Nạp dữ liệu XML bằng XMLHttpRequest
    function loadXMLData() {
        const loadingIndicator = document.getElementById('xml-loading');
        if (loadingIndicator) loadingIndicator.classList.remove('d-none');
        tableBody.innerHTML = `<tr><td colspan="9" class="text-center py-4 text-muted"><div class="spinner-border text-success spinner-border-sm me-2"></div>Đang nạp dữ liệu từ breakfast-menu.xml bằng XMLHttpRequest...</td></tr>`;

        const xhr = new XMLHttpRequest();
        // File XML thực tế tại thư mục /xml/breakfast-menu.xml
        xhr.open('GET', '/xml/breakfast-menu.xml', true);
        xhr.setRequestHeader('Cache-Control', 'no-cache');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200 || xhr.status === 0) {
                    const xmlDoc = xhr.responseXML;
                    if (!xmlDoc) {
                        showError('Không thể phân tích tệp XML. Vui lòng kiểm tra định dạng!');
                        return;
                    }
                    parseXMLDOM(xmlDoc);
                    renderTable(cachedFoodItems);
                    updateRestaurantHeader();
                    if (loadingIndicator) loadingIndicator.classList.add('d-none');
                } else {
                    showError('Lỗi nạp file XML qua AJAX (Mã lỗi: ' + xhr.status + ')');
                }
            }
        };

        xhr.onerror = function () {
            showError('Lỗi kết nối XMLHttpRequest khi tải XML.');
        };

        xhr.send();
    }

    /**
     * Vận dụng các hàm xử lý nút (node) như firstChild, nextSibling
     * để duyệt và trích xuất dữ liệu XML DOM trên trình duyệt
     */
    function parseXMLDOM(xmlDoc) {
        cachedFoodItems = [];
        restaurantMeta = {};

        const root = xmlDoc.documentElement; // <breakfast_menu>
        if (!root) return;

        // 1. Duyệt thông tin nhà hàng bằng firstChild và nextSibling
        let node = root.firstChild;
        while (node) {
            if (node.nodeType === 1 && node.nodeName === 'restaurant') {
                let restChild = node.firstChild;
                while (restChild) {
                    if (restChild.nodeType === 1) {
                        let text = restChild.firstChild ? restChild.firstChild.nodeValue.trim() : restChild.textContent.trim();
                        restaurantMeta[restChild.nodeName] = text;
                    }
                    restChild = restChild.nextSibling;
                }
                break;
            }
            node = node.nextSibling;
        }

        // 2. Duyệt danh sách các món ăn <food> bằng firstChild & nextSibling
        let foodNode = root.firstChild;
        while (foodNode) {
            // Kiểm tra nodeType === 1 (ELEMENT_NODE) và tên node là 'food'
            if (foodNode.nodeType === 1 && foodNode.nodeName === 'food') {
                const item = {
                    id: foodNode.getAttribute('id') || '',
                    category: foodNode.getAttribute('category') || '',
                    status: foodNode.getAttribute('status') || 'available',
                };

                // Duyệt qua tất cả các phần tử con của <food> (name, price, unit, calories, description, origin, preparation_time, image)
                let propNode = foodNode.firstChild;
                while (propNode) {
                    if (propNode.nodeType === 1) {
                        const propName = propNode.nodeName;
                        // Lấy giá trị chuỗi qua firstChild.nodeValue
                        let propVal = '';
                        if (propNode.firstChild && propNode.firstChild.nodeType === 3) { // TEXT_NODE
                            propVal = propNode.firstChild.nodeValue.trim();
                        } else {
                            propVal = propNode.textContent.trim();
                        }
                        item[propName] = propVal;
                    }
                    // Chuyển sang nút kế tiếp (nextSibling)
                    propNode = propNode.nextSibling;
                }

                cachedFoodItems.push(item);
            }
            // Chuyển sang nút con kế tiếp của root (nextSibling)
            foodNode = foodNode.nextSibling;
        }
    }

    // Hiển thị thông tin header nhà hàng
    function updateRestaurantHeader() {
        const titleEl = document.getElementById('xml-restaurant-name');
        const phoneEl = document.getElementById('xml-restaurant-phone');
        const addrEl = document.getElementById('xml-restaurant-address');
        if (titleEl && restaurantMeta.name) titleEl.textContent = restaurantMeta.name;
        if (phoneEl && restaurantMeta.phone) phoneEl.textContent = 'Hotline: ' + restaurantMeta.phone;
        if (addrEl && restaurantMeta.address) addrEl.textContent = 'Địa chỉ: ' + restaurantMeta.address;
    }

    // Render bảng dữ liệu XML bằng Bootstrap 5
    function renderTable(items) {
        if (!items || items.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="9" class="text-center py-4 text-muted">Không tìm thấy món nào phù hợp với bộ lọc.</td></tr>`;
            updateStats(0, 0);
            return;
        }

        let html = '';
        let totalPrice = 0;

        items.forEach(function (item, index) {
            const priceNum = parseInt(item.price, 10) || 0;
            totalPrice += priceNum;

            const categoryLabels = {
                'mon_an_sang': { text: 'Món Ăn Sáng', class: 'bg-warning text-dark' },
                'rau_cu': { text: 'Rau Củ Hữu Cơ', class: 'bg-success text-white' },
                'thit_tuoi': { text: 'Thịt Tươi', class: 'bg-danger text-white' },
                'hai_san': { text: 'Hải Sản Tươi', class: 'bg-primary text-white' },
                'trai_cay': { text: 'Trái Cây', class: 'bg-info text-dark' }
            };

            const catInfo = categoryLabels[item.category] || { text: item.category, class: 'bg-secondary text-white' };
            const statusBadge = item.status === 'available' 
                ? '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Còn hàng</span>' 
                : '<span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i>Hết hàng</span>';

            html += `
                <tr id="row-${item.id}">
                    <td class="text-center fw-bold text-success">${item.id}</td>
                    <td>
                        <div class="fw-semibold text-dark">${escapeHtml(item.name || '')}</div>
                        <small class="text-muted d-block text-truncate" style="max-width: 280px;">${escapeHtml(item.description || '')}</small>
                    </td>
                    <td><span class="badge ${catInfo.class}">${catInfo.text}</span></td>
                    <td class="fw-bold text-danger">${formatCurrency(priceNum)}</td>
                    <td class="text-muted">${escapeHtml(item.unit || '')}</td>
                    <td><span class="badge bg-light text-dark border">${escapeHtml(item.calories || '-')}</span></td>
                    <td><small class="text-secondary"><i class="bi bi-geo-alt me-1"></i>${escapeHtml(item.origin || 'Việt Nam')}</small></td>
                    <td>${statusBadge}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-success btn-view-xml" data-id="${item.id}" title="Xem chi tiết DOM">
                            <i class="bi bi-eye"></i> Chi tiết
                        </button>
                    </td>
                </tr>
            `;
        });

        tableBody.innerHTML = html;
        updateStats(items.length, items.length > 0 ? Math.round(totalPrice / items.length) : 0);

        // Gắn sự kiện xem chi tiết
        document.querySelectorAll('.btn-view-xml').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const found = cachedFoodItems.find(function (f) { return f.id === id; });
                if (found) showDetailModal(found);
            });
        });
    }

    function updateStats(count, avgPrice) {
        const countEl = document.getElementById('xml-count');
        const avgEl = document.getElementById('xml-avg-price');
        if (countEl) countEl.textContent = count + ' món';
        if (avgEl) avgEl.textContent = formatCurrency(avgPrice);
    }

    function showDetailModal(item) {
        const modalTitle = document.getElementById('xmlModalLabel');
        const modalBody = document.getElementById('xmlModalBody');
        if (!modalTitle || !modalBody) return;

        modalTitle.innerHTML = `<i class="bi bi-file-earmark-code text-success me-2"></i>Nút XML [${item.id}]: ${escapeHtml(item.name)}`;
        modalBody.innerHTML = `
            <div class="card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-success fs-6">${item.category}</span>
                        <span class="text-danger fw-bold fs-5">${formatCurrency(item.price)} / ${escapeHtml(item.unit)}</span>
                    </div>
                    <p class="card-text text-muted mb-3">${escapeHtml(item.description)}</p>
                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item d-flex justify-content-between">
                            <strong><i class="bi bi-fire text-warning me-2"></i>Năng lượng (Calo):</strong>
                            <span>${escapeHtml(item.calories)}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong><i class="bi bi-geo-alt text-danger me-2"></i>Xuất xứ:</strong>
                            <span>${escapeHtml(item.origin)}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong><i class="bi bi-clock-history text-primary me-2"></i>Thời gian chuẩn bị:</strong>
                            <span>${escapeHtml(item.preparation_time || '5-10 phút')}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong><i class="bi bi-tag text-info me-2"></i>Trạng thái kho:</strong>
                            <span>${item.status === 'available' ? 'Còn hàng tươi' : 'Tạm hết'}</span>
                        </li>
                    </ul>
                    <div class="bg-light p-3 rounded font-monospace small">
                        <strong>Cấu trúc nút XML (node tree):</strong>
                        <pre class="mb-0 mt-1 text-primary">&lt;food id="${item.id}" category="${item.category}" status="${item.status}"&gt;
    &lt;name&gt;${escapeHtml(item.name)}&lt;/name&gt;
    &lt;price&gt;${item.price}&lt;/price&gt;
    &lt;unit&gt;${escapeHtml(item.unit)}&lt;/unit&gt;
    &lt;calories&gt;${escapeHtml(item.calories)}&lt;/calories&gt;
    &lt;description&gt;${escapeHtml(item.description)}&lt;/description&gt;
    &lt;origin&gt;${escapeHtml(item.origin)}&lt;/origin&gt;
    &lt;preparation_time&gt;${escapeHtml(item.preparation_time)}&lt;/preparation_time&gt;
&lt;/food&gt;</pre>
                    </div>
                </div>
            </div>
        `;

        const modalEl = document.getElementById('xmlDetailModal');
        if (modalEl && window.bootstrap) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }

    function filterData() {
        const cat = categorySelect ? categorySelect.value : '';
        const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';

        const filtered = cachedFoodItems.filter(function (item) {
            const matchCat = !cat || item.category === cat;
            const matchKeyword = !keyword || 
                (item.name && item.name.toLowerCase().includes(keyword)) ||
                (item.description && item.description.toLowerCase().includes(keyword)) ||
                (item.id && item.id.toLowerCase().includes(keyword));
            return matchCat && matchKeyword;
        });

        renderTable(filtered);
    }

    if (categorySelect) categorySelect.addEventListener('change', filterData);
    if (searchInput) searchInput.addEventListener('input', filterData);
    if (reloadBtn) reloadBtn.addEventListener('click', loadXMLData);

    function showError(msg) {
        tableBody.innerHTML = `<tr><td colspan="9" class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>${msg}</td></tr>`;
    }

    function formatCurrency(val) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Tự động nạp XML ban đầu
    loadXMLData();
});

