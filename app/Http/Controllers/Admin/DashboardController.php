<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $startOfWeek = now()->copy()->startOfWeek()->toDateString();
        $startOfMonth = now()->copy()->startOfMonth()->toDateString();

        $weekSales = Order::where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [
                $startOfWeek,
                now()->copy()->endOfDay(),
            ])
            ->sum('total');

        $monthSales = Order::where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [
                $startOfMonth,
                now()->copy()->endOfDay(),
            ])
            ->sum('total');

        $todaySales = Order::where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereDate('completed_at', $today)
            ->sum('total');

        $pendingOrders = Order::where('status', 'pending')->count();
        $acceptedOrders = Order::where('status', 'accepted')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();

        $activeProducts = Product::where('is_active', true)->get();
        $activeProductCount = $activeProducts->count();
        $lowStockProducts = $activeProducts
            ->filter(function ($product) {
                $isGeneric = ($product->inventory_type ?? 'rice') === 'generic';
                $stock = (float) ($isGeneric
                    ? $product->stock_base_quantity
                    : $product->stock_kg);
                $threshold = (float) ($isGeneric
                    ? $product->low_stock_threshold_quantity
                    : $product->low_stock_threshold);

                return $stock <= $threshold;
            });
        $lowStockCount = $lowStockProducts->count();
        $lowStockProducts = $lowStockProducts
            ->sortBy(function ($product) {
                return ($product->inventory_type ?? 'rice') === 'generic'
                    ? (float) $product->stock_base_quantity
                    : (float) $product->stock_kg;
            })
            ->take(6)
            ->values();

        $recentOrders = Order::with('customer')
            ->latest()
            ->take(7)
            ->get();

        $completedOrdersToday = Order::where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereDate('completed_at', $today)
            ->count();

        $ordersThisMonth = Order::whereBetween('created_at', [
            now()->copy()->startOfMonth(),
            now()->copy()->endOfDay(),
        ])->count();

        $customerCount = User::where('role', 'customer')
            ->where('is_admin', false)
            ->count();

        $chartStart = now()->copy()->subDays(6)->startOfDay();
        $chartEnd = now()->copy()->endOfDay();
        $dailySales = Order::query()
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$chartStart, $chartEnd])
            ->selectRaw('DATE(completed_at) as sales_date, SUM(total) as total')
            ->groupBy('sales_date')
            ->pluck('total', 'sales_date');

        $salesChartLabels = [];
        $salesChartData = [];
        for ($daysAgo = 6; $daysAgo >= 0; $daysAgo--) {
            $date = now()->copy()->subDays($daysAgo);
            $salesChartLabels[] = $date->format('D');
            $salesChartData[] = (float) ($dailySales[$date->toDateString()] ?? 0);
        }

        $salesByCategory = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', 'completed')
            ->whereNotNull('orders.completed_at')
            ->whereBetween('orders.completed_at', [
                now()->copy()->startOfMonth(),
                now()->copy()->endOfDay(),
            ])
            ->selectRaw("COALESCE(NULLIF(products.category, ''), 'Uncategorized') as category, SUM(order_items.line_total) as total")
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $topProducts = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'completed')
            ->whereNotNull('orders.completed_at')
            ->whereBetween('orders.completed_at', [
                now()->copy()->startOfMonth(),
                now()->copy()->endOfDay(),
            ])
            ->select(
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.line_total) as sales_total')
            )
            ->groupBy('order_items.product_name')
            ->orderByDesc('sales_total')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'todaySales',
            'weekSales',
            'monthSales',
            'ordersThisMonth',
            'activeProductCount',
            'customerCount',
            'pendingOrders',
            'acceptedOrders',
            'completedOrders',
            'cancelledOrders',
            'lowStockCount',
            'lowStockProducts',
            'recentOrders',
            'completedOrdersToday',
            'salesChartLabels',
            'salesChartData',
            'salesByCategory',
            'topProducts'
        ));
    }
}
