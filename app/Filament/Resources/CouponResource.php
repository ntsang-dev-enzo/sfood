<?php

namespace App\Filament\Resources;

use App\Filament\Authorization\AdminAuthorization as Authz;
use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Đơn hàng';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->required()->unique(ignoreRecord: true),
            Forms\Components\Select::make('type')->options([
                'percent' => 'Giảm %',
                'fixed' => 'Giảm cố định',
                'free_shipping' => 'Miễn phí ship',
            ])->required(),
            Forms\Components\TextInput::make('value')->numeric()->default(0),
            Forms\Components\TextInput::make('min_order_amount')->numeric()->default(0),
            Forms\Components\MultiSelect::make('applicable_category_ids')
                ->label('Áp dụng cho danh mục')
                ->options(Category::pluck('name', 'id')->toArray())
                ->helperText('Để trống nếu áp dụng cho tất cả danh mục'),
            Forms\Components\DateTimePicker::make('starts_at'),
            Forms\Components\DateTimePicker::make('expires_at'),
            Forms\Components\TextInput::make('usage_limit')->numeric(),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('code')->searchable(),
            Tables\Columns\TextColumn::make('type'),
            Tables\Columns\TextColumn::make('value'),
            Tables\Columns\TextColumn::make('starts_at')->dateTime()->label('Bắt đầu'),
            Tables\Columns\TextColumn::make('expires_at')->dateTime()->label('Kết thúc'),
            Tables\Columns\TextColumn::make('used_count')->label('Đã dùng'),
            Tables\Columns\TextColumn::make('usage_limit')->label('Giới hạn'),
            Tables\Columns\TextColumn::make('usage')->label('Usage')->getStateUsing(fn ($record) => ($record->used_count ?? 0) . '/' . ($record->usage_limit ?? '—')),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function canViewAny(): bool
    {
        return Authz::canManageCoupons();
    }

    public static function canCreate(): bool
    {
        return Authz::canManageCoupons();
    }

    public static function canEdit($record): bool
    {
        return Authz::canManageCoupons();
    }

    public static function canDelete($record): bool
    {
        return Authz::canManageCoupons();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
