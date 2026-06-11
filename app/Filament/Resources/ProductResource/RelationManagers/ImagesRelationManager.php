<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';
    protected static ?string $title = 'Hình ảnh';
    protected static ?string $modelLabel = 'Hình ảnh';

    protected static ?string $recordTitleAttribute = 'image_path';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('image_path')
                ->label('Hình ảnh')
                ->directory('products')
                ->image()
                ->required(),
            Forms\Components\TextInput::make('sort_order')->label('Thứ tự sắp xếp')->numeric()->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')->label('Hình ảnh')->square(),
                Tables\Columns\TextColumn::make('sort_order')->label('Thứ tự')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime()->sortable(),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
