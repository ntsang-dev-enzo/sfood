# SFood - Nông Sản & Thực Phẩm Tươi Sống Sạch

> **Đồ Án Môn Học:** Thiết Kế & Lập Trình Web  
> **Kiến trúc:** Laravel MVC + Bootstrap 5 + MySQL + Quản Lý Dữ Liệu XML & DTD  
> **Tương thích máy chủ:** Apache (XAMPP) / IIS Server / PHP Built-in Server  

---

## 1. Giới Thiệu Dự Án

**SFood** là hệ thống thương mại điện tử chuyên cung cấp nông sản sạch, thực phẩm tươi sống đạt tiêu chuẩn VietGAP và công thức món ngon gia đình. Dự án được xây dựng và hoàn thiện theo chuẩn giao diện hiện đại **Bootstrap 5**, tích hợp công nghệ **xử lý dữ liệu XML & DTD**, nạp dữ liệu bất đồng bộ qua **AJAX (XMLHttpRequest)** và duyệt cây phần tử **XML DOM**.

Dự án tuân thủ nghiêm ngặt các tiêu chí:
- **Không sử dụng Tailwind CSS:** Chuyển đổi 100% sang framework Bootstrap 5.3.3.
- **Không sử dụng AI:** Loại bỏ toàn bộ vi dịch vụ AI, thay thế chatbot tự động bằng kênh hỗ trợ trực tuyến LiveChat với nhân viên thật (Human Support).
- **Không sử dụng Emoji:** Toàn bộ biểu tượng cảm xúc được thay thế bằng hệ thống biểu tượng chuẩn **Bootstrap Icons (`bi bi-...`)**.
- **Responsive toàn diện:** Tối ưu hiển thị hoàn hảo trên máy tính, máy tính bảng và điện thoại di động (đặc biệt cấu hình hiển thị **3 sản phẩm trên 1 hàng** trên màn hình di động).

---

## 2. Các Yêu Cầu Đã Hoàn Thiện

### Phần 1: Giao Diện Người Dùng (Bootstrap 5)
- **Hệ thống Grid & Breakpoints:** Sử dụng Container, Row, Col 12 cột linh hoạt đảm bảo tính Responsive trên mọi kích thước màn hình.
- **3 sản phẩm / 1 hàng trên điện thoại:** Áp dụng lớp `row-cols-3` kết hợp tùy biến kích thước chữ, khoảng cách đệm và huy hiệu giúp card sản phẩm hiển thị gọn gàng, sắc nét trên thiết bị cầm tay.
- **Thanh điều hướng (Navbar):** 
  - Tích hợp thanh tìm kiếm sản phẩm với cơ chế gợi ý tự động (Autocomplete).
  - Menu đa cấp dạng Dropdown danh mục sản phẩm (Mega Menu 3 cột đồng bộ CSDL) và menu tài khoản người dùng.
- **Banner chuyển động (Carousel):** 
  - Kích thước đồng bộ 100% theo tỷ lệ chuẩn 16:9 (`440px` trên desktop, `340px` trên tablet, `220px` trên mobile).
  - Bố cục Bento Grid kết hợp thẻ quảng cáo khuyến mãi (Promo Cards) bên cạnh.
- **Thẻ hiển thị (Cards):** Thiết kế Card sản phẩm và Card công thức món ăn chuyên nghiệp, chuẩn phong cách E-commerce.
- **Thành phần Offcanvas & Collapse:**
  - **Offcanvas Cart:** Giỏ hàng trượt từ cạnh phải, hỗ trợ cập nhật số lượng và tính tiền trực tiếp bằng AJAX.
  - **Offcanvas Menu:** Thanh menu trượt trên thiết bị di động.
  - **Accordion / Collapse:** Triển khai tại trang Hỏi đáp thường gặp (FAQ).
- **Chân trang (Footer):** Bố cục 4 cột kèm Logo thương hiệu tự thiết kế và đầy đủ thông tin pháp lý, liên hệ.

