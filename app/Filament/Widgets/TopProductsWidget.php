<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TopProductsWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Sản phẩm bán chạy (theo số lượng)';

    public function table(Table $table): Table
    {
        $startDate = $this->filters['startDate'] ?? Carbon::now()->startOfMonth();
        $endDate = $this->filters['endDate'] ?? Carbon::now()->endOfMonth();

        if (is_string($startDate)) $startDate = Carbon::parse($startDate)->startOfDay();
        if (is_string($endDate)) $endDate = Carbon::parse($endDate)->endOfDay();

        // Get top 5 selling products from OrderItem within the period
        $topProductsIds = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->whereNotIn('orders.order_status', ['cancelled'])
            ->whereNotIn('orders.payment_status', ['failed', 'refunded'])
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select('product_variants.product_id', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('product_variants.product_id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get()
            ->pluck('total_sold', 'product_id');

        return $table
            ->query(
                Product::query()->whereIn('id', $topProductsIds->keys())
            )
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Hình ảnh')
                    ->square()
                    ->defaultImageUrl(fn () => asset('images/placeholder.png')),
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên sản phẩm')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Danh mục'),
                Tables\Columns\TextColumn::make('total_sold')
                    ->label('Đã bán')
                    ->getStateUsing(fn ($record) => $topProductsIds[$record->id] ?? 0)
                    ->numeric()
                    ->badge()
                    ->color('success'),
            ])
            ->defaultSort('total_sold', 'desc')
            ->paginated(false);
    }
}
