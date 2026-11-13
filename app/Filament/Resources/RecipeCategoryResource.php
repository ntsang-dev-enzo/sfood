<?php

namespace App\Filament\Resources;

use App\Filament\Authorization\AdminAuthorization as Authz;
use App\Filament\Resources\RecipeCategoryResource\Pages;
use App\Filament\Resources\RecipeCategoryResource\RelationManagers;
use App\Models\RecipeCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RecipeCategoryResource extends Resource
{
    protected static ?string $model = RecipeCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Nội dung';

    protected static ?string $modelLabel = 'Danh mục công thức';
    protected static ?string $pluralModelLabel = 'Danh mục công thức';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Tên danh mục')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                Forms\Components\TextInput::make('slug')
                    ->label('Đường dẫn (slug)')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên danh mục')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Đường dẫn')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Cập nhật lúc')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecipeCategories::route('/'),
            'create' => Pages\CreateRecipeCategory::route('/create'),
            'edit' => Pages\EditRecipeCategory::route('/{record}/edit'),
        ];
    }
}
