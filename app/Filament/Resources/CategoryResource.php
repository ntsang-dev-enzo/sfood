<?php

namespace App\Filament\Resources;

use App\Filament\Authorization\AdminAuthorization as Authz;
use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Sản phẩm';

    protected static ?string $modelLabel = 'Danh mục';
    protected static ?string $pluralModelLabel = 'Danh mục';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Tên danh mục')->required()->live(onBlur: true)
                ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
            Forms\Components\TextInput::make('name_en')->label('Tên tiếng Anh'),
            Forms\Components\TextInput::make('slug')->label('Đường dẫn')->required()->unique(ignoreRecord: true),
            Forms\Components\FileUpload::make('thumbnail')->label('Ảnh đại diện')->image()->directory('categories'),
            Forms\Components\Textarea::make('description')->label('Mô tả'),
            Forms\Components\Textarea::make('description_en')->label('Mô tả tiếng Anh'),
            Forms\Components\Toggle::make('is_active')->label('Đang hoạt động')->default(true),
            Forms\Components\TextInput::make('sort_order')->label('Thứ tự hiển thị')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Tên')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('slug')->label('Đường dẫn'),
            Tables\Columns\IconColumn::make('is_active')->label('Trạng thái')->boolean()->sortable(),
            Tables\Columns\TextColumn::make('products_count')->label('Số sản phẩm')->counts('products')->sortable(),
        ])->filters([
            Tables\Filters\TernaryFilter::make('is_active')->label('Trạng thái hoạt động'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
