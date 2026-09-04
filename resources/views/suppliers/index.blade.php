@extends('layouts.app')
@section('title', 'Proveedores')
@section('content')
<div class="actions" style="justify-content:space-between;margin-bottom:14px;"><h1>Proveedores</h1><a class="btn" href="{{ route('suppliers.create') }}">Nuevo proveedor</a></div>
<table><thead><tr><th>Nombre</th><th>NIT</th><th>Telefono</th><th>Estado</th><th></th></tr></thead><tbody>
@forelse($suppliers as $supplier)
<tr><td>{{ $supplier->name }}</td><td>{{ $supplier->nit }}</td><td>{{ $supplier->phone }}</td><td>{{ $supplier->active ? 'Activo' : 'Inactivo' }}</td><td class="actions"><a class="btn light" href="{{ route('suppliers.edit', $supplier) }}">Editar</a><form method="post" action="{{ route('suppliers.destroy', $supplier) }}">@csrf @method('delete')<button class="btn danger swal-confirm" data-title="Eliminar proveedor?" data-text="Solo se eliminara si no tiene productos registrados." data-confirm="Si, eliminar">Eliminar</button></form></td></tr>
@empty <tr><td colspan="5" class="muted">No hay proveedores.</td></tr> @endforelse
</tbody></table><div class="pagination">{{ $suppliers->links() }}</div>
@endsection
