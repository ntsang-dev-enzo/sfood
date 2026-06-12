<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Authorization\AdminAuthorization as Authz;
use Illuminate\Notifications\DatabaseNotification;

class NotificationResource extends Resource
{
    protected static ?string $model = DatabaseNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationLabel = 'Thông báo';
    protected static ?string $modelLabel = 'Thông báo';
    protected static ?string $pluralModelLabel = 'Thông báo';
    protected static ?string $navigationGroup = 'Hệ thống';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Người nhận')
                    ->options(User::pluck('name', 'id')->toArray() + ['all' => 'Tất cả khách hàng'])
                    ->required()
                    ->searchable()
                    ->placeholder('Chọn người nhận'),
                Forms\Components\Select::make('type_category')
                    ->label('Loại thông báo')
                    ->options([
                        'account' => 'Tài khoản',
                        'order' => 'Đơn hàng',
                        'promotion' => 'Khuyến mãi',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->label('Tiêu đề')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('message')
                    ->label('Nội dung')
                    ->required()
                    ->maxLength(1000),
                Forms\Components\TextInput::make('url')
                    ->label('Đường dẫn (URL)')
                    ->url()
                    ->nullable()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('notifiable.name')
                    ->label('Người nhận')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $record->notifiable ? $record->notifiable->name : 'N/A'),
                Tables\Columns\TextColumn::make('data.type')
                    ->label('Loại'),
                Tables\Columns\TextColumn::make('data.title')
                    ->label('Tiêu đề')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canViewAny(): bool
    {
        return Authz::isAdmin();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
        ];
    }
}
