<?php
namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\PickupRequest;
use App\Models\Order;
use Carbon\Carbon;

class HomepageStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();

        $pendingPickupRequestsCurrent = PickupRequest::where('status', 'Pending')
            ->where('created_at', '>=', $currentMonth)
            ->count();
        $pendingPickupRequestsPrevious = PickupRequest::where('status', 'Pending')
            ->whereBetween('created_at', [$previousMonth, $currentMonth])
            ->count();
        $pendingPickupRequestsDifference = $pendingPickupRequestsCurrent - $pendingPickupRequestsPrevious;

        $newOrdersCurrent = Order::where('status', 'New Order')
            ->where('created_at', '>=', $currentMonth)
            ->count();
        $newOrdersPrevious = Order::where('status', 'New Order')
            ->whereBetween('created_at', [$previousMonth, $currentMonth])
            ->count();
        $newOrdersDifference = $newOrdersCurrent - $newOrdersPrevious;

        $waitingForPaymentCurrent = Order::where('status', 'Waiting For Payment')
            ->where('created_at', '>=', $currentMonth)
            ->count();
        $waitingForPaymentPrevious = Order::where('status', 'Waiting For Payment')
            ->whereBetween('created_at', [$previousMonth, $currentMonth])
            ->count();
        $waitingForPaymentDifference = $waitingForPaymentCurrent - $waitingForPaymentPrevious;

        return [
            Stat::make('Pending Pickup Requests', $pendingPickupRequestsCurrent)
                ->description($pendingPickupRequestsDifference > 0 ? 'Increase' : 'Decrease')
                ->descriptionIcon($pendingPickupRequestsDifference > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($this->getDailyData(PickupRequest::class, 'Pending'))
                ->color('success'),

            Stat::make('New Orders', $newOrdersCurrent)
                ->description($newOrdersDifference > 0 ? 'Increase' : 'Decrease')
                ->descriptionIcon($newOrdersDifference > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($this->getDailyData(Order::class, 'New Order'))
                ->color('danger'),

            Stat::make('Waiting for Payment', $waitingForPaymentCurrent)
                ->description($waitingForPaymentDifference > 0 ? 'Increase' : 'Decrease')
                ->descriptionIcon($waitingForPaymentDifference > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($this->getDailyData(Order::class, 'Waiting For Payment'))
                ->color('warning'),
        ];
    }

    protected function getDailyData($model, $status)
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $data = $model::where('status', $status)
            ->where('created_at', '>=', $currentMonth)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $daysInMonth = Carbon::now()->daysInMonth;
        $dailyData = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = Carbon::now()->startOfMonth()->addDays($i - 1)->format('Y-m-d');
            $dailyData[] = $data[$date] ?? 0;
        }

        return $dailyData;
    }
}
