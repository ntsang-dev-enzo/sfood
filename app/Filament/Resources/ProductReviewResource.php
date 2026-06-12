<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductReviewResource\Pages;
use App\Filament\Resources\ProductReviewResource\RelationManagers;
use App\Models\ProductReview;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductReviewResource extends Resource
{
    protected static ?string $model = ProductReview::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'Đánh giá SP';
    protected static ?string $modelLabel = 'Đánh giá';
    protected static ?string $pluralModelLabel = 'Đánh giá SP';
    protected static ?string $navigationGroup = 'Sản phẩm';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('created_at', today())->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Khách hàng')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\Select::make('product_id')
                    ->label('Sản phẩm')
                    ->relationship('product', 'name')
                    ->required(),
                Forms\Components\TextInput::make('rating')
                    ->label('Số sao')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(5),
                Forms\Components\Textarea::make('comment')
                    ->label('Bình luận')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('attachments')
                    ->label('Tệp đính kèm')
                    ->multiple()
                    ->directory('reviews')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Khách hàng')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Sản phẩm')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rating')
                    ->label('Sao')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => str_repeat('★', $state)),
                Tables\Columns\TextColumn::make('comment')
                    ->label('Bình luận')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày đăng')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductReviews::route('/'),
            'create' => Pages\CreateProductReview::route('/create'),
            'edit' => Pages\EditProductReview::route('/{record}/edit'),
        ];
    }
}
