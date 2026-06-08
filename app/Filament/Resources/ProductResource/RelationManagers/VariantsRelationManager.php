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

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('sku')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('price')->numeric()->required()->prefix('đ'),
            Forms\Components\TextInput::make('sale_price')->numeric()->prefix('đ'),
            Forms\Components\TextInput::make('stock')->numeric()->default(0),
            Forms\Components\FileUpload::make('image')->image()->directory('variants'),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name'),
            Tables\Columns\TextColumn::make('sku'),
            Tables\Columns\TextColumn::make('price')->money('VND'),
            Tables\Columns\TextColumn::make('stock'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->headerActions([
            Tables\Actions\CreateAction::make(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }
}
