<?php
namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Order;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class MonthlySalesChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Sales';

    protected function getType(): string
    {
        return 'line'; // or 'bar', 'pie', etc.
    }

    protected function getData(): array
    {
        // Apply the filter directly on the Eloquent query
        $query = Order::whereNotIn('status', ['New Order', 'Waiting For Payment', 'Cleaning']);

        $data = Trend::query($query)
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->sum('total_price');

        return [
            'datasets' => [
                [
                    'label' => 'Sales',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }
}
