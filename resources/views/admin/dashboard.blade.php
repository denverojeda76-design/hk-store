@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<style>
    .store-dashboard {
        --dashboard-green: #168653;
        --dashboard-green-dark: #116b42;
        --dashboard-green-soft: #e8f6ef;
        --dashboard-border: #e5ece7;
        --dashboard-muted: #748078;
        color: #23332a;
    }

    .store-dashboard * {
        box-sizing: border-box;
    }

    .dashboard-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        margin-bottom: 16px;
        border: 1px solid var(--dashboard-border);
        border-radius: 12px;
        background: #fff;
    }

    .dashboard-heading h1 {
        margin: 0 0 4px;
        color: #203d2c;
        font-size: 22px;
    }

    .dashboard-heading p {
        margin: 0;
        color: var(--dashboard-muted);
        font-size: 12px;
    }

    .dashboard-live {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        flex-shrink: 0;
        padding: 7px 10px;
        border: 1px solid #d5ebdd;
        border-radius: 20px;
        background: #f3fbf6;
        color: var(--dashboard-green-dark);
        font-size: 11px;
        font-weight: 700;
    }

    .dashboard-live::before {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--dashboard-green);
        content: "";
    }

    .dashboard-kpis {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 14px;
    }

    .dashboard-kpi {
        min-width: 0;
        padding: 16px;
        border: 1px solid var(--dashboard-border);
        border-radius: 12px;
        background: #fff;
    }

    .dashboard-kpi.primary {
        border-color: var(--dashboard-green);
        background: var(--dashboard-green);
        color: #fff;
    }

    .dashboard-kpi-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 12px;
        color: #53645a;
        font-size: 12px;
        font-weight: 600;
    }

    .dashboard-kpi.primary .dashboard-kpi-label {
        color: #e9fff2;
    }

    .dashboard-kpi-value {
        display: block;
        overflow-wrap: anywhere;
        color: #1d2b22;
        font-size: clamp(20px, 2vw, 26px);
        font-weight: 800;
        line-height: 1.2;
    }

    .dashboard-kpi.primary .dashboard-kpi-value {
        color: #fff;
    }

    .dashboard-kpi-note {
        display: block;
        margin-top: 7px;
        color: var(--dashboard-muted);
        font-size: 11px;
    }

    .dashboard-kpi.primary .dashboard-kpi-note {
        color: #ddf5e6;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(260px, .9fr);
        gap: 14px;
        align-items: stretch;
        margin-bottom: 14px;
    }

    .dashboard-panel {
        min-width: 0;
        padding: 16px;
        border: 1px solid var(--dashboard-border);
        border-radius: 12px;
        background: #fff;
    }

    .dashboard-panel-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }

    .dashboard-panel-heading h2 {
        margin: 0;
        color: #233d2c;
        font-size: 15px;
    }

    .dashboard-panel-heading small {
        color: var(--dashboard-muted);
        font-size: 11px;
    }

    .dashboard-chart {
        position: relative;
        width: 100%;
        height: 235px;
    }

    .dashboard-chart.doughnut {
        height: 210px;
        max-width: 300px;
        margin: 0 auto;
    }

    .category-legend {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 7px 12px;
        margin-top: 12px;
    }

    .category-legend-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        min-width: 0;
        color: #4a5b50;
        font-size: 11px;
    }

    .category-legend-item span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .category-legend-item strong {
        color: #253b2d;
        white-space: nowrap;
    }

    .dashboard-lower-grid {
        display: grid;
        grid-template-columns: minmax(220px, .8fr) minmax(0, 1.7fr);
        gap: 14px;
        align-items: start;
        margin-bottom: 14px;
    }

    .top-product-list {
        display: grid;
        gap: 8px;
    }

    .top-product {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px;
        border-radius: 8px;
        background: #f7faf8;
    }

    .top-product-name {
        min-width: 0;
        color: #263a2d;
        font-size: 12px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .top-product-quantity {
        display: block;
        margin-top: 3px;
        color: var(--dashboard-muted);
        font-size: 10px;
        font-weight: 400;
    }

    .top-product-sales {
        flex-shrink: 0;
        color: #244b34;
        font-size: 12px;
        font-weight: 700;
    }

    .dashboard-orders-wrap {
        overflow-x: auto;
    }

    .dashboard-orders {
        width: 100%;
        min-width: 500px;
        border-collapse: collapse;
    }

    .dashboard-orders th {
        padding: 9px 8px;
        border-bottom: 1px solid var(--dashboard-border);
        background: #fff;
        color: var(--dashboard-muted);
        font-size: 10px;
        font-weight: 700;
        text-align: left;
        white-space: nowrap;
    }

    .dashboard-orders td {
        padding: 10px 8px;
        border-bottom: 1px solid #eef2ef;
        color: #34443a;
        font-size: 11px;
        vertical-align: middle;
    }

    .dashboard-orders tbody tr:last-child td {
        border-bottom: 0;
    }

    .dashboard-order-number {
        color: #234b34;
        font-weight: 700;
    }

    .dashboard-status {
        display: inline-flex;
        padding: 4px 8px;
        border-radius: 20px;
        background: var(--dashboard-green-soft);
        color: var(--dashboard-green-dark);
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .dashboard-status.pending,
    .dashboard-status.accepted {
        background: #f0f7f2;
        color: #3b654a;
    }

    .dashboard-status.cancelled {
        background: #f2f5f3;
        color: #627067;
    }

    .dashboard-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 14px;
    }

    .dashboard-action-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        padding: 8px 12px;
        border: 1px solid var(--dashboard-green);
        border-radius: 6px;
        background: var(--dashboard-green);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
    }

    .dashboard-action-button:hover {
        border-color: var(--dashboard-green-dark);
        background: var(--dashboard-green-dark);
        color: #fff;
    }

    .dashboard-empty {
        padding: 18px 10px;
        color: var(--dashboard-muted);
        font-size: 12px;
        text-align: center;
    }

    .dashboard-status-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 14px;
    }

    .dashboard-status-count {
        padding: 11px;
        border: 1px solid var(--dashboard-border);
        border-radius: 8px;
        background: #fff;
    }

    .dashboard-status-count span {
        display: block;
        color: var(--dashboard-muted);
        font-size: 10px;
    }

    .dashboard-status-count strong {
        display: block;
        margin-top: 4px;
        color: #264333;
        font-size: 17px;
    }

    @media (max-width: 1050px) {
        .dashboard-kpis {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dashboard-grid {
            grid-template-columns: minmax(0, 1.3fr) minmax(240px, .9fr);
        }
    }

    @media (max-width: 760px) {
        .dashboard-grid,
        .dashboard-lower-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .dashboard-chart.doughnut {
            height: 190px;
        }
    }

    @media (max-width: 520px) {
        .dashboard-heading {
            align-items: flex-start;
            padding: 14px;
        }

        .dashboard-heading h1 {
            font-size: 19px;
        }

        .dashboard-live {
            padding: 6px 8px;
            font-size: 10px;
        }

        .dashboard-kpis {
            gap: 8px;
        }

        .dashboard-kpi {
            padding: 12px;
        }

        .dashboard-kpi-label {
            font-size: 10px;
        }

        .dashboard-kpi-note {
            font-size: 10px;
        }

        .dashboard-status-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>

<div class="store-dashboard">
    <header class="dashboard-heading">
        <div>
            <h1>Dashboard Overview</h1>
            <p>Welcome back! Here is your store's live performance view.</p>
        </div>
        <span class="dashboard-live">Live data</span>
    </header>

    <section class="dashboard-kpis" aria-label="Store overview">
        <article class="dashboard-kpi primary">
            <span class="dashboard-kpi-label">Revenue this month</span>
            <strong class="dashboard-kpi-value">₱{{ number_format((float) $monthSales, 2) }}</strong>
            <span class="dashboard-kpi-note">₱{{ number_format((float) $todaySales, 2) }} completed today</span>
        </article>
        <article class="dashboard-kpi">
            <span class="dashboard-kpi-label">Orders this month</span>
            <strong class="dashboard-kpi-value">{{ number_format($ordersThisMonth) }}</strong>
            <span class="dashboard-kpi-note">{{ number_format($pendingOrders) }} waiting for action</span>
        </article>
        <article class="dashboard-kpi">
            <span class="dashboard-kpi-label">Active products</span>
            <strong class="dashboard-kpi-value">{{ number_format($activeProductCount) }}</strong>
            <span class="dashboard-kpi-note">{{ number_format($lowStockCount) }} low-stock alerts</span>
        </article>
        <article class="dashboard-kpi">
            <span class="dashboard-kpi-label">Customers</span>
            <strong class="dashboard-kpi-value">{{ number_format($customerCount) }}</strong>
            <span class="dashboard-kpi-note">Registered customer accounts</span>
        </article>
    </section>

    <section class="dashboard-grid">
        <article class="dashboard-panel">
            <div class="dashboard-panel-heading">
                <h2>Sales Trend</h2>
                <small>Completed sales · last 7 days</small>
            </div>
            <div class="dashboard-chart">
                <canvas id="salesTrendsChart" aria-label="Sales over the last seven days"></canvas>
            </div>
        </article>

        <article class="dashboard-panel">
            <div class="dashboard-panel-heading">
                <h2>Sales by Category</h2>
                <small>This month</small>
            </div>
            @if ($salesByCategory->isNotEmpty())
                <div class="dashboard-chart doughnut">
                    <canvas id="categorySalesChart" aria-label="Sales split by product category"></canvas>
                </div>
                <div class="category-legend">
                    @foreach ($salesByCategory as $categorySale)
                        <div class="category-legend-item">
                            <span>{{ $categorySale->category }}</span>
                            <strong>₱{{ number_format((float) $categorySale->total, 0) }}</strong>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="dashboard-empty">No completed category sales this month yet.</div>
            @endif
        </article>
    </section>

    <section class="dashboard-lower-grid">
        <article class="dashboard-panel">
            <div class="dashboard-panel-heading">
                <h2>Top Products</h2>
                <small>This month</small>
            </div>
            @if ($topProducts->isNotEmpty())
                <div class="top-product-list">
                    @foreach ($topProducts as $product)
                        <div class="top-product">
                            <div class="top-product-name">
                                {{ $product->product_name }}
                                <span class="top-product-quantity">{{ number_format((float) $product->quantity_sold, 0) }} sold</span>
                            </div>
                            <span class="top-product-sales">₱{{ number_format((float) $product->sales_total, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="dashboard-empty">No completed product sales this month yet.</div>
            @endif
        </article>

        <article class="dashboard-panel">
            <div class="dashboard-panel-heading">
                <h2>Recent Orders</h2>
                <a class="dashboard-action-button" href="{{ route('admin.orders.index') }}">All orders</a>
            </div>
            @if ($recentOrders->isNotEmpty())
                <div class="dashboard-orders-wrap">
                    <table class="dashboard-orders">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentOrders as $order)
                                <tr>
                                    <td>
                                        <a class="dashboard-order-number" href="{{ route('admin.orders.show', $order) }}">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td>{{ $order->recipient_name ?: ($order->customer?->name ?? 'Customer') }}</td>
                                    <td>{{ $order->created_at?->format('M d') }}</td>
                                    <td><span class="dashboard-status {{ strtolower($order->status) }}">{{ ucfirst($order->status) }}</span></td>
                                    <td>₱{{ number_format((float) $order->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="dashboard-empty">No orders have been placed yet.</div>
            @endif
        </article>
    </section>

    <nav class="dashboard-actions" aria-label="Dashboard shortcuts">
        <a class="dashboard-action-button" href="{{ route('admin.orders.index') }}">Manage Orders</a>
        <a class="dashboard-action-button" href="{{ route('admin.inventory.index') }}">Inventory</a>
        <a class="dashboard-action-button" href="{{ route('admin.products.index') }}">Products</a>
        <a class="dashboard-action-button" href="{{ route('admin.reports.index') }}">Reports</a>
        <a class="dashboard-action-button" href="{{ route('admin.expenses.index') }}">Expenses</a>
    </nav>

    <section class="dashboard-status-summary" aria-label="Order status totals">
        <div class="dashboard-status-count"><span>Pending</span><strong>{{ number_format($pendingOrders) }}</strong></div>
        <div class="dashboard-status-count"><span>Accepted</span><strong>{{ number_format($acceptedOrders) }}</strong></div>
        <div class="dashboard-status-count"><span>Completed</span><strong>{{ number_format($completedOrders) }}</strong></div>
        <div class="dashboard-status-count"><span>Cancelled</span><strong>{{ number_format($cancelledOrders) }}</strong></div>
    </section>

    <section class="dashboard-panel">
        <div class="dashboard-panel-heading">
            <h2>Low Stock Alerts</h2>
            <a class="dashboard-action-button" href="{{ route('admin.inventory.index') }}">Open inventory</a>
        </div>
        @if ($lowStockProducts->isNotEmpty())
            <div class="dashboard-orders-wrap">
                <table class="dashboard-orders">
                    <thead>
                        <tr><th>Product</th><th>Available stock</th><th>Alert threshold</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($lowStockProducts as $product)
                            @php
                                $isGenericProduct = ($product->inventory_type ?? 'rice') === 'generic';
                                $stockQuantity = (float) ($isGenericProduct ? $product->stock_base_quantity : $product->stock_kg);
                                $stockUnit = $isGenericProduct
                                    ? ($product->base_unit ?: $product->stock_unit ?: 'unit')
                                    : 'kg';
                                $thresholdQuantity = (float) ($isGenericProduct
                                    ? $product->low_stock_threshold_quantity
                                    : $product->low_stock_threshold);
                            @endphp
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ number_format($stockQuantity, 2) }} {{ $stockUnit }}</td>
                                <td>{{ number_format($thresholdQuantity, 2) }} {{ $stockUnit }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="dashboard-empty">No active products are currently at or below their stock threshold.</div>
        @endif
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') {
            return;
        }

        const salesCanvas = document.getElementById('salesTrendsChart');
        if (salesCanvas) {
            new Chart(salesCanvas, {
                type: 'line',
                data: {
                    labels: @json($salesChartLabels),
                    datasets: [{
                        label: 'Sales (₱)',
                        data: @json($salesChartData),
                        borderColor: '#168653',
                        backgroundColor: 'rgba(22, 134, 83, .12)',
                        fill: true,
                        tension: .38,
                        pointRadius: 3,
                        pointHoverRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return '₱' + Number(context.parsed.y).toLocaleString('en-PH', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#edf2ee' },
                            ticks: {
                                callback: function (value) {
                                    return '₱' + Number(value).toLocaleString('en-PH');
                                }
                            }
                        }
                    }
                }
            });
        }

        const categoryCanvas = document.getElementById('categorySalesChart');
        if (categoryCanvas) {
            new Chart(categoryCanvas, {
                type: 'doughnut',
                data: {
                    labels: @json($salesByCategory->pluck('category')->values()),
                    datasets: [{
                        data: @json($salesByCategory->pluck('total')->map(fn ($total) => (float) $total)->values()),
                        backgroundColor: ['#168653', '#42a878', '#72c49a', '#a2dabc', '#c4e9d3', '#dcefe3'],
                        borderWidth: 3,
                        borderColor: '#fff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '66%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.label + ': ₱' + Number(context.parsed).toLocaleString('en-PH', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    }
                }
            });
        }
    });

    window.setTimeout(function () {
        window.location.reload();
    }, 60000);
</script>
@endsection
