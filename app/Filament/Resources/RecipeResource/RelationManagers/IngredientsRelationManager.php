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
    protected static ?string $title = 'Sản phẩm';
    protected static ?string $modelLabel = 'Sản phẩm';

    // Hiển thị “sản phẩm combo” theo công thức recipe (ingredients => product_variant => product)


    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('product_id')
                ->label('Sản phẩm')
                ->options(\App\Models\Product::pluck('name', 'id'))
                ->live()
                ->afterStateUpdated(fn (Forms\Set $set) => $set('product_variant_id', null))
                ->afterStateHydrated(function (Forms\Components\Select $component, Forms\Get $get) {
                    if ($variantId = $get('product_variant_id')) {
                        $variant = \App\Models\ProductVariant::find($variantId);
                        if ($variant) {
                            $component->state($variant->product_id);
                        }
                    }
                })
                ->searchable()
                ->dehydrated(false) // Don't save this to DB
                ->required(),

            Forms\Components\Select::make('product_variant_id')
                ->label('Biến thể')
                ->options(function (Forms\Get $get) {
                    $productId = $get('product_id');
                    if (! $productId) {
                        return [];
                    }
                    return \App\Models\ProductVariant::where('product_id', $productId)->pluck('name', 'id');
                })
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

            Tables\Columns\TextColumn::make('quantity_label')->label('Số lượng'),
        ])->headerActions([
            Tables\Actions\CreateAction::make(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }

}
