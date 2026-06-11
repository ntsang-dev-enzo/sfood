<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;

class StatsOverviewWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? Carbon::now()->startOfMonth();
        $endDate = $this->filters['endDate'] ?? Carbon::now()->endOfMonth();

        // Xử lý logic convert chuỗi từ picker thành Carbon nếu cần
        if (is_string($startDate)) $startDate = Carbon::parse($startDate)->startOfDay();
        if (is_string($endDate)) $endDate = Carbon::parse($endDate)->endOfDay();

        // Tổng doanh thu (không tính đơn hủy/thất bại)
        $totalRevenue = Order::whereNotIn('order_status', ['cancelled'])
            ->whereNotIn('payment_status', ['failed', 'refunded'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        // Tổng đơn hàng (không tính hủy)
        $totalOrders = Order::whereNotIn('order_status', ['cancelled'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Tỷ lệ đơn hủy
        $cancelledOrders = Order::where('order_status', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $allOrdersCount = Order::whereBetween('created_at', [$startDate, $endDate])->count();
        $cancelRate = $allOrdersCount > 0 ? round(($cancelledOrders / $allOrdersCount) * 100, 1) : 0;

        // Số đơn yêu cầu hoàn tiền đang chờ (chỉ đếm đơn phát sinh trong khoảng)
        $refundPendingCount = Order::where('payment_status', 'refund_pending')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Số khách hàng đăng ký mới
        $newUsers = User::whereBetween('created_at', [$startDate, $endDate])->count();

        return [
            Stat::make('Doanh thu', number_format($totalRevenue) . ' đ')
                ->description('Tổng doanh thu các đơn hợp lệ')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
                
            Stat::make('Đơn hàng thành công', $totalOrders)
                ->description('Tỷ lệ hủy: ' . $cancelRate . '%')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),

            Stat::make('Yêu cầu hoàn tiền', $refundPendingCount)
                ->description('Cần xử lý hoàn tiền ngay')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($refundPendingCount > 0 ? 'danger' : 'success'),
                
            Stat::make('Thành viên mới', $newUsers)
                ->description('Đăng ký trong kỳ')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
        ];
    }
}
