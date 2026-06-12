<?php

namespace App\Filament\Resources;

use App\Filament\Authorization\AdminAuthorization as Authz;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Đơn hàng';

    protected static ?string $modelLabel = 'Đơn hàng';
    protected static ?string $pluralModelLabel = 'Đơn hàng';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('order_status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('order_status', 'pending')->count() > 0 ? 'warning' : 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('order_code')->label('Mã đơn hàng')->disabled(),
            Forms\Components\Select::make('order_status')->label('Trạng thái đơn')->options([
                'pending' => 'Chờ xử lý',
                'confirmed' => 'Đã xác nhận',
                'shipping' => 'Đang giao',
                'delivered' => 'Đã giao',
                'cancelled' => 'Đã hủy',
            ]),
            Forms\Components\Select::make('payment_status')->label('Trạng thái thanh toán')->options([
                'pending' => 'Chờ thanh toán',
                'paid' => 'Đã thanh toán',
                'failed' => 'Thất bại',
                'refund_pending' => 'Yêu cầu hoàn tiền',
                'refunded' => 'Đã hoàn tiền',
            ]),

            Forms\Components\Textarea::make('notes')->label('Ghi chú'),

            // Thông tin khách hàng/địa chỉ đặt hàng (ưu tiên hiển thị trong form edit)
            Forms\Components\Fieldset::make('shipping_address')
                ->label('Địa chỉ khách hàng')
                ->schema([
                    Forms\Components\TextInput::make('shipping_address.recipient_name')->label('Người nhận'),
                    Forms\Components\TextInput::make('shipping_address.phone')->label('Số điện thoại'),
                    Forms\Components\Textarea::make('shipping_address.address')->label('Địa chỉ chi tiết')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('order_code')->label('Mã đơn')->searchable(),
            Tables\Columns\TextColumn::make('user.name')->label('Khách hàng')->searchable()
                ->url(fn (Order $record) => $record->user_id ? UserResource::getUrl('index', ['tableSearch' => $record->user?->name]) : null)
                ->color('primary'),
            Tables\Columns\TextColumn::make('subtotal')->label('Tạm tính')->money('VND')->sortable(),
            Tables\Columns\TextColumn::make('shipping_fee')->label('Phí ship')->money('VND')->sortable(),
            Tables\Columns\TextColumn::make('discount_amount')->label('Giảm giá')->money('VND')->sortable(),
            Tables\Columns\TextColumn::make('total_amount')->label('Tổng cộng')->money('VND')->sortable(),
            Tables\Columns\TextColumn::make('order_status')->label('Trạng thái đơn')->badge()->sortable()
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'pending' => 'Chờ xử lý',
                    'confirmed' => 'Đã xác nhận',
                    'shipping' => 'Đang giao',
                    'delivered' => 'Đã giao',
                    'cancelled' => 'Đã hủy',
                    default => ucfirst($state),
                })
                ->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'confirmed' => 'info',
                    'shipping' => 'info',
                    'delivered' => 'success',
                    'cancelled' => 'danger',
                    default => 'gray',
                }),
            Tables\Columns\TextColumn::make('payment_status')->label('Trạng thái TT')->badge()->sortable()
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'pending' => 'Chờ thanh toán',
                    'paid' => 'Đã thanh toán',
                    'failed' => 'Thất bại',
                    'refund_pending' => 'Yêu cầu hoàn tiền',
                    'refunded' => 'Đã hoàn tiền',
                    default => ucfirst($state),
                })
                ->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'paid' => 'success',
                    'failed' => 'danger',
                    'refund_pending' => 'warning',
                    'refunded' => 'info',
                    default => 'gray',
                }),
            Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime()->sortable(),
        ])->filters([
            Tables\Filters\SelectFilter::make('order_status')->label('Trạng thái đơn')->options([
                'pending' => 'Chờ xử lý',
                'confirmed' => 'Đã xác nhận',
                'shipping' => 'Đang giao',
                'delivered' => 'Đã giao',
                'cancelled' => 'Đã hủy',
            ]),
            Tables\Filters\SelectFilter::make('payment_status')->label('Trạng thái thanh toán')->options([
                'pending' => 'Chờ thanh toán',
                'paid' => 'Đã thanh toán',
                'failed' => 'Thất bại',
                'refund_pending' => 'Yêu cầu hoàn tiền',
                'refunded' => 'Đã hoàn tiền',
            ]),
            Tables\Filters\Filter::make('created_at')
                ->form([
                    Forms\Components\DatePicker::make('created_from')->label('Từ ngày'),
                    Forms\Components\DatePicker::make('created_until')->label('Đến ngày'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['created_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
                }),
        ])->actions([
            Tables\Actions\Action::make('manual_refund')
                ->label('Xác nhận đã hoàn tiền')
                ->icon('heroicon-o-check-circle')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Xác nhận đã hoàn tiền thủ công')
                ->modalDescription('Chỉ bấm nút này khi bạn đã hoàn tiền cho khách qua chuyển khoản hoặc PayOS Dashboard. Trạng thái đơn sẽ chuyển sang "Đã hoàn tiền".')
                ->visible(fn (Order $record): bool => $record->payment_status === 'refund_pending')
                ->action(function (Order $record) {
                    $record->update(['payment_status' => 'refunded']);
                    if ($record->payment) {
                        $record->payment->update(['status' => 'refunded']);
                    }

                    if ($record->user) {
                        $record->user->notify(new \App\Notifications\OrderUpdated([
                            'title' => 'Hoàn tiền thành công',
                            'message' => 'Đơn hàng #' . $record->order_code . ' đã được hoàn tiền thành công.',
                            'url' => route('orders.index', ['status' => 'cancelled']),
                            'type' => 'order',
                        ]));
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Đã đánh dấu hoàn tiền thành công')
                        ->success()
                        ->send();
                }),
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function canViewAny(): bool
    {
        return Authz::canManageOrders();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canView($record): bool
    {
        return Authz::canManageOrders();
    }

    public static function canEdit($record): bool
    {
        return Authz::isAdmin() || Authz::isStaff();
    }

    public static function canDelete($record): bool
    {
        return Authz::isAdmin();
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\OrderResource\RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}

