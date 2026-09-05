<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FactusService
{
    public function issue(Sale $sale): array
    {
        $token = $this->accessToken();
        try {
            $response = Http::acceptJson()
                ->withToken($token)
                ->timeout(30)
                ->post($this->baseUrl().'/v2/bills/validate', $this->payload($sale));
        } catch (ConnectionException $exception) {
            throw new RuntimeException('No fue posible conectar con Factus. Intenta nuevamente.');
        }

        if ($response->failed()) {
            Log::warning('Factus rechazó una factura electrónica.', [
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'http_status' => $response->status(),
                'response' => $response->json() ?: $response->body(),
            ]);

            throw new RuntimeException($this->errorMessage($response));
        }

        return $response->json();
    }

    private function accessToken(): string
    {
        $this->assertConfigured();

        return Cache::remember('factus.access_token', now()->addMinutes(50), function () {
            try {
                $response = Http::acceptJson()->asForm()->timeout(20)->post($this->baseUrl().'/oauth/token', [
                    'grant_type' => 'password',
                    'client_id' => config('services.factus.client_id'),
                    'client_secret' => config('services.factus.client_secret'),
                    'username' => config('services.factus.username'),
                    'password' => config('services.factus.password'),
                ]);
            } catch (ConnectionException $exception) {
                throw new RuntimeException('No fue posible conectar con Factus. Intenta nuevamente.');
            }

            if ($response->failed() || ! $response->json('access_token')) {
                throw new RuntimeException('No fue posible autenticar la cuenta de Factus. Revisa la configuración.');
            }

            return $response->json('access_token');
        });
    }

    private function errorMessage(Response $response): string
    {
        $payload = $response->json() ?: [];
        $message = data_get($payload, 'data.message')
            ?: data_get($payload, 'message')
            ?: 'Factus no pudo validar la factura electrónica.';
        $errors = data_get($payload, 'data.errors') ?? data_get($payload, 'errors', []);
        $details = collect(Arr::flatten($errors))
            ->filter(fn ($error) => is_scalar($error))
            ->map(fn ($error) => (string) $error)
            ->filter()
            ->unique()
            ->take(3)
            ->implode(' ');

        if ($details && ! str_contains(mb_strtolower($message), mb_strtolower($details))) {
            return $message.': '.$details;
        }

        return $message;
    }

    private function payload(Sale $sale): array
    {
        $customer = $sale->customer;
        if (! $customer) {
            throw new RuntimeException('La factura electrónica requiere un cliente.');
        }

        $customerData = [
            'identification_document_code' => $customer->identification_document_code,
            'identification' => preg_replace('/[^0-9A-Za-z]/', '', $customer->document),
            'legal_organization_code' => $customer->legal_organization_code,
            'tribute_code' => $customer->tribute_code,
            'responsibilities' => $this->responsibilities($customer->responsibilities),
            'country_code' => $customer->country_code ?: 'CO',
            'address' => $customer->address,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'municipality_code' => $customer->municipality_code,
        ];

        if ($customer->identification_document_code === '31' && $customer->dv) {
            $customerData['dv'] = $customer->dv;
        }
        if ($customer->legal_organization_code === '1') {
            $customerData['company'] = $customer->name;
            $customerData['trade_name'] = $customer->trade_name;
        } else {
            $customerData['names'] = $customer->name;
        }

        $items = $sale->items->values();
        $remainingDiscount = (float) $sale->discount;
        $payloadItems = $items->map(function ($item, $index) use ($items, $sale, &$remainingDiscount) {
            $discount = $index === $items->count() - 1
                ? $remainingDiscount
                : round(((float) $sale->discount * ((float) $item->subtotal / max((float) $sale->subtotal, 1))), 2);
            $remainingDiscount -= $discount;
            $reference = $item->product?->sku ?: $item->product?->code ?: $item->service?->code ?: 'ITEM-'.$item->id;

            $payloadItem = [
                'code_reference' => $reference,
                'name' => $item->product_name,
                'quantity' => number_format((float) $item->quantity, 2, '.', ''),
                'price' => number_format((float) $item->unit_price, 2, '.', ''),
                'unit_measure_code' => '94',
                'standard_code' => '999',
                'taxes' => [[
                    'code' => '01',
                    'rate' => number_format((float) $item->tax_rate, 2, '.', ''),
                    'is_excluded' => (float) $item->tax_rate === 0.0,
                ]],
            ];

            // Factus validates this field with a minimum of 0.01; omit it without a discount.
            if (round($discount, 2) >= 0.01) {
                $payloadItem['discount_amount'] = number_format($discount, 2, '.', '');
            }

            return $payloadItem;
        })->all();

        $payload = [
            'reference_code' => $sale->invoice_number,
            'document' => '01',
            'operation_type' => '10',
            'send_email' => filled($customer->email),
            'payment_details' => [[
                'payment_form' => $sale->payment_method === 'credito' ? '2' : '1',
                'payment_method_code' => $this->paymentMethodCode($sale->payment_method),
                'reference_code' => $sale->invoice_number,
                'amount' => number_format((float) $sale->total, 2, '.', ''),
                ...($sale->payment_method === 'credito' ? ['due_date' => $sale->credit_due_date?->format('Y-m-d')] : []),
            ]],
            'customer' => array_filter($customerData, fn ($value) => $value !== null && $value !== ''),
            'items' => $payloadItems,
        ];

        if ($rangeId = config('services.factus.numbering_range_id')) {
            $payload['numbering_range_id'] = (int) $rangeId;
        }

        return $payload;
    }

    private function responsibilities(mixed $responsibilities): array
    {
        if (is_string($responsibilities)) {
            $decoded = json_decode($responsibilities, true);
            $responsibilities = is_array($decoded)
                ? $decoded
                : explode(',', $responsibilities);
        }

        if (! is_array($responsibilities)) {
            return ['R-99-PN'];
        }

        $responsibilities = array_values(array_unique(array_filter(array_map(
            fn ($responsibility) => is_scalar($responsibility) ? trim((string) $responsibility) : '',
            $responsibilities
        ))));

        return $responsibilities ?: ['R-99-PN'];
    }

    private function paymentMethodCode(string $method): string
    {
        return match ($method) {
            'transferencia' => '42',
            'tarjeta' => '48',
            default => '10',
        };
    }

    private function assertConfigured(): void
    {
        foreach (['client_id', 'client_secret', 'username', 'password'] as $key) {
            if (! config("services.factus.{$key}")) {
                throw new RuntimeException('Factus no está configurado en este servidor.');
            }
        }
    }

    private function baseUrl(): string
    {
        return rtrim(config('services.factus.base_url'), '/');
    }
}
