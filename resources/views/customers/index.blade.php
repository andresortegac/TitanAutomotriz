@extends('layouts.app')
@section('title', 'Clientes')
@section('content')
<div class="actions" style="justify-content:space-between;margin-bottom:14px;"><h1>Clientes</h1><a class="btn" href="{{ route('customers.create') }}">Nuevo cliente</a></div>
<table><thead><tr><th>Nombre</th><th>Documento</th><th>Telefono</th><th>Correo</th><th></th></tr></thead><tbody>
@forelse($customers as $customer)
<tr><td>{{ $customer->name }}</td><td>{{ $customer->document }}</td><td>{{ $customer->phone }}</td><td>{{ $customer->email }}</td><td class="actions"><a class="btn light" href="{{ route('customers.edit', $customer) }}">Editar</a>@if(auth()->user()->isAdmin())<form method="post" action="{{ route('customers.destroy', $customer) }}">@csrf @method('delete')<button class="btn danger swal-confirm" data-title="Eliminar cliente?" data-text="Solo se eliminara si no tiene ventas registradas." data-confirm="Si, eliminar">Eliminar</button></form>@endif</td></tr>
@empty <tr><td colspan="5" class="muted">No hay clientes.</td></tr> @endforelse
</tbody></table><div class="pagination">{{ $customers->links() }}</div>
@endsection
