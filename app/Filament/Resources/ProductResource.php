<?php

namespace App\Filament\Resources;

use App\Filament\Authorization\AdminAuthorization as Authz;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\VariantsRelationManager;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Sản phẩm';

    protected static ?string $modelLabel = 'Sản phẩm';
    protected static ?string $pluralModelLabel = 'Sản phẩm';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('category_id')->label('Danh mục')->relationship('category', 'name')->required(),
            Forms\Components\TextInput::make('name')->label('Tên sản phẩm')->required()->live(onBlur: true)
                ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
            Forms\Components\TextInput::make('name_en')->label('Tên tiếng Anh'),
            Forms\Components\TextInput::make('slug')->label('Đường dẫn')->required()->unique(ignoreRecord: true),
            Forms\Components\Textarea::make('short_description')->label('Mô tả ngắn')->columnSpanFull()->maxLength(500),
            Forms\Components\Textarea::make('short_description_en')->label('Mô tả ngắn tiếng Anh')->columnSpanFull()->maxLength(500),
            Forms\Components\RichEditor::make('description')->label('Mô tả')->columnSpanFull()->fileAttachmentsDirectory('products/descriptions'),
            Forms\Components\RichEditor::make('description_en')->label('Mô tả tiếng Anh')->columnSpanFull()->fileAttachmentsDirectory('products/descriptions'),
            Forms\Components\FileUpload::make('thumbnail')->label('Ảnh đại diện')->image()->directory('products'),
            Forms\Components\Toggle::make('is_active')->label('Kích hoạt')->default(true),
            Forms\Components\Toggle::make('is_hot')->label('Sản phẩm Hot')->default(false),
            Forms\Components\Toggle::make('is_new')->label('Sản phẩm Mới')->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Tên sản phẩm')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('category.name')->label('Danh mục')->sortable(),
            Tables\Columns\IconColumn::make('is_active')->label('Trạng thái')->boolean()->sortable(),
            Tables\Columns\IconColumn::make('is_hot')->label('Hot')->boolean()->sortable(),
            Tables\Columns\IconColumn::make('is_new')->label('Mới')->boolean()->sortable(),
            Tables\Columns\TextColumn::make('view_count')->label('Lượt xem')->sortable(),
            Tables\Columns\TextColumn::make('variants_count')->label('Số biến thể')->counts('variants')->sortable(),
        ])->filters([
            Tables\Filters\SelectFilter::make('category_id')->relationship('category', 'name')->label('Danh mục'),
            Tables\Filters\TernaryFilter::make('is_active')->label('Trạng thái hoạt động'),
            Tables\Filters\Filter::make('created_at')
                ->form([
                    Forms\Components\DatePicker::make('created_from')->label('Từ ngày'),
                    Forms\Components\DatePicker::make('created_until')->label('Đến ngày'),
                ])
                ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                    return $query
                        ->when(
                            $data['created_from'],
                            fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('created_at', '<=', $date),
                        );
                }),
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
        return [
            VariantsRelationManager::class,
            \App\Filament\Resources\ProductResource\RelationManagers\ImagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
