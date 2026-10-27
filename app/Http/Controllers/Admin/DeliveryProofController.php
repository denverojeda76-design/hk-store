<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Storage;

class DeliveryProofController extends Controller
{
    public function __invoke(Order $order)
    {
        abort_unless(
            $order->delivery_proof_path,
            404,
            'No delivery proof has been uploaded for this order.'
        );

        $disk = Storage::disk('local');
        $path = $order->delivery_proof_path;

        abort_unless(
            $disk->exists($path),
            404,
            'The delivery proof file could not be found.'
        );

        return response()->file(
            $disk->path($path),
            [
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
                'Content-Disposition' => 'inline',
            ]
        );
    }
}
