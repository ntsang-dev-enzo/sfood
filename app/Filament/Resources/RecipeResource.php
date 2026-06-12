<?php

namespace App\Filament\Resources;

use App\Filament\Authorization\AdminAuthorization as Authz;
use App\Filament\Resources\RecipeResource\Pages;
use App\Filament\Resources\RecipeResource\RelationManagers\IngredientsRelationManager;
use App\Models\Recipe;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RecipeResource extends Resource
{
    protected static ?string $model = Recipe::class;

    protected static ?string $navigationIcon = 'heroicon-o-film';

    protected static ?string $navigationGroup = 'Nội dung';

    protected static ?string $modelLabel = 'Công thức';
    protected static ?string $pluralModelLabel = 'Công thức';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->label('Tiêu đề')->required()->live(onBlur: true)
                ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
            Forms\Components\TextInput::make('title_en')->label('Tiêu đề tiếng Anh'),
            Forms\Components\TextInput::make('slug')->label('Đường dẫn')->required()->unique(ignoreRecord: true),
            Forms\Components\Select::make('recipe_category_id')
                ->label('Danh mục')
                ->relationship('category', 'name')
                ->searchable()
                ->preload(),
            Forms\Components\Textarea::make('description')->label('Mô tả')->columnSpanFull(),
            Forms\Components\Textarea::make('description_en')->label('Mô tả tiếng Anh')->columnSpanFull(),
            Forms\Components\FileUpload::make('thumbnail')->label('Ảnh đại diện')->image()->directory('recipes'),
            Forms\Components\TextInput::make('video_url')->url()->label('Video URL (YouTube hoặc file)'),
            Forms\Components\TextInput::make('cook_time')->label('Thời gian nấu')->numeric()->suffix('phút'),
            Forms\Components\Toggle::make('is_published')->label('Xuất bản')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->label('Tiêu đề')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('cook_time')->label('Thời gian nấu')->suffix(' phút')->sortable(),
            Tables\Columns\TextColumn::make('view_count')->label('Lượt xem')->sortable(),
            Tables\Columns\IconColumn::make('is_published')->label('Xuất bản')->boolean()->sortable(),
        ])->filters([
            Tables\Filters\TernaryFilter::make('is_published')->label('Đã xuất bản'),
        ])->actions([
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

    public static function getRelations(): array
    {
        // Recipe combo: hiển thị sản phẩm theo công thức (ingredients => product_variant)
        return [
            IngredientsRelationManager::class,
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecipes::route('/'),
            'create' => Pages\CreateRecipe::route('/create'),
            'edit' => Pages\EditRecipe::route('/{record}/edit'),
        ];
    }
}
