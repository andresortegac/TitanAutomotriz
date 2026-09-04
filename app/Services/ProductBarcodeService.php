<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBarcode;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductBarcodeService
{
    public function __construct(private readonly BarcodeService $barcodes)
    {
    }

    public function syncPrimaryBarcode(Product $product, ?string $code, string $type, ?int $userId = null): ProductBarcode
    {
        return DB::transaction(function () use ($product, $code, $type, $userId): ProductBarcode {
            $manualCode = filled($code);
            $code = filled($code)
                ? $this->barcodes->normalize($code, $type)
                : $this->barcodes->generateInternalBarcode($product);

            $this->barcodes->validate($code, $type);
            $this->ensureAvailable($code, $product);

            $primaryBarcode = ProductBarcode::where('product_id', $product->id)
                ->where('is_primary', true)
                ->first();

            if (! $primaryBarcode) {
                $primaryBarcode = ProductBarcode::where('product_id', $product->id)->first();
            }

            ProductBarcode::where('product_id', $product->id)->update(['is_primary' => false]);

            $primaryBarcode ??= new ProductBarcode(['product_id' => $product->id]);

            $primaryBarcode->fill([
                'code' => $code,
                'type' => $type,
                'source' => $manualCode ? 'manual' : 'internal',
                'is_primary' => true,
                'active' => true,
                'created_by' => $primaryBarcode->exists ? $primaryBarcode->created_by : $userId,
                'updated_by' => $userId,
            ]);
            $primaryBarcode->save();

            return $primaryBarcode;
        });
    }

    public function ensureAvailable(string $code, ?Product $product = null): void
    {
        $existing = ProductBarcode::with('product')
            ->where('code', $code)
            ->when($product, fn ($query) => $query->where('product_id', '!=', $product->id))
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'barcode' => "Este codigo de barras ya esta asignado al producto: {$existing->product->name}.",
            ]);
        }
    }
}
