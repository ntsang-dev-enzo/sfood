<?php

namespace App\Filament\Resources;

use App\Filament\Authorization\AdminAuthorization as Authz;
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
                    ->searchable()
                    ->preload()
                    ->live()
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->required(fn (string $operation): bool => $operation === 'create'),
                Forms\Components\Select::make('variant_id') // We map this to the variant ID
                    ->label('Biến thể sản phẩm')
                    ->options(function (Forms\Get $get) {
                        $productId = $get('product_id');
                        if (!$productId) return [];
                        return \App\Models\ProductVariant::where('product_id', $productId)->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->visible(fn (string $operation): bool => $operation === 'create'),
                Forms\Components\TextInput::make('name')
                    ->label('Tên biến thể')
                    ->disabled()
                    ->visible(fn (string $operation): bool => $operation === 'edit'),
                Forms\Components\TextInput::make('price')
                    ->label('Giá bán gốc')
                    ->numeric()
                    ->disabled()
                    ->prefix('đ')
                    ->visible(fn (string $operation): bool => $operation === 'edit'),
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
                Forms\Components\TextInput::make('flash_sale_stock')
                    ->label('Giới hạn số lượng bán')
                    ->numeric()
                    ->nullable()
                    ->helperText('Để trống nếu không giới hạn'),
                Forms\Components\TextInput::make('flash_sale_sold')
                    ->label('Số lượng đã bán')
                    ->numeric()
                    ->default(0)
                    ->disabled(fn (string $operation): bool => $operation === 'create')
                    ->visible(fn (string $operation): bool => $operation === 'edit'),
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
                Tables\Filters\SelectFilter::make('flash_sale_status')
                    ->label('Trạng thái Flash Sale')
                    ->options([
                        'active' => 'Đang diễn ra',
                        'upcoming' => 'Sắp diễn ra',
                        'ended' => 'Đã kết thúc',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query->whereNotNull('flash_sale_price');
                        }

                        $now = now();
                        if ($data['value'] === 'active') {
                            return $query->whereNotNull('flash_sale_price')
                                ->where('flash_sale_starts_at', '<=', $now)
                                ->where('flash_sale_ends_at', '>=', $now);
                        }
                        if ($data['value'] === 'upcoming') {
                            return $query->whereNotNull('flash_sale_price')
                                ->where('flash_sale_starts_at', '>', $now);
                        }
                        if ($data['value'] === 'ended') {
                            return $query->whereNotNull('flash_sale_price')
                                ->where('flash_sale_ends_at', '<', $now);
                        }

                        return $query;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function canViewAny(): bool
    {
        return Authz::canManageCatalog();
    }

    public static function canCreate(): bool
    {
        return Authz::canManageCatalog();
    }

    public static function canEdit($record): bool
    {
        return Authz::canManageCatalog();
    }

    public static function canDelete($record): bool
    {
        return Authz::isAdmin();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFlashSales::route('/'),
            'create' => Pages\CreateFlashSale::route('/create'),
            'edit' => Pages\EditFlashSale::route('/{record}/edit'),
        ];
    }
}
