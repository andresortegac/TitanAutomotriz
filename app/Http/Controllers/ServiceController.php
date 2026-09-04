<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $services = Service::when($request->search, function ($query, $search): void {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('services.index', compact('services'));
    }

    public function create()
    {
        return view('services.create');
    }

    public function store(Request $request)
    {
        Service::create($this->validated($request));

        return redirect()->route('services.index')->with('success', 'Servicio creado.');
    }

    public function edit(Service $service)
    {
        return view('services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $service->update($this->validated($request, $service));

        return redirect()->route('services.index')->with('success', 'Servicio actualizado.');
    }

    public function destroy(Service $service)
    {
        if ($service->saleItems()->exists()) {
            return back()->withErrors('No se puede eliminar un servicio vendido.');
        }

        $service->delete();

        return back()->with('success', 'Servicio eliminado.');
    }

    private function validated(Request $request, ?Service $service = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:80', 'unique:services,code,'.($service?->id ?? 'NULL')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'active' => ['nullable', 'boolean'],
        ]) + ['active' => false];
    }
}
