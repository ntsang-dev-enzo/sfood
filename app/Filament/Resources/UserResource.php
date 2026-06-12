<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Authorization\AdminAuthorization as Authz;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Tài khoản';

    protected static ?string $modelLabel = 'Khách hàng';
    protected static ?string $pluralModelLabel = 'Khách hàng';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('created_at', today())->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
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
                    ->label('Email')
                    ->email()
                    ->required()
                    ->disabled(fn () => !Authz::isAdmin())
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('Số điện thoại')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\Select::make('role')
                    ->label('Vai trò')
                    ->options([
                        'admin' => 'Admin',
                        'staff' => 'Nhân viên',
                        'user' => 'Khách hàng',
                    ])
                    ->required()
                    ->default('user')
                    ->disabled(fn () => !Authz::isAdmin()),
                Forms\Components\TextInput::make('reward_points')
                    ->label('Điểm thưởng')
                    ->numeric()
                    ->default(0)
                    ->disabled(fn () => !Authz::isAdmin()),
                Forms\Components\TextInput::make('password')
                    ->label('Mật khẩu (để trống nếu không đổi)')
                    ->password()
                    ->disabled(fn () => !Authz::isAdmin())
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => \App\Support\MediaHelper::avatar($record->avatar, $record->name)),
                Tables\Columns\TextColumn::make('name')
                    ->label('Họ tên')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Số điện thoại')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reward_points')
                    ->label('Điểm thưởng')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Vai trò')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Admin',
                        'staff' => 'Nhân viên',
                        'user' => 'Khách hàng',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'staff' => 'warning',
                        'user' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày đăng ký')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Vai trò')
                    ->options([
                        'admin' => 'Admin',
                        'staff' => 'Nhân viên',
                        'user' => 'Khách hàng',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('sendNotification')
                    ->label('Gửi thông báo')
                    ->icon('heroicon-o-bell')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('title')->label('Tiêu đề')->required(),
                        Forms\Components\Textarea::make('message')->label('Nội dung')->required(),
                        Forms\Components\TextInput::make('url')->label('URL (Tùy chọn)')->url(),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->notify(new \App\Notifications\CustomerNotification([
                            'title' => $data['title'],
                            'message' => $data['message'],
                            'url' => $data['url'] ?? null,
                            'type' => 'account',
                        ]));
                        \Filament\Notifications\Notification::make()->title('Đã gửi thông báo')->success()->send();
                    }),
                Tables\Actions\Action::make('sendVoucher')
                    ->label('Gửi voucher')
                    ->icon('heroicon-o-ticket')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('coupon_id')
                            ->label('Mã giảm giá')
                            ->options(\App\Models\Coupon::where('is_active', true)->pluck('code', 'id'))
                            ->required(),
                    ])
                    ->action(function (User $record, array $data): void {
                        $coupon = \App\Models\Coupon::find($data['coupon_id']);
                        if ($coupon) {
                            $record->collectedCoupons()->syncWithoutDetaching([$coupon->id]);
                            $record->notify(new \App\Notifications\CustomerNotification([
                                'title' => 'Tặng mã giảm giá 🎁',
                                'message' => 'Bạn vừa nhận được mã giảm giá: ' . $coupon->code,
                                'url' => route('vouchers.index'),
                                'type' => 'promotion',
                            ]));
                            \Filament\Notifications\Notification::make()->title('Đã gửi voucher')->success()->send();
                        }
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function canViewAny(): bool
    {
        return Authz::isAdmin() || Authz::isStaff();
    }

    public static function canCreate(): bool
    {
        return Authz::isAdmin();
    }

    public static function canEdit($record): bool
    {
        if (Authz::isAdmin()) {
            return true;
        }

        // Staff chỉ có thể sửa khách hàng
        if (Authz::isStaff()) {
            return $record->role === 'user';
        }

        return false;
    }

    public static function canDelete($record): bool
    {
        // Không ai được xóa chính mình
        if ($record->id === auth()->id()) {
            return false;
        }

        if (Authz::isAdmin()) {
            return true;
        }

        // Staff không thể xóa admin và khách hàng (hoặc thực tế là không xóa ai cả)
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsers::route('/'),
        ];
    }
}
