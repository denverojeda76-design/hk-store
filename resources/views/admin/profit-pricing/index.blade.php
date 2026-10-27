@extends('admin.layouts.app')

@section('title', 'Profit & Pricing')

@section('content')
    <div class="page-heading">
        <h1>Profit &amp; Pricing</h1>
        <p>Review product costs, target profit percentages, and suggested selling prices.</p>
    </div>

    <div class="panel">
        <h2>Pricing Formula</h2>
        <p class="muted">
            Suggested selling price = Cost per sack × (1 + Profit percentage ÷ 100).
        </p>
        <p class="muted">
            Example: ₱1,000 cost with a 10% target profit gives a suggested price of ₱1,100.
            This recommendation does not automatically change the current selling price.
        </p>
        <a class="button" href="{{ route('admin.dashboard') }}">
            Edit Product Costs and Pricing
        </a>
    </div>

    <div class="panel">
        <h2>Product Pricing Overview</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Sack Weight</th>
                        <th>Current Sack Price</th>
                        <th>Cost per Sack</th>
                        <th>Target Profit</th>
                        <th>Suggested Price</th>
                        <th>Estimated Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        @php
                            $hasCost = $product->cost_per_sack !== null;
                            $hasProfit = $product->profit_percentage !== null;

                            $suggestedPrice = ($hasCost && $hasProfit)
                                ? (float) $product->cost_per_sack
                                    * (1 + (float) $product->profit_percentage / 100)
                                : null;

                            $estimatedProfit = $suggestedPrice !== null
                                ? $suggestedPrice - (float) $product->cost_per_sack
                                : null;
                        @endphp

                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>
                                {{ $product->sack_weight_kg
                                    ? number_format($product->sack_weight_kg) . ' kg'
                                    : 'Not set' }}
                            </td>
                            <td>
                                @if ($product->price_per_sack !== null)
                                    ₱{{ number_format((float) $product->price_per_sack, 2) }}
                                @else
                                    Not set
                                @endif
                            </td>
                            <td>
                                @if ($hasCost)
                                    ₱{{ number_format((float) $product->cost_per_sack, 2) }}
                                @else
                                    Not recorded
                                @endif
                            </td>
                            <td>
                                @if ($hasProfit)
                                    {{ number_format((float) $product->profit_percentage, 2) }}%
                                @else
                                    Not set
                                @endif
                            </td>
                            <td>
                                @if ($suggestedPrice !== null)
                                    ₱{{ number_format($suggestedPrice, 2) }}
                                @else
                                    Not available
                                @endif
                            </td>
                            <td>
                                @if ($estimatedProfit !== null)
                                    ₱{{ number_format($estimatedProfit, 2) }}
                                @else
                                    Not available
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="muted">
            Estimated profit is based on the configured cost and target percentage.
            It is not actual sales profit.
        </p>
    </div>
@endsection