---

### Phần 2: Chức Năng Hệ Thống (PHP + MySQL + XML)
- **Cơ sở dữ liệu (MySQL):**
  - Quản lý đầy đủ các bảng dữ liệu: Users, Categories, Products, ProductVariants, ProductImages, FlashSales, Orders, OrderItems, ShippingAddresses, Coupons, UserCoupons, Recipes, RecipeIngredients, ProductReviews, ChatMessages.
  - Thực hiện trọn vẹn các thao tác CRUD từ PHP đến MySQL.
- **Quản lý dữ liệu bằng XML & DTD (Nội dung trọng tâm):**
  - **Tệp XML thực tế:** `public/xml/breakfast-menu.xml` quản lý danh mục thực đơn món ăn.
  - **Tệp định nghĩa DTD:** `public/xml/breakfast-menu.dtd` với quy tắc định danh duy nhất `<!ATTLIST food id ID #REQUIRED>` và cấu trúc cây thẻ hợp lệ.
  - **Xác thực tự động:** Xác thực tính hợp lệ của cây XML thông qua phương thức `DOMDocument::validate()` của PHP.
  - **Mục quản trị riêng trong Filament Admin:**
    - Đường dẫn: `/admin/manage-xml-menu`
    - Kiểm tra trạng thái DTD thời gian thực.
    - Duyệt danh sách các thẻ `<food>` trong XML.
    - Thêm món mới vào XML có kiểm tra trùng lặp thuộc tính `id`.
    - Trình soạn thảo trực tiếp mã nguồn `breakfast-menu.xml` và `breakfast-menu.dtd`.
    - Đồng bộ dữ liệu 1 chạm từ cơ sở dữ liệu MySQL sang XML.

---

### Phần 3: Tương Tác JavaScript & AJAX
- **Kiểm tra dữ liệu biểu mẫu (Form Validation):**
  - Tệp `public/js/form-validation.js` thực hiện kiểm tra định dạng email, độ dài mật khẩu ($\ge 6$ ký tự), mật khẩu xác nhận và số điện thoại Việt Nam trước khi gửi dữ liệu lên máy chủ.
- **Xử lý XML DOM với XMLHttpRequest:**
  - Tệp `public/js/xml-loader.js` sử dụng đối tượng `XMLHttpRequest` để nạp tệp `breakfast-menu.xml` mà **không cần tải lại trang**.
  - Áp dụng các hàm xử lý nút DOM chuẩn:
    - `node.firstChild`: Duyệt phần tử con đầu tiên.
    - `node.nextSibling`: Duyệt các phần tử anh em kế tiếp trong cây XML.
  - Hiển thị bảng dữ liệu động tại trang client `/thuc-don-xml` kèm bộ lọc danh mục và ô tìm kiếm tức thì.

---

### Phần 4: Tính Năng Thương Mại Điện Tử Nổi Bật
- **Biến thể sản phẩm:** Chọn phân loại khối lượng/đóng gói, tự động cập nhật giá bán, giá gốc và hình ảnh tương ứng.
- **Flash Sale Giờ Vàng:** Hiển thị đồng hồ đếm ngược thời gian thực (Countdown Timer) và thanh tiến độ số lượng đã bán.
- **Công thức món ngon (Recipes):** Hướng dẫn nấu ăn chi tiết kèm nút *"Mua nhanh tất cả nguyên liệu"* đưa toàn bộ thành phần vào giỏ hàng.
- **Sổ địa chỉ nhiều cấp:** Chọn Tỉnh/Thành phố, Quận/Huyện, Phường/Xã tích hợp API địa giới hành chính.
- **Tính phí vận chuyển thông minh:** Tự động miễn phí giao hàng cho đơn giao tại nội thành Cần Thơ (Ninh Kiều, Cái Răng) hoặc đơn hàng từ 1.000.000₫.
- **Hệ thống Mã giảm giá (Coupons):** Hỗ trợ giảm theo %, giảm tiền cố định hoặc miễn phí vận chuyển.
- **Tích điểm thưởng (Reward Points):** Tích lũy điểm khi mua hàng và áp dụng giảm trừ trực tiếp vào đơn thanh toán.
- **Đánh giá & Xếp hạng sao:** Khách hàng đã mua hàng có thể gửi đánh giá, xếp hạng sao và đính kèm hình ảnh thực tế.
- **Kênh hỗ trợ trực tuyến (LiveChat):** Chat trực tiếp giữa người dùng và quản trị viên, có phân hệ xử lý tin nhắn riêng biệt trong Filament Admin.
- **Cổng thanh toán:** Hỗ trợ thanh toán tiền mặt khi nhận hàng (COD) và thanh toán quét mã QR chuyển khoản qua PayOS.

