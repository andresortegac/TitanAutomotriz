<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

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

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'document' => ['nullable', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
