<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CustomerController extends Controller
{
    public function index()
    {
        return view('customers.index', ['customers' => Customer::latest()->paginate(10)]);
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        Customer::create($this->validated($request));

        return redirect()->route('customers.index')->with('success', 'Cliente creado.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validated($request));

        return redirect()->route('customers.index')->with('success', 'Cliente actualizado.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->sales()->exists()) {
            return back()->withErrors('No se puede eliminar un cliente con ventas.');
        }

        $customer->delete();

        return back()->with('success', 'Cliente eliminado.');
    }

    public function municipalities(Request $request)
    {
        $term = trim((string) $request->query('search'));

        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $municipalities = Cache::remember('dane.municipalities.'.md5(mb_strtolower($term)), now()->addDays(7), function () use ($term) {
            $where = "UPPER(MPIO_CNMBRE) LIKE '%".str_replace("'", "''", mb_strtoupper($term))."%'";
            $response = Http::timeout(8)->get('https://geoportal.dane.gov.co/mparcgis/rest/services/MMRA2025/Serv_CapasMMRA_2025/MapServer/317/query', [
                'where' => $where,
                'outFields' => 'MPIO_CDPMP,MPIO_CNMBRE,DPTO_CNMBRE',
                'returnGeometry' => 'false',
                'resultRecordCount' => 30,
                'f' => 'json',
            ]);

            if ($response->failed()) {
                return [];
            }

            return collect($response->json('features', []))->map(function ($feature) {
                $attributes = $feature['attributes'] ?? [];

                return [
                    'code' => (string) ($attributes['MPIO_CDPMP'] ?? ''),
                    'name' => $attributes['MPIO_CNMBRE'] ?? '',
                    'department' => $attributes['DPTO_CNMBRE'] ?? '',
                ];
            })->filter(fn ($municipality) => $municipality['code'] && $municipality['name'])->values()->all();
        });

        return response()->json($municipalities);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'document' => ['nullable', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'identification_document_code' => ['required', 'string', 'max:5'],
            'dv' => ['nullable', 'string', 'max:2'],
            'legal_organization_code' => ['required', 'in:1,2'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'tribute_code' => ['required', 'string', 'max:20'],
            'responsibilities' => ['nullable', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'size:2'],
            'municipality_code' => ['nullable', 'string', 'max:10'],
        ]);

        $data['responsibilities'] = array_values(array_filter(array_map(
            'trim', explode(',', $data['responsibilities'] ?? 'R-99-PN')
        )));

        return $data;
    }
}
