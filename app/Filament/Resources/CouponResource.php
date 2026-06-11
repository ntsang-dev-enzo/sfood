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

    protected static ?string $modelLabel = 'Mã giảm giá';
    protected static ?string $pluralModelLabel = 'Mã giảm giá';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->label('Mã code')->required()->unique(ignoreRecord: true),
            Forms\Components\Select::make('type')->label('Loại')->options([
                'percent' => 'Giảm %',
                'fixed' => 'Giảm cố định',
                'free_shipping' => 'Miễn phí ship',
            ])->required(),
            Forms\Components\TextInput::make('value')->label('Giá trị giảm')->numeric()->default(0),
            Forms\Components\TextInput::make('min_order_amount')->label('Đơn hàng tối thiểu')->numeric()->default(0),
            Forms\Components\MultiSelect::make('applicable_category_ids')
                ->label('Áp dụng cho danh mục')
                ->options(Category::pluck('name', 'id')->toArray())
                ->helperText('Để trống nếu áp dụng cho tất cả danh mục'),
            Forms\Components\DateTimePicker::make('starts_at')->label('Thời gian bắt đầu'),
            Forms\Components\DateTimePicker::make('expires_at')->label('Thời gian kết thúc'),
            Forms\Components\TextInput::make('usage_limit')->label('Giới hạn số lần dùng')->numeric(),
            Forms\Components\Toggle::make('is_active')->label('Kích hoạt')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('code')->label('Mã code')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('type')->label('Loại')->sortable(),
            Tables\Columns\TextColumn::make('value')->label('Giá trị')->sortable(),
            Tables\Columns\TextColumn::make('starts_at')->dateTime()->label('Bắt đầu')->sortable(),
            Tables\Columns\TextColumn::make('expires_at')->dateTime()->label('Kết thúc')->sortable(),
            Tables\Columns\TextColumn::make('used_count')->label('Đã dùng')->sortable(),
            Tables\Columns\TextColumn::make('usage_limit')->label('Giới hạn')->sortable(),
            Tables\Columns\TextColumn::make('usage')->label('Sử dụng')->getStateUsing(fn ($record) => ($record->used_count ?? 0) . '/' . ($record->usage_limit ?? '—')),
            Tables\Columns\IconColumn::make('is_active')->label('Trạng thái')->boolean()->sortable(),
        ])->filters([
            Tables\Filters\SelectFilter::make('type')->label('Loại mã')->options([
                'percent' => 'Giảm %',
                'fixed' => 'Giảm cố định',
                'free_shipping' => 'Miễn phí ship',
            ]),
            Tables\Filters\TernaryFilter::make('is_active')->label('Trạng thái hoạt động'),
            Tables\Filters\Filter::make('created_at')
                ->form([
                    Forms\Components\DatePicker::make('created_from')->label('Từ ngày'),
                    Forms\Components\DatePicker::make('created_until')->label('Đến ngày'),
                ])
                ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                    return $query
                        ->when(
                            $data['created_from'],
                            fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('created_at', '<=', $date),
                        );
                }),
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
