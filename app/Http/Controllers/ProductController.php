<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Redirect the owner/admin to the admin dashboard.
        if (auth()->check() && auth()->user()->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        // Validate storefront search and filter inputs.
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in([
                'name',
                'price_asc',
                'price_desc',
            ])],
        ]);

        // Retrieve active products for customers and guests.
        $query = Product::query()
            ->where('is_active', true);

        if (!empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('rice_type', 'like', "%{$search}%");
            });
        }

        if (!empty($validated['type'])) {
            $query->where('rice_type', $validated['type']);
        }

        $sort = $validated['sort'] ?? 'name';

        if ($sort === 'price_asc') {
            $query->orderByRaw(
                'CASE WHEN price_per_kg IS NULL THEN 1 ELSE 0 END'
            )->orderBy('price_per_kg');
        } elseif ($sort === 'price_desc') {
            $query->orderByRaw(
                'CASE WHEN price_per_kg IS NULL THEN 1 ELSE 0 END'
            )->orderByDesc('price_per_kg');
        } else {
            $query->orderBy('name');
        }

        $products = $query->get();

        // Retrieve available rice types for the storefront filter.
        $riceTypes = Product::where('is_active', true)
            ->whereNotNull('rice_type')
            ->distinct()
            ->orderBy('rice_type')
            ->pluck('rice_type');

        return view('home', compact(
            'products',
            'riceTypes',
            'sort'
        ));
    }
}