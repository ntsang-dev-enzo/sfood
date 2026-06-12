<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages;
use App\Models\Contact;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Liên hệ khách hàng';
    protected static ?string $pluralModelLabel = 'Liên hệ';
    protected static ?string $navigationGroup = 'Quản lý khách hàng';

    public static function canAccess(): bool
    {
        return auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isCustomerService());
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::where('status', 'pending')->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Họ tên')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('Số điện thoại')
                    ->maxLength(20),
                Forms\Components\Textarea::make('message')
                    ->label('Nội dung')
                    ->readOnly()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('reply_message')
                    ->label('Nội dung phản hồi')
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'pending' => 'Chờ xử lý',
                        'resolved' => 'Đã xử lý',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\Hidden::make('user_id'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Họ tên')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('phone')->label('Số điện thoại')->searchable(),
                Tables\Columns\IconColumn::make('user_id')
                    ->label('Thành viên')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->color(fn($state) => $state ? 'success' : 'gray'),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'resolved' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Ngày gửi')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('reply')
                    ->label('Phản hồi nhanh')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('reply_message')
                            ->label('Nội dung phản hồi')
                            ->required(),
                    ])
                    ->action(function (Contact $record, array $data) {
                        $record->reply_message = $data['reply_message'];
                        $record->status = 'resolved';
                        $record->save();

                        \Filament\Notifications\Notification::make()
                            ->title('Đã gửi phản hồi')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Contact $record) => $record->status === 'pending'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListContacts::route('/'),
            'create' => Pages\CreateContact::route('/create'),
            'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}
