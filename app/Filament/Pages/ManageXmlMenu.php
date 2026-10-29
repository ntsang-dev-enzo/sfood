<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use DOMDocument;
use DOMXPath;
use App\Models\Product;

class ManageXmlMenu extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';
    protected static ?string $navigationLabel = 'Quản lý XML & DTD';
    protected static ?string $title = 'Quản lý Dữ Liệu XML & DTD (breakfast-menu.xml)';
    protected static ?string $navigationGroup = 'Quản lý dữ liệu XML';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.manage-xml-menu';

    public string $xmlPath = '';
    public string $dtdPath = '';
    public string $xmlContent = '';
    public string $dtdContent = '';
    public array $foodItems = [];
    public array $restaurantInfo = [];
    public string $validationStatus = '';
    public bool $isValid = false;
    public string $activeTab = 'items';

    // Form thêm món mới
    public string $newId = '';
    public string $newName = '';
    public string $newCategory = 'mon_an_sang';
    public string $newPrice = '';
    public string $newUnit = 'Phần';
    public string $newCalories = '350 kcal';
    public string $newDescription = '';
    public string $newOrigin = 'Đà Lạt';
    public string $newPrepTime = '10 phút';

    public function mount(): void
    {
        $this->xmlPath = public_path('xml/breakfast-menu.xml');
        $this->dtdPath = public_path('xml/breakfast-menu.dtd');

        $this->loadFiles();
        $this->validateXml();
        $this->parseFoodItems();
        $this->suggestNextId();
    }

    public function loadFiles(): void
    {
        if (file_exists($this->xmlPath)) {
            $this->xmlContent = file_get_contents($this->xmlPath);
        }
        if (file_exists($this->dtdPath)) {
            $this->dtdContent = file_get_contents($this->dtdPath);
        }
    }

    public function validateXml(): void
    {
        if (!file_exists($this->xmlPath)) {
            $this->isValid = false;
            $this->validationStatus = 'Tệp XML không tồn tại tại: ' . $this->xmlPath;
            return;
        }

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $loaded = $dom->load($this->xmlPath);

        if (!$loaded) {
            $this->isValid = false;
            $errors = libxml_get_errors();
            $this->validationStatus = 'Cú pháp XML không hợp lệ: ' . ($errors[0]->message ?? 'Lỗi không xác định');
            libxml_clear_errors();
            return;
        }

        if ($dom->validate()) {
            $this->isValid = true;
            $this->validationStatus = 'Hợp lệ 100%! Tệp XML tuân thủ hoàn toàn quy tắc định danh duy nhất (ID) và cấu trúc cây DTD.';
        } else {
            $this->isValid = false;
            $errors = libxml_get_errors();
            $errorMsgs = [];
            foreach ($errors as $err) {
                $errorMsgs[] = "Dòng {$err->line}: {$err->message}";
            }
            $this->validationStatus = 'Không vượt qua kiểm tra DTD: ' . implode(' | ', $errorMsgs);
            libxml_clear_errors();
        }
    }

    public function parseFoodItems(): void
    {
        $this->foodItems = [];
        $this->restaurantInfo = [];

        if (!file_exists($this->xmlPath)) return;

        $dom = new DOMDocument();
        if (!@$dom->load($this->xmlPath)) return;

        // Lấy thông tin restaurant
        $rests = $dom->getElementsByTagName('restaurant');
        if ($rests->length > 0) {
            $rest = $rests->item(0);
            $this->restaurantInfo = [
                'name' => $rest->getElementsByTagName('name')->item(0)?->nodeValue ?? '',
                'phone' => $rest->getElementsByTagName('phone')->item(0)?->nodeValue ?? '',
                'address' => $rest->getElementsByTagName('address')->item(0)?->nodeValue ?? '',
                'website' => $rest->getElementsByTagName('website')->item(0)?->nodeValue ?? '',
            ];
        }

        // Lấy danh sách food items
        $foods = $dom->getElementsByTagName('food');
        foreach ($foods as $food) {
            $this->foodItems[] = [
                'id' => $food->getAttribute('id'),
                'category' => $food->getAttribute('category'),
                'status' => $food->getAttribute('status') ?: 'available',
                'name' => $food->getElementsByTagName('name')->item(0)?->nodeValue ?? '',
                'price' => $food->getElementsByTagName('price')->item(0)?->nodeValue ?? '0',
                'unit' => $food->getElementsByTagName('unit')->item(0)?->nodeValue ?? '',
                'calories' => $food->getElementsByTagName('calories')->item(0)?->nodeValue ?? '',
                'description' => $food->getElementsByTagName('description')->item(0)?->nodeValue ?? '',
                'origin' => $food->getElementsByTagName('origin')->item(0)?->nodeValue ?? '',
                'preparation_time' => $food->getElementsByTagName('preparation_time')->item(0)?->nodeValue ?? '',
            ];
        }
    }

    protected function suggestNextId(): void
    {
        $maxNum = 0;
        foreach ($this->foodItems as $item) {
            if (preg_match('/^f(\d+)$/i', $item['id'], $m)) {
                $num = intval($m[1]);
                if ($num > $maxNum) $maxNum = $num;
            }
        }
        $next = $maxNum + 1;
        $this->newId = sprintf('f%02d', $next);
    }

    public function runValidationCheck(): void
    {
        $this->validateXml();

        if ($this->isValid) {
            Notification::make()
                ->title('Xác thực XML & DTD thành công')
                ->body('Tệp XML hoàn toàn hợp lệ theo định nghĩa DTD và quy tắc ID duy nhất.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Kiểm tra DTD thất bại')
                ->body($this->validationStatus)
                ->danger()
                ->send();
        }
    }

    public function saveXmlRaw(): void
    {
        try {
            file_put_contents($this->xmlPath, $this->xmlContent);
            // Cập nhật bản sao
            @file_put_contents(public_path('xml/sfood-menu.xml'), $this->xmlContent);

            $this->validateXml();
            $this->parseFoodItems();

            Notification::make()
                ->title('Đã lưu tệp XML')
                ->body('Nội dung XML đã được cập nhật thành công.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Lỗi lưu XML')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function saveDtdRaw(): void
    {
        try {
            file_put_contents($this->dtdPath, $this->dtdContent);
            @file_put_contents(public_path('xml/sfood-menu.dtd'), $this->dtdContent);

            $this->validateXml();

            Notification::make()
                ->title('Đã lưu tệp DTD')
                ->body('Nội dung DTD đã được cập nhật thành công.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Lỗi lưu DTD')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function addFoodItem(): void
    {
        $this->newId = trim($this->newId);
        $this->newName = trim($this->newName);

        if (!$this->newId || !$this->newName || !$this->newPrice) {
            Notification::make()
                ->title('Thiếu dữ liệu')
                ->body('Vui lòng nhập đầy đủ Mã ID, Tên món và Đơn giá.')
                ->warning()
                ->send();
            return;
        }

        // Kiểm tra tính duy nhất của ID theo quy tắc DTD
        foreach ($this->foodItems as $item) {
            if (strtolower($item['id']) === strtolower($this->newId)) {
                Notification::make()
                    ->title('ID bị trùng lặp!')
                    ->body("Mã định danh ID '{$this->newId}' đã tồn tại trong file XML. Thuộc tính ID trong DTD phải là duy nhất.")
                    ->danger()
                    ->send();
                return;
            }
        }

        try {
            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->load($this->xmlPath);

            $root = $dom->documentElement;

            $food = $dom->createElement('food');
            $food->setAttribute('id', $this->newId);
            $food->setAttribute('category', $this->newCategory);
            $food->setAttribute('status', 'available');

            $food->appendChild($dom->createElement('name', htmlspecialchars($this->newName)));
            $food->appendChild($dom->createElement('price', intval($this->newPrice)));
            $food->appendChild($dom->createElement('unit', htmlspecialchars($this->newUnit ?: 'Kg')));
            $food->appendChild($dom->createElement('calories', htmlspecialchars($this->newCalories ?: '300 kcal')));
            $food->appendChild($dom->createElement('description', htmlspecialchars($this->newDescription ?: 'Món ngon tươi sạch tại SFood')));
            $food->appendChild($dom->createElement('origin', htmlspecialchars($this->newOrigin ?: 'Việt Nam')));
            $food->appendChild($dom->createElement('preparation_time', htmlspecialchars($this->newPrepTime ?: '10 phút')));
            $food->appendChild($dom->createElement('image', '/logo.png'));

            $root->appendChild($food);
            $dom->save($this->xmlPath);
            @file_put_contents(public_path('xml/sfood-menu.xml'), $dom->saveXML());

            $this->loadFiles();
            $this->validateXml();
            $this->parseFoodItems();
            $this->suggestNextId();

            // Reset form
            $this->newName = '';
            $this->newPrice = '';
            $this->newDescription = '';

            Notification::make()
                ->title('Thêm món thành công!')
                ->body("Đã chèn nút <food id=\"{$this->newId}\"> vào cây XML DOM.")
                ->success()
                ->send();

        } catch (\Throwable $e) {
            Notification::make()
                ->title('Lỗi thêm món')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function deleteFoodItem(string $id): void
    {
        try {
            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->load($this->xmlPath);

            $xpath = new DOMXPath($dom);
            $nodes = $xpath->query("//food[@id='$id']");

            if ($nodes->length > 0) {
                $node = $nodes->item(0);
                $node->parentNode->removeChild($node);
                $dom->save($this->xmlPath);
                @file_put_contents(public_path('xml/sfood-menu.xml'), $dom->saveXML());

                $this->loadFiles();
                $this->validateXml();
                $this->parseFoodItems();
                $this->suggestNextId();

                Notification::make()
                    ->title('Đã xóa món khỏi XML')
                    ->body("Đã gỡ bỏ phần tử có ID '{$id}' khỏi tài liệu XML.")
                    ->success()
                    ->send();
            }
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Lỗi khi xóa')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function syncFromProductsDatabase(): void
    {
        try {
            $products = Product::where('is_active', true)->with('category')->limit(12)->get();
            if ($products->isEmpty()) {
                Notification::make()->title('Không có sản phẩm')->body('Không có sản phẩm nào trong database để đồng bộ.')->warning()->send();
                return;
            }

            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;

            $implementation = new \DOMImplementation();
            $dtd = $implementation->createDocumentType('breakfast_menu', '', 'breakfast-menu.dtd');
            $dom = $implementation->createDocument('', '', $dtd);
            $dom->encoding = 'UTF-8';
            $dom->formatOutput = true;

            $root = $dom->createElement('breakfast_menu');

            // Restaurant
            $rest = $dom->createElement('restaurant');
            $rest->appendChild($dom->createElement('name', 'SFood - Nông Sản & Thực Phẩm Tươi Sống'));
            $rest->appendChild($dom->createElement('phone', '1900 6868'));
            $rest->appendChild($dom->createElement('address', '123 Đường Nông Nghiệp Xanh, TP. Hồ Chí Minh'));
            $rest->appendChild($dom->createElement('website', 'https://sfood.vn'));
            $root->appendChild($rest);

            $catMap = [
                'rau-cu' => 'rau_cu',
                'thit-ca' => 'thit_tuoi',
                'hai-san' => 'hai_san',
                'trai-cay' => 'trai_cay',
            ];

            $counter = 1;
            foreach ($products as $p) {
                $id = sprintf('f%02d', $counter++);
                $catSlug = $p->category?->slug ?? 'rau-cu';
                $xmlCat = $catMap[$catSlug] ?? 'rau_cu';

                $food = $dom->createElement('food');
                $food->setAttribute('id', $id);
                $food->setAttribute('category', $xmlCat);
                $food->setAttribute('status', 'available');

                $food->appendChild($dom->createElement('name', htmlspecialchars($p->name)));
                $food->appendChild($dom->createElement('price', intval($p->minPrice())));
                $food->appendChild($dom->createElement('unit', 'Kg'));
                $food->appendChild($dom->createElement('calories', '120 kcal'));
                $food->appendChild($dom->createElement('description', htmlspecialchars($p->short_description ?: 'Nông sản tươi sạch tiêu chuẩn VietGAP')));
                $food->appendChild($dom->createElement('origin', 'Đà Lạt'));
                $food->appendChild($dom->createElement('preparation_time', '5-10 phút'));
                $food->appendChild($dom->createElement('image', '/logo.png'));

                $root->appendChild($food);
            }

            $dom->appendChild($root);
            $dom->save($this->xmlPath);
            @file_put_contents(public_path('xml/sfood-menu.xml'), $dom->saveXML());

            $this->loadFiles();
            $this->validateXml();
            $this->parseFoodItems();
            $this->suggestNextId();

            Notification::make()
                ->title('Đồng bộ từ MySQL sang XML thành công!')
                ->body('Đã nạp ' . count($products) . ' sản phẩm từ cơ sở dữ liệu MySQL vào breakfast-menu.xml.')
                ->success()
                ->send();

        } catch (\Throwable $e) {
            Notification::make()
                ->title('Lỗi đồng bộ')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
