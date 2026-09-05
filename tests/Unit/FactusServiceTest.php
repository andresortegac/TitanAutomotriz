<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\FactusService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class FactusServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('factus.access_token');
        config()->set('services.factus', [
            'base_url' => 'https://api-sandbox.factus.test',
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'username' => 'user',
            'password' => 'password',
            'numbering_range_id' => null,
        ]);
    }

    public function test_it_normalizes_responsibilities_and_omits_zero_discount(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response(['access_token' => 'token']),
            '*/v2/bills/validate' => Http::response(['data' => ['number' => 'SETP1']], 201),
        ]);

        app(FactusService::class)->issue($this->sale('R-99-PN', 0));

        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/v2/bills/validate')
            && $request['customer']['responsibilities'] === ['R-99-PN']
            && ! array_key_exists('discount_amount', $request['items'][0]));
    }

    public function test_it_sends_a_positive_discount_amount(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response(['access_token' => 'token']),
            '*/v2/bills/validate' => Http::response(['data' => ['number' => 'SETP1']], 201),
        ]);

        app(FactusService::class)->issue($this->sale(['R-99-PN'], 10));

        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/v2/bills/validate')
            && $request['items'][0]['discount_amount'] === '10.00');
    }

    public function test_it_includes_nested_validation_errors_in_the_exception_message(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response(['access_token' => 'token']),
            '*/v2/bills/validate' => Http::response([
                'status' => 'Validation error',
                'message' => 'Error de validación',
                'data' => [
                    'message' => 'Error de validación',
                    'errors' => ['customer.responsibilities' => ['El campo responsabilidades fiscales debe ser un array.']],
                ],
            ], 422),
        ]);

        try {
            app(FactusService::class)->issue($this->sale(['R-99-PN'], 0));
            $this->fail('Se esperaba una excepción de validación de Factus.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('responsabilidades fiscales debe ser un array', $exception->getMessage());
        }
    }

    private function sale(array|string $responsibilities, float $discount): Sale
    {
        $customer = new Customer([
            'name' => 'Cliente de prueba',
            'document' => '123456789',
            'identification_document_code' => '13',
            'legal_organization_code' => '2',
            'tribute_code' => 'ZZ',
            'responsibilities' => $responsibilities,
            'country_code' => 'CO',
            'address' => 'Calle 1',
            'email' => 'cliente@example.test',
            'phone' => '3000000000',
            'municipality_code' => '05001',
        ]);

        $item = new SaleItem([
            'product_name' => 'Producto de prueba',
            'unit_price' => 100,
            'tax_rate' => 19,
            'quantity' => 1,
            'subtotal' => 100,
        ]);
        $item->id = 1;

        $sale = new Sale([
            'invoice_number' => 'SETP-1',
            'subtotal' => 100,
            'discount' => $discount,
            'total' => 100 - $discount,
            'payment_method' => 'efectivo',
        ]);
        $sale->setRelation('customer', $customer);
        $sale->setRelation('items', collect([$item]));

        return $sale;
    }
}
