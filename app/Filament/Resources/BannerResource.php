<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Banners quảng cáo';
    protected static ?string $modelLabel = 'Banner';
    protected static ?string $pluralModelLabel = 'Banners';
    protected static ?string $navigationGroup = 'Giao diện & Banners';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Tiêu đề Banner')
                    ->placeholder('Ví dụ: Nông sản sạch VietGAP giảm giá 20%')
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('image_path')
                    ->label('Hình ảnh Banner (Đồng bộ kích thước 16:9)')
                    ->image()
                    ->directory('banners')
                    ->imageEditor()
                    ->imageCropAspectRatio('16:9')
                    ->imageResizeTargetWidth('1920')
                    ->imageResizeTargetHeight('800')
                    ->helperText('Kích thước khuyến nghị: 1920x800px hoặc 1200x500px để tất cả banner hiển thị đồng nhất kích thước')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('link')
                    ->label('Đường dẫn khi click (URL)')
                    ->placeholder('https://... hoặc /san-pham')
                    ->maxLength(255),

                Forms\Components\TextInput::make('sort_order')
                    ->label('Thứ tự hiển thị')
                    ->numeric()
                    ->default(0),

                Forms\Components\Toggle::make('is_active')
                    ->label('Kích hoạt hiển thị')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Hình ảnh')
                    ->height(50)
                    ->extraImgAttributes(['class' => 'rounded object-cover border']),
                Tables\Columns\TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable(),
                Tables\Columns\TextColumn::make('link')
                    ->label('Đường dẫn')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Kích hoạt'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Thứ tự')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
