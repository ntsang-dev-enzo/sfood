<x-filament-panels::page>
    {{-- Thanh Trạng Thái & Xác Thực XML DTD --}}
    <div class="rounded-xl p-4 {{ $isValid ? 'bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800' }}">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $isValid ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }} shrink-0">
                    @if($isValid)
                        <x-heroicon-m-check-badge class="w-6 h-6" />
                    @else
                        <x-heroicon-m-exclamation-triangle class="w-6 h-6" />
                    @endif
                </div>
                <div>
                    <h4 class="font-bold text-sm {{ $isValid ? 'text-emerald-900 dark:text-emerald-200' : 'text-rose-900 dark:text-rose-200' }}">
                        {{ $isValid ? 'Tệp XML & DTD Hợp Lệ 100%' : 'Lỗi Xác Thực DTD / XML' }}
                    </h4>
                    <p class="text-xs {{ $isValid ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                        {{ $validationStatus }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <x-filament::button wire:click="runValidationCheck" size="sm" color="gray" icon="heroicon-m-arrow-path">
                    Kiểm tra DTD
                </x-filament::button>
                <x-filament::button wire:click="syncFromProductsDatabase" size="sm" color="warning" icon="heroicon-m-arrow-path-rounded-square">
                    Đồng bộ từ MySQL sang XML
                </x-filament::button>
                <x-filament::button tag="a" href="{{ url('/thuc-don-xml') }}" target="_blank" size="sm" color="success" icon="heroicon-m-arrow-top-right-on-square">
                    Mở trang Client AJAX DOM
                </x-filament::button>
            </div>
        </div>
    </div>

    {{-- Tabs Điều Hướng --}}
    <div class="flex border-b border-gray-200 dark:border-gray-800 gap-4 text-sm font-semibold">
        <button type="button" wire:click="$set('activeTab', 'items')" 
                class="pb-3 border-b-2 transition {{ $activeTab === 'items' ? 'border-amber-600 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
            Danh sách món trong XML ({{ count($foodItems) }})
        </button>
        <button type="button" wire:click="$set('activeTab', 'add')" 
                class="pb-3 border-b-2 transition {{ $activeTab === 'add' ? 'border-amber-600 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
            + Thêm món vào cây XML
        </button>
        <button type="button" wire:click="$set('activeTab', 'xml_editor')" 
                class="pb-3 border-b-2 transition {{ $activeTab === 'xml_editor' ? 'border-amber-600 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
            Sửa tệp breakfast-menu.xml
        </button>
        <button type="button" wire:click="$set('activeTab', 'dtd_editor')" 
                class="pb-3 border-b-2 transition {{ $activeTab === 'dtd_editor' ? 'border-amber-600 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
            Sửa tệp breakfast-menu.dtd
        </button>
    </div>

    {{-- NỘI DUNG TAB 1: DANH SÁCH MÓN TRONG XML --}}
    @if($activeTab === 'items')
        <div class="space-y-4">
            <x-filament::section>
                <x-slot name="heading">
                    Dữ liệu cây phần tử &lt;food&gt; trong file XML
                </x-slot>
                <x-slot name="description">
                    Duyệt trực tiếp từ tài liệu <code>breakfast-menu.xml</code>. Mỗi món có thuộc tính <code>id ID #REQUIRED</code> định danh duy nhất.
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm divide-y divide-gray-200 dark:divide-gray-800">
                        <thead>
                            <tr class="text-xs uppercase text-gray-500 bg-gray-50 dark:bg-gray-800/50">
                                <th class="p-3">Mã ID</th>
                                <th class="p-3">Tên món &amp; Mô tả</th>
                                <th class="p-3">Danh mục</th>
                                <th class="p-3">Đơn giá</th>
                                <th class="p-3">Đơn vị</th>
                                <th class="p-3">Calo</th>
                                <th class="p-3">Xuất xứ</th>
                                <th class="p-3">Chuẩn bị</th>
                                <th class="p-3 text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($foodItems as $item)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                    <td class="p-3 font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                        {{ $item['id'] }}
                                    </td>
                                    <td class="p-3">
                                        <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $item['name'] }}</div>
                                        <div class="text-xs text-gray-500 truncate max-w-xs">{{ $item['description'] }}</div>
                                    </td>
                                    <td class="p-3">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                            {{ $item['category'] }}
                                        </span>
                                    </td>
                                    <td class="p-3 font-bold text-rose-600">
                                        {{ number_format(intval($item['price'])) }}₫
                                    </td>
                                    <td class="p-3 text-gray-600 dark:text-gray-400">{{ $item['unit'] }}</td>
                                    <td class="p-3">{{ $item['calories'] }}</td>
                                    <td class="p-3 text-xs">{{ $item['origin'] }}</td>
                                    <td class="p-3 text-xs">{{ $item['preparation_time'] }}</td>
                                    <td class="p-3 text-right">
                                        <x-filament::button wire:click="deleteFoodItem('{{ $item['id'] }}')" 
                                                            wire:confirm="Bạn có chắc muốn xóa món {{ $item['id'] }} khỏi file XML?" 
                                                            size="xs" color="danger" icon="heroicon-m-trash">
                                            Xóa
                                        </x-filament::button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="p-6 text-center text-gray-500">Chưa có phần tử nào trong file XML.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>
    @endif

    {{-- NỘI DUNG TAB 2: THÊM MÓN VÀO XML --}}
    @if($activeTab === 'add')
        <x-filament::section>
            <x-slot name="heading">
                Thêm phần tử &lt;food&gt; mới vào tệp XML
            </x-slot>
            <x-slot name="description">
                Hệ thống tự động kiểm tra quy tắc định danh duy nhất (ID) không được trùng lặp với các phần tử khác trong tài liệu.
            </x-slot>

            <form wire:submit="addFoodItem" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Mã định danh ID (duy nhất) <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="newId" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm font-mono" placeholder="f09" required>
                        <p class="text-xs text-gray-500 mt-1">Phải bắt đầu bằng chữ cái, không dấu cách (vd: f09, f10...)</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Danh mục DTD <span class="text-rose-500">*</span></label>
                        <select wire:model="newCategory" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm" required>
                            <option value="mon_an_sang">Món Ăn Sáng (mon_an_sang)</option>
                            <option value="rau_cu">Rau Củ Hữu Cơ (rau_cu)</option>
                            <option value="thit_tuoi">Thịt Tươi (thit_tuoi)</option>
                            <option value="hai_san">Hải Sản Tươi Sống (hai_san)</option>
                            <option value="trai_cay">Trái Cây Tươi (trai_cay)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Đơn giá (VNĐ) <span class="text-rose-500">*</span></label>
                        <input type="number" wire:model="newPrice" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm" placeholder="45000" min="0" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold mb-1">Tên món ăn <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="newName" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm" placeholder="Ví dụ: Salad Ức Gà Nướng Rau Xanh" required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Đơn vị tính</label>
                        <input type="text" wire:model="newUnit" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm" placeholder="Đĩa / Phần / Kg">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Năng lượng (Calories)</label>
                        <input type="text" wire:model="newCalories" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm" placeholder="320 kcal">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Nguồn gốc / Xuất xứ</label>
                        <input type="text" wire:model="newOrigin" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm" placeholder="Đà Lạt, Lâm Đồng">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Thời gian chuẩn bị</label>
                        <input type="text" wire:model="newPrepTime" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm" placeholder="10 phút">
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-sm font-semibold mb-1">Mô tả món ăn</label>
                        <textarea wire:model="newDescription" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm" placeholder="Mô tả hương vị, nguyên liệu thành phần..."></textarea>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <x-filament::button type="submit" color="success" icon="heroicon-m-plus">
                        Thêm vào tệp breakfast-menu.xml
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>
    @endif

    {{-- NỘI DUNG TAB 3: TRÌNH BIÊN TẬP TRỰC TIẾP XML --}}
    @if($activeTab === 'xml_editor')
        <x-filament::section>
            <x-slot name="heading">
                Chỉnh sửa mã nguồn XML (breakfast-menu.xml)
            </x-slot>
            <x-slot name="description">
                Đường dẫn: <code>{{ $xmlPath }}</code>. Sau khi lưu, hệ thống sẽ tự động chạy <code>DOMDocument::validate()</code> để kiểm tra tính hợp lệ.
            </x-slot>

            <form wire:submit="saveXmlRaw" class="space-y-4">
                <textarea wire:model="xmlContent" rows="18" 
                          class="w-full font-mono text-xs p-4 rounded-lg bg-gray-900 text-emerald-400 border border-gray-700 focus:ring-emerald-500 focus:border-emerald-500 leading-relaxed"></textarea>

                <div class="flex justify-between items-center">
                    <a href="{{ url('/xml/breakfast-menu.xml') }}" target="_blank" class="text-xs text-amber-600 dark:text-amber-400 hover:underline">
                        Mở liên kết tệp XML gốc trong tab mới &rarr;
                    </a>
                    <x-filament::button type="submit" color="primary" icon="heroicon-m-check">
                        Lưu tệp breakfast-menu.xml
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>
    @endif

    {{-- NỘI DUNG TAB 4: TRÌNH BIÊN TẬP QUY TẮC DTD --}}
    @if($activeTab === 'dtd_editor')
        <x-filament::section>
            <x-slot name="heading">
                Chỉnh sửa quy tắc Document Type Definition (breakfast-menu.dtd)
            </x-slot>
            <x-slot name="description">
                Đường dẫn: <code>{{ $dtdPath }}</code>. Định nghĩa cấu trúc thẻ và ràng buộc thuộc tính <code>ID #REQUIRED</code>.
            </x-slot>

            <form wire:submit="saveDtdRaw" class="space-y-4">
                <textarea wire:model="dtdContent" rows="18" 
                          class="w-full font-mono text-xs p-4 rounded-lg bg-gray-900 text-amber-300 border border-gray-700 focus:ring-amber-500 focus:border-amber-500 leading-relaxed"></textarea>

                <div class="flex justify-between items-center">
                    <a href="{{ url('/xml/breakfast-menu.dtd') }}" target="_blank" class="text-xs text-amber-600 dark:text-amber-400 hover:underline">
                        Mở liên kết tệp DTD trong tab mới &rarr;
                    </a>
                    <x-filament::button type="submit" color="warning" icon="heroicon-m-check">
                        Lưu tệp breakfast-menu.dtd
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>
    @endif
</x-filament-panels::page>
