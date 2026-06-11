<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Actions\Action;
use Illuminate\Support\Carbon;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $title = 'Tổng quan hệ thống';

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('startDate')
                    ->label('Từ ngày')
                    ->default(now()->startOfMonth())
                    ->maxDate(now()),
                DatePicker::make('endDate')
                    ->label('Đến ngày')
                    ->default(now()->endOfMonth())
                    ->maxDate(now()),
            ])
            ->columns(2);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_product')
                ->label('Thêm Sản phẩm')
                ->icon('heroicon-m-plus')
                ->url(fn (): string => \App\Filament\Resources\ProductResource::getUrl('create')),
            
            Action::make('manage_orders')
                ->label('Duyệt Đơn hàng')
                ->icon('heroicon-m-shopping-bag')
                ->color('success')
                ->url(fn (): string => \App\Filament\Resources\OrderResource::getUrl('index', ['tableFilters[order_status][value]' => 'pending'])),
                
            Action::make('manage_coupons')
                ->label('Mã giảm giá')
                ->icon('heroicon-m-ticket')
                ->color('warning')
                ->url(fn (): string => \App\Filament\Resources\CouponResource::getUrl('index')),
        ];
    }
}
