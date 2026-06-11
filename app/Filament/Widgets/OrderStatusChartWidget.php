<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Models\Order;
use Carbon\Carbon;

class OrderStatusChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Tỷ lệ Trạng thái Đơn hàng';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? Carbon::now()->startOfMonth();
        $endDate = $this->filters['endDate'] ?? Carbon::now()->endOfMonth();

        if (is_string($startDate)) $startDate = Carbon::parse($startDate)->startOfDay();
        if (is_string($endDate)) $endDate = Carbon::parse($endDate)->endOfDay();

        $statuses = [
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang chuẩn bị',
            'shipping' => 'Đang giao',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];

        $counts = [];
        $colors = [];
        $bgColors = [
            'pending' => '#f59e0b', // warning (amber)
            'processing' => '#0ea5e9', // info (sky)
            'shipping' => '#3b82f6', // primary (blue)
            'completed' => '#10b981', // success (emerald)
            'cancelled' => '#ef4444', // danger (red)
        ];

        foreach ($statuses as $key => $label) {
            $counts[] = Order::where('order_status', $key)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
            $colors[] = $bgColors[$key];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Số lượng',
                    'data' => $counts,
                    'backgroundColor' => $colors,
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => array_values($statuses),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
