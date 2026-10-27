<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function reportDates(Request $request): array
    {
        $period = $request->query('period', 'daily');

        if (! in_array($period, ['daily', 'weekly', 'monthly', 'yearly'], true)) {
            $period = 'daily';
        }

        $selectedDate = $request->query('date', Carbon::today()->toDateString());

        try {
            $referenceDate = Carbon::parse($selectedDate)->startOfDay();
            $selectedDate = $referenceDate->toDateString();
        } catch (\Throwable $exception) {
            $referenceDate = Carbon::today()->startOfDay();
            $selectedDate = $referenceDate->toDateString();
        }

        switch ($period) {
            case 'weekly':
                $start = $referenceDate->copy()->startOfWeek();
                $end = $referenceDate->copy()->endOfWeek();
                break;
            case 'monthly':
                $start = $referenceDate->copy()->startOfMonth();
                $end = $referenceDate->copy()->endOfMonth();
                break;
            case 'yearly':
                $start = $referenceDate->copy()->startOfYear();
                $end = $referenceDate->copy()->endOfYear();
                break;
            default:
                $start = $referenceDate->copy()->startOfDay();
                $end = $referenceDate->copy()->endOfDay();
        }

        return [$period, $selectedDate, $start, $end];
    }

    private function completedOrders(Carbon $start, Carbon $end)
    {
        return Order::query()
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [
                $start->toDateTimeString(),
                $end->toDateTimeString(),
            ]);
    }

    private function completedItems(Carbon $start, Carbon $end)
    {
        return OrderItem::query()
            ->whereHas('order', function ($query) use ($start, $end) {
                $query->where('status', 'completed')
                    ->whereNotNull('completed_at')
                    ->whereBetween('completed_at', [
                        $start->toDateTimeString(),
                        $end->toDateTimeString(),
                    ]);
            });
    }

    private function expenseTotal(Carbon $start, Carbon $end): float
    {
        return (float) Expense::query()
            ->whereBetween('spent_at', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->sum('amount');
    }

    private function varietyBreakdown(Carbon $start, Carbon $end)
    {
        return $this->completedItems($start, $end)
            ->whereNotNull('unit_cost')
            ->get([
                'product_name',
                'brand',
                'unit_type',
                'quantity',
                'line_total',
                'unit_cost',
            ])
            ->groupBy(function ($item) {
                return trim((string) $item->product_name)
                    . '|' . trim((string) $item->brand);
            })
            ->map(function ($items) {
                $first = $items->first();
                $quantity = (float) $items->sum('quantity');
                $sales = (float) $items->sum('line_total');
                $cost = (float) $items->sum(function ($item) {
                    return (float) $item->unit_cost * (float) $item->quantity;
                });

                return (object) [
                    'product_name' => $first->product_name ?: 'Unnamed product',
                    'brand' => $first->brand ?: '',
                    'quantity' => $quantity,
                    'sales' => $sales,
                    'cost' => $cost,
                    'profit' => $sales - $cost,
                ];
            })
            ->sortBy('product_name')
            ->values();
    }

    public function index(Request $request)
    {
        $period = $request->query('period', 'daily');

        if (!in_array($period, ['daily', 'weekly', 'monthly', 'yearly'], true)) {
            $period = 'daily';
        }

        $selectedDate = $request->query(
            'date',
            Carbon::today()->toDateString()
        );

        try {
            $referenceDate = Carbon::parse($selectedDate)->startOfDay();
            $selectedDate = $referenceDate->toDateString();
        } catch (\Throwable $exception) {
            $referenceDate = Carbon::today()->startOfDay();
            $selectedDate = $referenceDate->toDateString();
        }

        switch ($period) {
            case 'weekly':
                $start = $referenceDate->copy()->startOfWeek();
                $end = $referenceDate->copy()->endOfWeek();
                break;

            case 'monthly':
                $start = $referenceDate->copy()->startOfMonth();
                $end = $referenceDate->copy()->endOfMonth();
                break;

            case 'yearly':
                $start = $referenceDate->copy()->startOfYear();
                $end = $referenceDate->copy()->endOfYear();
                break;

            default:
                $start = $referenceDate->copy()->startOfDay();
                $end = $referenceDate->copy()->endOfDay();
                break;
        }

        /*
         * Completed orders within the selected period.
         */
        $completedQuery = Order::query()
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [
                $start->toDateTimeString(),
                $end->toDateTimeString(),
            ]);

        $salesTotal = (clone $completedQuery)->sum('total');

        $completedOrderCount = (clone $completedQuery)->count();

        /*
         * Item sales and cost only include records with unit_cost.
         */
        $recordedItemQuery = OrderItem::query()
            ->whereHas('order', function ($query) use ($start, $end) {
                $query->where('status', 'completed')
                    ->whereNotNull('completed_at')
                    ->whereBetween('completed_at', [
                        $start->toDateTimeString(),
                        $end->toDateTimeString(),
                    ]);
            })
            ->whereNotNull('unit_cost');

        $recordedItemSales = (clone $recordedItemQuery)
            ->sum('line_total');

        $recordedItemCost = (clone $recordedItemQuery)
            ->selectRaw('COALESCE(SUM(unit_cost * quantity), 0) as aggregate')
            ->value('aggregate');

        $grossProfit = (float) $recordedItemSales
            - (float) $recordedItemCost;

        $itemsWithoutCostCount = OrderItem::query()
            ->whereHas('order', function ($query) use ($start, $end) {
                $query->where('status', 'completed')
                    ->whereNotNull('completed_at')
                    ->whereBetween('completed_at', [
                        $start->toDateTimeString(),
                        $end->toDateTimeString(),
                    ]);
            })
            ->whereNull('unit_cost')
            ->count();

        /*
         * Expenses within the selected period.
         */
        $expensesTotal = Expense::query()
            ->whereBetween('spent_at', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->sum('amount');
        $netProfit = $grossProfit - (float) $expensesTotal;
        $varietyBreakdown = $this->varietyBreakdown($start, $end);

        /*
         * Daily sales breakdown.
         */
        $dailySales = (clone $completedQuery)
            ->selectRaw('
                DATE(completed_at) as sales_date,
                COUNT(*) as order_count,
                SUM(total) as sales_total
            ')
            ->groupByRaw('DATE(completed_at)')
            ->orderByRaw('DATE(completed_at) ASC')
            ->get();

        /*
         * Payment method totals.
         *
         * COD / Cash on Delivery -> Cash
         * Cash                  -> Cash
         * GCash                 -> GCash
         *
         * Other payment values are excluded from this chart rather than
         * incorrectly being counted as Cash.
         */
        $paymentRows = (clone $completedQuery)
            ->where(function ($query) {
                $query->whereRaw("
                    LOWER(REPLACE(TRIM(payment_method), '_', ' ')) IN (
                        'cod',
                        'cash',
                        'cash on delivery',
                        'cash on delivery (cod)'
                    )
                ")->orWhereRaw("
                    LOWER(REPLACE(TRIM(payment_method), '_', ' ')) IN (
                        'gcash',
                        'g cash'
                    )
                ");
            })
            ->selectRaw("
                CASE
                    WHEN LOWER(REPLACE(TRIM(payment_method), '_', ' ')) IN (
                        'cod',
                        'cash',
                        'cash on delivery',
                        'cash on delivery (cod)'
                    ) THEN 'Cash'
                    ELSE 'GCash'
                END as payment_label
            ")
            ->selectRaw('SUM(total) as sales_total')
            ->groupBy('payment_label')
            ->orderByRaw("
                CASE
                    WHEN payment_label = 'Cash' THEN 1
                    ELSE 2
                END
            ")
            ->get();

        $paymentLabels = $paymentRows
            ->pluck('payment_label')
            ->all();

        $paymentData = $paymentRows
            ->pluck('sales_total')
            ->map(fn ($value) => (float) $value)
            ->all();

        /*
         * Completed transaction details, including GCash references.
         */
        $transactionDetails = (clone $completedQuery)
            ->with(['payments'])
            ->orderByDesc('completed_at')
            ->get([
                'id',
                'order_number',
                'completed_at',
                'recipient_name',
                'payment_method',
                'payment_status',
                'total',
            ]);

        return view('admin.reports.index', compact(
            'period',
            'selectedDate',
            'start',
            'end',
            'salesTotal',
            'completedOrderCount',
            'recordedItemSales',
            'recordedItemCost',
            'grossProfit',
            'itemsWithoutCostCount',
            'expensesTotal',
            'netProfit',
            'varietyBreakdown',
            'dailySales',
            'paymentLabels',
            'paymentData',
            'transactionDetails'
        ));
    }

    public function export(Request $request)
    {
        [$period, $selectedDate, $start, $end] = $this->reportDates($request);

        $completedQuery = $this->completedOrders($start, $end);
        $salesTotal = (float) (clone $completedQuery)->sum('total');
        $completedOrderCount = (int) (clone $completedQuery)->count();

        $recordedItems = $this->completedItems($start, $end)
            ->whereNotNull('unit_cost');
        $recordedItemSales = (float) (clone $recordedItems)->sum('line_total');
        $recordedItemCost = (float) (clone $recordedItems)
            ->selectRaw('COALESCE(SUM(unit_cost * quantity), 0) as aggregate')
            ->value('aggregate');

        $grossProfit = $recordedItemSales - $recordedItemCost;
        $expensesTotal = $this->expenseTotal($start, $end);
        $netProfit = $grossProfit - $expensesTotal;
        $itemsWithoutCostCount = $this->completedItems($start, $end)
            ->whereNull('unit_cost')
            ->count();
        $varieties = $this->varietyBreakdown($start, $end);
        $transactions = (clone $completedQuery)
            ->with(['payments'])
            ->orderBy('completed_at')
            ->get([
                'id',
                'order_number',
                'completed_at',
                'recipient_name',
                'payment_method',
                'payment_status',
                'total',
            ]);
        $filename = 'sales-profit-' . $period . '-' . $selectedDate . '.csv';

        return response()->streamDownload(function () use (
            $period,
            $selectedDate,
            $start,
            $end,
            $salesTotal,
            $completedOrderCount,
            $recordedItemSales,
            $recordedItemCost,
            $grossProfit,
            $expensesTotal,
            $netProfit,
            $itemsWithoutCostCount,
            $varieties,
            $transactions
        ) {
            $file = fopen('php://output', 'w');

            if ($file === false) {
                throw new \RuntimeException('Unable to open report output stream.');
            }

            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, ['Sales and Profit Report']);
            fputcsv($file, ['Period', $period]);
            fputcsv($file, ['Reference date', $selectedDate]);
            fputcsv($file, ['Start', $start->toDateTimeString()]);
            fputcsv($file, ['End', $end->toDateTimeString()]);
            fputcsv($file, []);

            fputcsv($file, ['Summary', 'Amount / Count']);
            fputcsv($file, ['Total sales (completed orders)', $salesTotal]);
            fputcsv($file, ['Completed orders', $completedOrderCount]);
            fputcsv($file, ['Recorded item sales', $recordedItemSales]);
            fputcsv($file, ['Recorded item cost', $recordedItemCost]);
            fputcsv($file, ['Recorded gross profit', $grossProfit]);
            fputcsv($file, ['Recorded expenses', $expensesTotal]);
            fputcsv($file, ['Estimated net profit', $netProfit]);
            fputcsv($file, ['Items without recorded cost', $itemsWithoutCostCount]);
            fputcsv($file, []);
            fputcsv($file, [
                'Rice variety / product',
                'Brand',
                'Quantity (mixed units)',
                'Recorded item sales',
                'Recorded item cost',
                'Estimated profit',
            ]);

            foreach ($varieties as $variety) {
                fputcsv($file, [
                    $variety->product_name,
                    $variety->brand,
                    $variety->quantity,
                    $variety->sales,
                    $variety->cost,
                    $variety->profit,
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['Completed Transaction Details']);
            fputcsv($file, [
                'Order Number',
                'Date Completed',
                'Customer',
                'Payment Method',
                'Payment Status',
                'GCash Reference Number',
                'Transaction Total',
            ]);

            foreach ($transactions as $transaction) {
                $referenceNumber = '';

                if (strtolower(trim((string) $transaction->payment_method)) === 'gcash') {
                    $payment = $transaction->payments->first(
                        fn ($payment) => strtolower(trim((string) $payment->method)) === 'gcash'
                    );

                    $referenceNumber = $payment?->reference_number ?? '';
                }

                fputcsv($file, [
                    $transaction->order_number,
                    $transaction->completed_at,
                    $transaction->recipient_name ?: 'N/A',
                    $transaction->payment_method ?: 'N/A',
                    $transaction->payment_status ?: 'Unknown',
                    $referenceNumber,
                    $transaction->total,
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, [
                'Note',
                'Profit includes only completed order items with recorded unit cost.',
            ]);
            fputcsv($file, [
                'Note',
                'Quantities may mix sacks and kilograms; use sales, cost and profit for financial totals.',
            ]);

            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}