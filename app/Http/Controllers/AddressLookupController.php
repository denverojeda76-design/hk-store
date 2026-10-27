<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AddressLookupController extends Controller
{
    public function lookup(Request $request)
    {
        $validated = $request->validate([
            'address' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        $apiKey = config('services.geoapify.key');

        if (!is_string($apiKey) || trim($apiKey) === '') {
            return response()->json([
                'message' => 'Address lookup is not configured. Please contact the store.',
            ], 503);
        }

        $address = trim($validated['address']);
        $cacheKey = 'geoapify_address_' . hash('sha256', mb_strtolower($address));

        try {
            $results = Cache::remember(
                $cacheKey,
                now()->addHours(12),
                function () use ($address, $apiKey) {
                    $response = Http::acceptJson()
                        ->timeout(10)
                        ->get('https://api.geoapify.com/v1/geocode/search', [
                            'text' => $address . ', Abuyog, Leyte, Philippines',
                            'filter' => 'countrycode:ph',
                            'limit' => 5,
                            'lang' => 'en',
                            'apiKey' => $apiKey,
                        ]);

                    $response->throw();

                    return $response->json('features', []);
                }
            );
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Address lookup is temporarily unavailable. Please try again.',
            ], 502);
        }

        $barangayZones = app(CheckoutController::class)
            ->getConfiguredBarangayZonesForLookup();

        // The checkout controller will expose its existing configured
        // barangay map through the small method added in the next step.
        if (!is_array($barangayZones) || empty($barangayZones)) {
            return response()->json([
                'message' => 'Unable to verify this address. Please contact the store.',
            ], 422);
        }

        $matches = [];

        foreach ($results as $feature) {
            $properties = $feature['properties'] ?? [];

            $country = mb_strtolower((string) ($properties['country'] ?? ''));
            $state = mb_strtolower((string) ($properties['state'] ?? ''));
            $city = mb_strtolower((string) (
                $properties['city']
                ?? $properties['municipality']
                ?? $properties['county']
                ?? ''
            ));

            if (
                $country !== 'philippines'
                || !str_contains($state, 'leyte')
                || !str_contains($city, 'abuyog')
            ) {
                continue;
            }

            $components = [
                $properties['suburb'] ?? null,
                $properties['village'] ?? null,
                $properties['district'] ?? null,
                $properties['neighbourhood'] ?? null,
                $properties['quarter'] ?? null,
            ];

            foreach ($components as $component) {
                if (!is_string($component) || trim($component) === '') {
                    continue;
                }

                foreach ($barangayZones as $barangayName => $zone) {
                    if (mb_strtolower(trim($component)) === mb_strtolower($barangayName)) {
                        $matches[$barangayName] = $zone;
                    }
                }
            }
        }

        if (count($matches) !== 1) {
            return response()->json([
                'message' => 'Hindi matukoy nang tiyak ang barangay. Pakidagdagan ang address o piliin ang barangay kung available.',
            ], 422);
        }

        $barangay = array_key_first($matches);

        return response()->json([
            'barangay' => $barangay,
            'message' => 'Nakita ang posibleng barangay. Pakisuri bago magpatuloy.',
        ]);
    }

    public function suggestions(Request $request)
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        $apiKey = config('services.geoapify.key');

        if (!is_string($apiKey) || trim($apiKey) === '') {
            return response()->json([
                'message' => 'Address suggestions are not configured.',
            ], 503);
        }

        try {
            $response = Http::acceptJson()
                ->timeout(8)
                ->get('https://api.geoapify.com/v1/geocode/autocomplete', [
                    'text' => trim($validated['text']) . ', Abuyog, Leyte, Philippines',
                    'filter' => 'countrycode:ph',
                    'limit' => 5,
                    'lang' => 'en',
                    'apiKey' => $apiKey,
                ]);

            $response->throw();

            $features = $response->json('features', []);
            $suggestions = [];

            foreach ($features as $feature) {
                $properties = $feature['properties'] ?? [];
                $formatted = $properties['formatted'] ?? null;

                if (!is_string($formatted) || trim($formatted) === '') {
                    continue;
                }

                $suggestions[] = [
                    'formatted' => $formatted,
                    'city' => $properties['city']
                        ?? $properties['municipality']
                        ?? '',
                    'province' => $properties['state'] ?? '',
                    'country' => $properties['country'] ?? '',
                    'suburb' => $properties['suburb'] ?? '',
                    'village' => $properties['village'] ?? '',
                ];
            }

            return response()->json(['suggestions' => $suggestions]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Address suggestions are temporarily unavailable.',
            ], 502);
        }
    }
}
