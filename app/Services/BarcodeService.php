<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBarcode;
use Illuminate\Validation\ValidationException;

class BarcodeService
{
    public const TYPES = [
        ProductBarcode::TYPE_CODE128,
        ProductBarcode::TYPE_EAN13,
        ProductBarcode::TYPE_EAN8,
        ProductBarcode::TYPE_UPCA,
    ];

    public function generateSku(Product $product): string
    {
        return 'FER-'.str_pad((string) $product->id, 6, '0', STR_PAD_LEFT);
    }

    public function generateInternalBarcode(Product $product): string
    {
        return 'FER'.str_pad((string) $product->id, 8, '0', STR_PAD_LEFT);
    }

    public function normalize(string $code, string $type = ProductBarcode::TYPE_CODE128): string
    {
        $code = strtoupper(trim($code));

        if ($type === ProductBarcode::TYPE_CODE128) {
            return preg_replace('/\s+/', '', $code);
        }

        return preg_replace('/\D+/', '', $code);
    }

    public function validate(string $code, string $type): void
    {
        if (! in_array($type, self::TYPES, true)) {
            throw ValidationException::withMessages([
                'barcode_type' => 'Tipo de codigo de barras no valido.',
            ]);
        }

        if ($type === ProductBarcode::TYPE_CODE128 && (strlen($code) < 4 || strlen($code) > 80)) {
            throw ValidationException::withMessages([
                'barcode' => 'El codigo CODE128 debe tener entre 4 y 80 caracteres.',
            ]);
        }

        if ($type === ProductBarcode::TYPE_EAN13 && (! preg_match('/^\d{13}$/', $code) || ! $this->hasValidCheckDigit($code))) {
            throw ValidationException::withMessages([
                'barcode' => 'El codigo EAN-13 no es valido.',
            ]);
        }

        if ($type === ProductBarcode::TYPE_EAN8 && (! preg_match('/^\d{8}$/', $code) || ! $this->hasValidCheckDigit($code))) {
            throw ValidationException::withMessages([
                'barcode' => 'El codigo EAN-8 no es valido.',
            ]);
        }

        if ($type === ProductBarcode::TYPE_UPCA && (! preg_match('/^\d{12}$/', $code) || ! $this->hasValidCheckDigit($code))) {
            throw ValidationException::withMessages([
                'barcode' => 'El codigo UPC-A no es valido.',
            ]);
        }
    }

    private function hasValidCheckDigit(string $code): bool
    {
        $digits = array_map('intval', str_split($code));
        $checkDigit = array_pop($digits);
        $sum = 0;
        $length = count($digits);

        foreach (array_reverse($digits) as $index => $digit) {
            $sum += $index % 2 === 0 ? $digit * 3 : $digit;
        }

        return (10 - ($sum % 10)) % 10 === $checkDigit;
    }
}