---

## 3. Cấu Trúc Thư Mục Dự Án

```
sfood/
├── app/
│   ├── Filament/                  # Phân hệ quản trị Filament 3
│   │   ├── Pages/
│   │   │   ├── ManageXmlMenu.php  # Mục quản lý XML & DTD riêng biệt trong Admin
│   │   │   └── LiveChat.php       # Giao diện chat hỗ trợ khách hàng
│   │   └── Resources/             # Quản lý Sản phẩm, Đơn hàng, Banner, FlashSale...
│   ├── Http/Controllers/          # Các bộ điều khiển MVC (Product, Order, Cart...)
│   ├── Models/                    # Eloquent Models kết nối CSDL MySQL
│   └── Services/                  # Nghiệp vụ Giỏ hàng, Mã giảm giá, Đơn hàng
├── database/
│   ├── migrations/                # Cấu trúc các bảng CSDL
│   └── seeders/                   # Dữ liệu mẫu (Admin, User, Sản phẩm...)
├── public/                        # Document Root của ứng dụng (Apache / IIS)
│   ├── bootstrap/                 # Thư viện Bootstrap 5.3.3 & Bootstrap Icons
│   ├── js/
│   │   ├── form-validation.js     # JavaScript kiểm tra dữ liệu form
│   │   └── xml-loader.js          # JavaScript nạp & duyệt cây XML DOM bằng AJAX
│   ├── xml/
│   │   ├── breakfast-menu.xml     # Tệp dữ liệu XML thực tế
│   │   └── breakfast-menu.dtd     # Tệp định nghĩa quy tắc DTD chuẩn ID
│   ├── logo.png                   # Logo thương hiệu nhóm tự thiết kế
│   ├── .htaccess                  # Cấu hình máy chủ Apache / XAMPP URL Rewrite
│   ├── web.config                 # Cấu hình máy chủ Windows IIS URL Rewrite
│   └── build/                     # Tài nguyên CSS/JS sau khi biên dịch Vite
├── resources/
│   ├── css/
│   │   └── app.css                # CSS giao diện Bootstrap 5 (Không dùng Tailwind)
│   ├── js/
│   │   └── app.js                 # File Javascript chính của ứng dụng
│   └── views/                     # Toàn bộ Blade Views (Bootstrap 5 Responsive)
│       ├── auth/                  # Đăng nhập, Đăng ký, Quên mật khẩu
│       ├── checkout/              # Trang thanh toán đơn hàng
│       ├── favorites/             # Danh sách sản phẩm & video yêu thích
│       ├── layouts/               # Layout App, Header, Footer, Offcanvas
│       ├── orders/                # Quản lý đơn hàng & đánh giá
│       ├── pages/                 # Trang chủ, Thực đơn XML, Giới thiệu, Liên hệ...
│       ├── partials/              # Card sản phẩm (3 cột/hàng trên mobile), Recipe Card...
│       └── products/              # Danh mục và chi tiết sản phẩm
├── routes/
│   └── web.php                    # Khai báo tuyến đường ứng dụng
├── package.json                   # Cấu hình thư viện Frontend
└── vite.config.js                 # Cấu hình biên dịch Vite
```

