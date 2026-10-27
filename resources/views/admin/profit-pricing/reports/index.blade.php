@extends('admin.layouts.app')

@section('title', 'Reports')

@section('content')
    <div class="page-heading">
        <h1>Business Reports</h1>
        <p>Review completed sales, recorded item costs, and business expenses.</p>
    </div>

    <div class="panel">
        <h2>Report Filters</h2>

        <form method="GET" action="{{ route('admin.reports.index') }}">
            <div class="fields">
                <div class="field">
                    <label for="period">Report Period</label>
                    <select id="period" name="period" required>
                        <option value="daily" @selected($period === 'daily')>Daily</option>
                        <option value="weekly" @selected($period === 'weekly')>Weekly</option>
                        <option value="monthly" @selected($period === 'monthly')>Monthly</option>
                        <option value="yearly" @selected($period === 'yearly')>Yearly</option>
                    </select>
                </div>

                <div class="field">
                    <label for="date">Reference Date</label>
                    <input
                        id="date"
                        type="date"
                        name="date"
                        value="{{ $selectedDate->toDateString() }}"
                        required
                    >
                </div>
            </div>

            <p class="muted">
                Selected period: {{ $start->format('M d, Y') }}
                – {{ $end->format('M d, Y') }}
            </p>

            <button class="button" type="submit">Generate Report</button>
        </form>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <p>Completed Sales</p>
            <strong>₱{{ number_format((float) $salesTotal, 2) }}</strong>
        </div>

        <div class="summary-card">
            <p>Completed Orders</p>
            <strong>{{ number_format($completedOrderCount) }}</strong>
        </div>

        <div class="summary-card">
            <p>Recorded Expenses</p>
            <strong>₱{{ number_format((float) $expensesTotal, 2) }}</strong>
        </div>

        <div class="summary-card">
            <p>Gross Profit on Items with Recorded Costs</p>
            <strong>₱{{ number_format((float) $grossProfit, 2) }}</strong>
        </div>

        <div class="summary-card">
            <p>Recorded Cost for Those Items</p>
            <strong>₱{{ number_format((float) $recordedItemCost, 2) }}</strong>
        </div>

        <div class="summary-card">
            <p>Items Without Recorded Cost</p>
            <strong>{{ number_format($itemsWithoutCostCount) }}</strong>
        </div>
    </div>

    <div class="panel" style="margin-top:20px;">
        <h2>Completed Sales by Date</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date Completed</th>
                        <th>Completed Orders</th>
                        <th>Sales Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dailySales as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row->report_date)->format('M d, Y') }}</td>
                            <td>{{ number_format($row->order_count) }}</td>
                            <td>₱{{ number_format((float) $row->sales_total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">No completed sales were found for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <h2>Completed Order Details</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Completion Date</th>
                        <th>Customer</th>
                        <th>Recorded Item Costs</th>
                        <th>Order Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($completedOrders as $order)
                        @php
                            $orderRecordedCost = $order->items
                                ->filter(fn ($item) => $item->unit_cost !== null)
                                ->sum(fn ($item) =>
                                    (float) $item->unit_cost * (int) $item->quantity
                                );

                            $hasUnrecordedCost = $order->items
                                ->contains(fn ($item) => $item->unit_cost === null);
                        @endphp

                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->completed_at?->format('M d, Y H:i') ?? 'Not available' }}</td>
                            <td>{{ $order->recipient_name ?: ($order->customer?->name ?? 'Unknown') }}</td>
                            <td>
                                ₱{{ number_format((float) $orderRecordedCost, 2) }}
                                @if ($hasUnrecordedCost)
                                    <br><span class="muted">Some item costs not recorded</span>
                                @endif
                            </td>
                            <td>₱{{ number_format((float) $order->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No completed orders found for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <p class="muted">
        Reports include orders marked completed with a completion date.
        Gross profit is calculated only for items with recorded unit costs.
        Recorded expenses are shown separately and are not deducted from gross profit.
        Historical items without costs are not assigned estimated costs.
    </p>
@endsection