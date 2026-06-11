<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Models\Order;
use Carbon\Carbon;

class OrdersChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Biểu đồ Doanh thu';
    protected static ?int $sort = 2;
    public ?string $filter = 'daily';

    protected function getFilters(): ?array
    {
        return [
            'daily' => 'Theo ngày',
            'monthly' => 'Theo tháng',
        ];
    }

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? Carbon::now()->startOfMonth();
        $endDate = $this->filters['endDate'] ?? Carbon::now()->endOfMonth();

        if (is_string($startDate)) $startDate = Carbon::parse($startDate)->startOfDay();
        if (is_string($endDate)) $endDate = Carbon::parse($endDate)->endOfDay();

        $activeFilter = $this->filter;

        $orders = Order::select('created_at', 'total_amount')
            ->whereNotIn('order_status', ['cancelled'])
            ->whereNotIn('payment_status', ['failed', 'refunded'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $data = [];
        $labels = [];

        if ($activeFilter === 'monthly') {
            $groupedOrders = $orders->groupBy(function ($order) {
                return Carbon::parse($order->created_at)->format('m/Y');
            });

            // Lấy tất cả các tháng trong khoảng thời gian
            $period = \Carbon\CarbonPeriod::create($startDate, '1 month', $endDate);
            foreach ($period as $date) {
                $monthYear = $date->format('m/Y');
                $labels[] = $monthYear;
                $data[] = isset($groupedOrders[$monthYear]) ? $groupedOrders[$monthYear]->sum('total_amount') : 0;
            }
        } else {
            $groupedOrders = $orders->groupBy(function ($order) {
                return Carbon::parse($order->created_at)->format('d/m');
            });

            // Lấy tất cả các ngày trong khoảng thời gian
            $period = \Carbon\CarbonPeriod::create($startDate, '1 day', $endDate);
            foreach ($period as $date) {
                $dayMonth = $date->format('d/m');
                $labels[] = $dayMonth;
                $data[] = isset($groupedOrders[$dayMonth]) ? $groupedOrders[$dayMonth]->sum('total_amount') : 0;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Doanh thu (VND)',
                    'data' => $data,
                    'borderColor' => '#10b981', // Màu leaf-500
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
