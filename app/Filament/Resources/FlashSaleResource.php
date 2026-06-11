<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FlashSaleResource\Pages;
use App\Models\ProductVariant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FlashSaleResource extends Resource
{
    protected static ?string $model = ProductVariant::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationLabel = 'Flash Sales';
    protected static ?string $modelLabel = 'Sản phẩm Flash Sale';
    protected static ?string $pluralModelLabel = 'Flash Sales';
    protected static ?string $navigationGroup = 'Sản phẩm';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Sản phẩm')
                    ->relationship('product', 'name')
                    ->disabled(),
                Forms\Components\TextInput::make('name')
                    ->label('Tên biến thể')
                    ->disabled(),
                Forms\Components\TextInput::make('price')
                    ->label('Giá bán gốc')
                    ->numeric()
                    ->disabled()
                    ->prefix('đ'),
                Forms\Components\TextInput::make('flash_sale_price')
                    ->label('Giá Flash Sale')
                    ->numeric()
                    ->prefix('đ')
                    ->required(),
                Forms\Components\DateTimePicker::make('flash_sale_starts_at')
                    ->label('Bắt đầu Flash Sale')
                    ->required(),
                Forms\Components\DateTimePicker::make('flash_sale_ends_at')
                    ->label('Kết thúc Flash Sale')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Sản phẩm')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên biến thể')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Giá gốc')
                    ->money('VND')
                    ->sortable(),
                Tables\Columns\TextColumn::make('flash_sale_price')
                    ->label('Giá Flash Sale')
                    ->money('VND')
                    ->sortable(),
                Tables\Columns\TextColumn::make('flash_sale_starts_at')
                    ->label('Bắt đầu')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('flash_sale_ends_at')
                    ->label('Kết thúc')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('active_flash_sales')
                    ->label('Đang diễn ra')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('flash_sale_price')
                        ->where('flash_sale_starts_at', '<=', now())
                        ->where('flash_sale_ends_at', '>=', now())
                    )
                    ->default(),
                Tables\Filters\Filter::make('has_flash_sales')
                    ->label('Có Flash Sale')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('flash_sale_price')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFlashSales::route('/'),
            'edit' => Pages\EditFlashSale::route('/{record}/edit'),
        ];
    }
}
