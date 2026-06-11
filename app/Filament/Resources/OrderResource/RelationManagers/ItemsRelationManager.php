<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('product_name')->disabled(),
            Forms\Components\TextInput::make('variant_name')->disabled(),
            Forms\Components\TextInput::make('unit_price')->disabled()->prefix('đ'),
            Forms\Components\TextInput::make('quantity')->disabled(),
            Forms\Components\TextInput::make('subtotal')->disabled()->prefix('đ'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('product_name'),
            Tables\Columns\TextColumn::make('variant_name'),
            Tables\Columns\TextColumn::make('unit_price')->money('VND'),
            Tables\Columns\TextColumn::make('quantity'),
            Tables\Columns\TextColumn::make('subtotal')->money('VND'),
        ])->headerActions([])->actions([]);
    }
}
