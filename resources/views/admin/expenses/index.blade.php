 @extends('admin.layouts.app')

@section('title', 'Expenses')

@section('content')
    <div class="page-heading">
        <h1>Expenses</h1>
        <p>Record and monitor business expenses.</p>
    </div>

    @if (session('success'))
        <div class="notice success" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="notice error" role="alert">
            <strong>Please correct the following errors:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="summary-grid">
        <div class="summary-card">
            <p>Total Recorded Expenses</p>
            <strong>₱{{ number_format((float) $totalExpenses, 2) }}</strong>
        </div>

        <div class="summary-card">
            <p>Expenses Recorded Today</p>
            <strong>₱{{ number_format((float) $todayExpenses, 2) }}</strong>
        </div>
    </div>

    <div class="panel">
        <h2>Record an Expense</h2>
        <p>The expense will be saved to the business expense records when you submit this form.</p>

        <form method="POST" action="{{ route('admin.expenses.store') }}">
            @csrf

            <div class="fields">
                <div class="field">
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        <option value="">Select a category</option>
                        @foreach ([
                            'Food',
                            'Rice purchase',
                            'Transportation',
                            'Delivery',
                            'Rent',
                            'Utilities',
                            'Salaries',
                            'Packaging',
                            'Maintenance',
                            'Other'
                        ] as $category)
                            <option value="{{ $category }}" @selected(old('category') === $category)>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="amount">Amount (₱)</label>
                    <input id="amount" type="number" name="amount"
                        min="0.01" max="99999999.99" step="0.01"
                        value="{{ old('amount') }}" placeholder="0.00" required>
                </div>

                <div class="field">
                    <label for="spent_at">Expense Date</label>
                    <input id="spent_at" type="date" name="spent_at"
                        value="{{ old('spent_at', now()->toDateString()) }}"
                        max="{{ now()->toDateString() }}" required>
                </div>

                <div class="field">
                    <label for="description">Description (Optional)</label>
                    <input id="description" type="text" name="description"
                        maxlength="1000" value="{{ old('description') }}"
                        placeholder="e.g. Rice delivery">
                </div>

                <div class="field">
                    <label for="notes">Notes (Optional)</label>
                    <textarea id="notes" name="notes" rows="3" maxlength="2000"
                        placeholder="Additional details or reference">{{ old('notes') }}</textarea>
                </div>
            </div>

            <button class="button" type="submit">Save Expense</button>
        </form>
    </div>

    <div class="panel">
        <h2>Expense History</h2>
        <p>Showing the latest 100 expense records.</p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description / Notes</th>
                        <th>Recorded By</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr>
                            <td>{{ $expense->spent_at->format('M d, Y') }}</td>
                            <td>{{ $expense->category }}</td>
                            <td>
                                {{ $expense->description ?: '—' }}

                                @if ($expense->notes)
                                    <br>
                                    <small>{{ $expense->notes }}</small>
                                @endif
                            </td>
                            <td>{{ $expense->creator?->name ?? 'Unknown' }}</td>
                            <td><strong>₱{{ number_format((float) $expense->amount, 2) }}</strong></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No expense records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection