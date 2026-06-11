<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';
    protected static ?string $title = 'Biến thể';
    protected static ?string $modelLabel = 'Biến thể';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Tên biến thể')->required(),
            Forms\Components\TextInput::make('sku')->label('Mã SKU')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('price')->label('Giá bán')->numeric()->required()->prefix('đ'),
            Forms\Components\TextInput::make('sale_price')->label('Giá khuyến mãi')->numeric()->prefix('đ'),
            Forms\Components\TextInput::make('flash_sale_price')->label('Giá Flash Sale')->numeric()->prefix('đ'),
            Forms\Components\DateTimePicker::make('flash_sale_starts_at')->label('Bắt đầu Flash Sale'),
            Forms\Components\DateTimePicker::make('flash_sale_ends_at')->label('Kết thúc Flash Sale'),
            Forms\Components\TextInput::make('stock')->label('Tồn kho')->numeric()->default(0),
            Forms\Components\FileUpload::make('image')->label('Hình ảnh')->image()->directory('variants'),
            Forms\Components\Toggle::make('is_active')->label('Kích hoạt')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Tên')->sortable(),
            Tables\Columns\TextColumn::make('sku')->label('SKU')->sortable(),
            Tables\Columns\TextColumn::make('price')->label('Giá')->money('VND')->sortable(),
            Tables\Columns\TextColumn::make('flash_sale_price')->label('Giá Flash Sale')->money('VND')->sortable(),
            Tables\Columns\TextColumn::make('stock')->label('Tồn kho')->sortable(),
            Tables\Columns\IconColumn::make('is_active')->label('Kích hoạt')->boolean()->sortable(),
        ])->headerActions([
            Tables\Actions\CreateAction::make(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }
}
