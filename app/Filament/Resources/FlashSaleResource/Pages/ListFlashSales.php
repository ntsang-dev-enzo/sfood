<?php

namespace App\Filament\Resources\FlashSaleResource\Pages;

use App\Filament\Resources\FlashSaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFlashSales extends ListRecords
{
    protected static string $resource = FlashSaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('bulk_flash_sale')
                ->label('Tạo Flash Sale Hàng Loạt')
                ->icon('heroicon-o-bolt')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\Radio::make('apply_to')
                        ->label('Áp dụng theo')
                        ->options([
                            'category' => 'Danh mục',
                            'product' => 'Sản phẩm',
                        ])
                        ->default('category')
                        ->reactive(),
                    \Filament\Forms\Components\Select::make('category_ids')
                        ->label('Chọn danh mục')
                        ->multiple()
                        ->options(\App\Models\Category::pluck('name', 'id'))
                        ->visible(fn (\Filament\Forms\Get $get) => $get('apply_to') === 'category')
                        ->required(fn (\Filament\Forms\Get $get) => $get('apply_to') === 'category'),
                    \Filament\Forms\Components\Select::make('product_ids')
                        ->label('Chọn sản phẩm')
                        ->multiple()
                        ->options(\App\Models\Product::pluck('name', 'id'))
                        ->searchable()
                        ->visible(fn (\Filament\Forms\Get $get) => $get('apply_to') === 'product')
                        ->required(fn (\Filament\Forms\Get $get) => $get('apply_to') === 'product'),
                    \Filament\Forms\Components\Radio::make('discount_type')
                        ->label('Loại giảm giá')
                        ->options([
                            'percent' => 'Phần trăm (%)',
                            'fixed' => 'Số tiền (đ)',
                        ])
                        ->default('percent')
                        ->inline(),
                    \Filament\Forms\Components\TextInput::make('discount_value')
                        ->label('Mức giảm')
                        ->numeric()
                        ->required()
                        ->minValue(1),
                    \Filament\Forms\Components\DateTimePicker::make('flash_sale_starts_at')
                        ->label('Thời gian bắt đầu')
                        ->required(),
                    \Filament\Forms\Components\DateTimePicker::make('flash_sale_ends_at')
                        ->label('Thời gian kết thúc')
                        ->required()
                        ->after('flash_sale_starts_at'),
                ])
                ->action(function (array $data) {
                    $query = \App\Models\ProductVariant::query();
                    if ($data['apply_to'] === 'category') {
                        $query->whereHas('product', function ($q) use ($data) {
                            $q->whereIn('category_id', $data['category_ids']);
                        });
                    } else {
                        $query->whereIn('product_id', $data['product_ids']);
                    }

                    $variants = $query->get();
                    $count = 0;

                    foreach ($variants as $variant) {
                        $discount = $data['discount_type'] === 'percent'
                            ? ($variant->price * $data['discount_value'] / 100)
                            : $data['discount_value'];

                        $flashSalePrice = max(0, $variant->price - $discount);

                        $variant->update([
                            'flash_sale_price' => $flashSalePrice,
                            'flash_sale_starts_at' => $data['flash_sale_starts_at'],
                            'flash_sale_ends_at' => $data['flash_sale_ends_at'],
                        ]);
                        $count++;
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Thành công')
                        ->body("Đã cập nhật Flash Sale cho {$count} biến thể.")
                        ->success()
                        ->send();
                }),
                
            Actions\Action::make('remove_bulk_flash_sale')
                ->label('Hủy Flash Sale Hàng Loạt')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Radio::make('apply_to')
                        ->label('Áp dụng theo')
                        ->options([
                            'category' => 'Danh mục',
                            'product' => 'Sản phẩm',
                        ])
                        ->default('category')
                        ->reactive(),
                    \Filament\Forms\Components\Select::make('category_ids')
                        ->label('Chọn danh mục')
                        ->multiple()
                        ->options(\App\Models\Category::pluck('name', 'id'))
                        ->visible(fn (\Filament\Forms\Get $get) => $get('apply_to') === 'category')
                        ->required(fn (\Filament\Forms\Get $get) => $get('apply_to') === 'category'),
                    \Filament\Forms\Components\Select::make('product_ids')
                        ->label('Chọn sản phẩm')
                        ->multiple()
                        ->options(\App\Models\Product::pluck('name', 'id'))
                        ->searchable()
                        ->visible(fn (\Filament\Forms\Get $get) => $get('apply_to') === 'product')
                        ->required(fn (\Filament\Forms\Get $get) => $get('apply_to') === 'product'),
                ])
                ->action(function (array $data) {
                    $query = \App\Models\ProductVariant::query();
                    if ($data['apply_to'] === 'category') {
                        $query->whereHas('product', function ($q) use ($data) {
                            $q->whereIn('category_id', $data['category_ids']);
                        });
                    } else {
                        $query->whereIn('product_id', $data['product_ids']);
                    }

                    $count = $query->update([
                        'flash_sale_price' => null,
                        'flash_sale_starts_at' => null,
                        'flash_sale_ends_at' => null,
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Thành công')
                        ->body("Đã hủy Flash Sale cho {$count} biến thể.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
