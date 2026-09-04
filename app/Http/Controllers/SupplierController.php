<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        return view('suppliers.index', ['suppliers' => Supplier::latest()->paginate(10)]);
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        Supplier::create($this->validated($request));

        return redirect()->route('suppliers.index')->with('success', 'Proveedor creado.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $supplier->update($this->validated($request));

        return redirect()->route('suppliers.index')->with('success', 'Proveedor actualizado.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->products()->exists()) {
            return back()->withErrors('No se puede eliminar un proveedor con productos.');
        }

        $supplier->delete();

        return back()->with('success', 'Proveedor eliminado.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nit' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
        ]) + ['active' => false];
    }
}
