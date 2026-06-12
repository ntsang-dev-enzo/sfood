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
            Tables\Columns\TextColumn::make('user.name')->label('Khách hàng'),
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
            Tables\Actions\Action::make('confirm_refund')
                ->label('Xác nhận hoàn tiền')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Xác nhận hoàn tiền tự động qua PayOS')
                ->modalDescription('Hệ thống sẽ gọi API hoàn tiền trực tiếp qua cổng thanh toán PayOS. Bạn có chắc chắn muốn tiếp tục?')
                ->visible(fn (Order $record): bool => $record->payment_status === 'refund_pending' && $record->payment && $record->payment->gateway === 'payos')
                ->action(function (Order $record) {
                    $payment = $record->payment;
                    $clientId = config('services.payos.client_id');
                    $apiKey = config('services.payos.api_key');
                    $orderCode = $payment->gateway_response['orderCode'] ?? null;
                    
                    if ($orderCode) {
                        try {
                            $response = \Illuminate\Support\Facades\Http::withHeaders([
                                'x-client-id' => $clientId,
                                'x-api-key' => $apiKey,
                            ])->post("https://api-merchant.payos.vn/v2/payment-requests/{$orderCode}/refund", [
                                'amount' => $record->total_amount,
                                'reason' => 'Admin xác nhận hoàn tiền',
                            ]);

                            if ($response->successful() && $response->json('code') == '00') {
                                $record->update(['payment_status' => 'refunded']);
                                \Filament\Notifications\Notification::make()
                                    ->title('Hoàn tiền tự động qua PayOS thành công')
                                    ->success()
                                    ->send();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('Lỗi API PayOS')
                                    ->body($response->json('desc') ?? 'Không thể hoàn tiền tự động')
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Throwable $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Lỗi kết nối PayOS')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('Lỗi dữ liệu')
                            ->body('Không tìm thấy Mã giao dịch (OrderCode) của PayOS.')
                            ->danger()
                            ->send();
                    }
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
        return Authz::canManageOrders();
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

