<?php

namespace App\Filament\Resources\RecipeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class IngredientsRelationManager extends RelationManager
{
    protected static string $relationship = 'ingredients';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('product_variant_id')
                ->relationship('productVariant', 'name')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->product->name.' - '.$record->name)
                ->searchable()
                ->required(),
            Forms\Components\TextInput::make('quantity_label')->required()->label('Số lượng (vd: 500g, 2 muỗng)'),
            Forms\Components\TextInput::make('quantity')->numeric()->default(1),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('productVariant.product.name')->label('Sản phẩm'),
            Tables\Columns\TextColumn::make('productVariant.name')->label('Biến thể'),
            Tables\Columns\TextColumn::make('quantity_label'),
        ])->headerActions([
            Tables\Actions\CreateAction::make(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }
}
