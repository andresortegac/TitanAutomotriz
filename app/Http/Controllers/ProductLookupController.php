<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductLookupController extends Controller
{
    public function __invoke(Request $request)
    {
        $search = trim((string) $request->query('code', ''));

        if ($search === '') {
            return response()->json([
                'success' => false,
                'message' => 'Escribe o escanea un codigo.',
            ], 422);
        }

        $product = Product::with('primaryBarcode')
            ->where('active', true)
            ->where(function ($query) use ($search): void {
                $query->where('code', $search)
                    ->orWhere('sku', $search)
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhereHas('barcodes', function ($barcodes) use ($search): void {
                        $barcodes->where('code', $search)->where('active', true);
                    });
            })
            ->first();

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado.',
            ], 404);
        }

        if ($product->stock <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Producto agotado.',
            ], 409);
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'code' => $product->code,
                'sku' => $product->sku,
                'barcode' => $product->primaryBarcode?->code,
                'barcode_type' => $product->primaryBarcode?->type,
                'name' => $product->name,
                'price' => (float) $product->sale_price,
                'tax_rate' => (float) $product->tax_rate,
                'stock' => $product->stock,
            ],
        ]);
    }
}