---

## 4. Hướng Dẫn Cài Đặt & Khởi Chạy

### Yêu cầu hệ thống
- **PHP:** Phiên bản 8.2 trở lên (bật các extension `pdo_mysql`, `curl`, `dom`, `libxml`, `fileinfo`).
- **Composer:** Phiên bản 2.x.
- **Node.js:** Phiên bản 18.x hoặc 20.x trở lên.
- **Hệ quản trị CSDL:** MySQL / MariaDB (hoặc XAMPP).
- **Máy chủ Web:** Apache (XAMPP), IIS, hoặc PHP Built-in Server.

---

### Các bước cài đặt chi tiết

#### Bước 1: Cài đặt các gói phụ thuộc PHP
Mở cửa sổ dòng lệnh tại thư mục gốc của dự án:
```bash
composer install
```

#### Bước 2: Cài đặt và biên dịch tài nguyên Frontend (Bootstrap 5)
```bash
npm install
npm run build
```

#### Bước 3: Cấu hình tệp môi trường `.env`
Sao chép tệp mẫu hoặc chỉnh sửa tệp `.env`:
```bash
cp .env.example .env
php artisan key:generate
```

Cấu hình thông số kết nối CSDL MySQL trong `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sfood
DB_USERNAME=root
DB_PASSWORD=
```

#### Bước 4: Tạo cơ sở dữ liệu và nạp dữ liệu mẫu
Tạo cơ sở dữ liệu `sfood` trong MySQL (qua phpMyAdmin hoặc MySQL CLI), sau đó chạy lệnh:
```bash
php artisan migrate --seed
php artisan storage:link
```

#### Bước 5: Khởi chạy máy chủ
```bash
php artisan serve
```
Truy cập website tại địa chỉ: `http://localhost:8000`

---

## 5. Tài Khoản Đăng Nhập Mặc Định

| Vai trò | Email đăng nhập | Mật khẩu | Quyền hạn |
| :--- | :--- | :--- | :--- |
| **Quản trị viên (Admin)** | `admin@sfood.vn` | `password` | Toàn quyền quản trị hệ thống, Filament Admin, quản lý XML & DTD |
| **Nhân viên (Staff)** | `staff@sfood.vn` | `password` | Quản lý sản phẩm, đơn hàng, trả lời LiveChat |
| **Khách hàng mẫu** | `user@sfood.vn` | `password` | Mua hàng, tích điểm, áp mã voucher, đánh giá sản phẩm |

---

## 6. Các Tuyến Đường Chính (Routes)

- **Trang chủ:** `/`
- **Danh sách sản phẩm:** `/san-pham`
- **Chi tiết sản phẩm:** `/san-pham/{slug}`
- **Bảng thực đơn XML (AJAX DOM):** `/thuc-don-xml`
- **Tệp XML gốc:** `/xml/breakfast-menu.xml`
- **Tệp DTD:** `/xml/breakfast-menu.dtd`
- **Công thức nấu ăn:** `/cong-thuc`
- **Trạm Voucher Khuyến Mãi:** `/tram-khuyen-mai`
- **Thanh toán đơn hàng:** `/thanh-toan`
- **Quản trị hệ thống (Filament Admin):** `/admin`
- **Quản lý XML & DTD trong Admin:** `/admin/manage-xml-menu`
- **Trang Giới thiệu:** `/gioi-thieu`
- **Trang Liên hệ:** `/lien-he`
- **Trang Hỏi đáp (Accordion FAQ):** `/hoi-dap`
- **Trang Chính sách:** `/chinh-sach`

---

## 7. Giấy Phép & Bản Quyền
Dự án được thực hiện phục vụ mục đích học tập môn **Thiết Kế & Lập Trình Web**. Toàn bộ mã nguồn được cấp phép theo tiêu chuẩn mở [MIT License](LICENSE).
