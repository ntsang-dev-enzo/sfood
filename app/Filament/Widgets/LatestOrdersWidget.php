<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Carbon\Carbon;

class LatestOrdersWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Đơn hàng mới nhất';

    public function table(Table $table): Table
    {
        $startDate = $this->filters['startDate'] ?? Carbon::now()->startOfMonth();
        $endDate = $this->filters['endDate'] ?? Carbon::now()->endOfMonth();

        if (is_string($startDate)) $startDate = Carbon::parse($startDate)->startOfDay();
        if (is_string($endDate)) $endDate = Carbon::parse($endDate)->endOfDay();

        return $table
            ->query(
                Order::query()
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Mã ĐH')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Khách hàng'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Tổng tiền')
                    ->money('VND'),
                Tables\Columns\TextColumn::make('order_status')
                    ->label('Trạng thái')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Chờ xử lý',
                        'processing' => 'Đang chuẩn bị',
                        'shipping' => 'Đang giao',
                        'completed' => 'Hoàn thành',
                        'cancelled' => 'Đã hủy',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipping' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Thời gian')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Chi tiết')
                    ->url(fn (Order $record): string => \App\Filament\Resources\OrderResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-m-eye'),
            ])
            ->paginated(false);
    }
}
