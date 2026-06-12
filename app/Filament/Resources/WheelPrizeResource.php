<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WheelPrizeResource\Pages;
use App\Filament\Resources\WheelPrizeResource\RelationManagers;
use App\Models\WheelPrize;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WheelPrizeResource extends Resource
{
    protected static ?string $model = WheelPrize::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Tên phần thưởng'),
                Forms\Components\Select::make('type')
                    ->options([
                        'fixed' => 'Giảm tiền mặt',
                        'percent' => 'Giảm theo phần trăm',
                        'free_shipping' => 'Miễn phí vận chuyển',
                    ])
                    ->required()
                    ->label('Loại thưởng'),
                Forms\Components\TextInput::make('value')
                    ->numeric()
                    ->default(0)
                    ->label('Giá trị (VNĐ hoặc %)'),
                Forms\Components\TextInput::make('min_order_amount')
                    ->numeric()
                    ->default(0)
                    ->label('Đơn tối thiểu (VNĐ)'),
                Forms\Components\TextInput::make('weight')
                    ->numeric()
                    ->default(10)
                    ->required()
                    ->label('Tỷ lệ trúng (Weight)'),
                Forms\Components\Select::make('applicable_category_ids')
                    ->multiple()
                    ->options(\App\Models\Category::pluck('name', 'id'))
                    ->label('Danh mục áp dụng'),
                Forms\Components\Select::make('applicable_product_ids')
                    ->multiple()
                    ->options(\App\Models\Product::pluck('name', 'id'))
                    ->searchable()
                    ->label('Sản phẩm áp dụng'),
                Forms\Components\Toggle::make('is_miss')
                    ->label('Là ô "Chúc bạn may mắn lần sau"? (Không ra mã coupon)')
                    ->default(false),
                Forms\Components\Toggle::make('is_active')
                    ->label('Kích hoạt')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Tên phần thưởng'),
                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->label('Loại'),
                Tables\Columns\TextColumn::make('value')
                    ->numeric()
                    ->sortable()
                    ->label('Giá trị'),
                Tables\Columns\TextColumn::make('weight')
                    ->numeric()
                    ->sortable()
                    ->label('Tỷ lệ trúng'),
                Tables\Columns\IconColumn::make('is_miss')
                    ->boolean()
                    ->label('Ô trượt'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Kích hoạt'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWheelPrizes::route('/'),
            'create' => Pages\CreateWheelPrize::route('/create'),
            'edit' => Pages\EditWheelPrize::route('/{record}/edit'),
        ];
    }
}
