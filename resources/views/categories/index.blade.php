@extends('layouts.app')
@section('title', 'Categorias')
@section('content')
<div class="actions" style="justify-content:space-between;margin-bottom:14px;"><h1>Categorias</h1><a class="btn" href="{{ route('categories.create') }}">Nueva categoria</a></div>
<table><thead><tr><th>Nombre</th><th>Productos</th><th>Estado</th><th></th></tr></thead><tbody>
@forelse($categories as $category)
<tr><td>{{ $category->name }}</td><td>{{ $category->products_count }}</td><td>{{ $category->active ? 'Activa' : 'Inactiva' }}</td><td class="actions"><a class="btn light" href="{{ route('categories.edit', $category) }}">Editar</a><form method="post" action="{{ route('categories.destroy', $category) }}">@csrf @method('delete')<button class="btn danger swal-confirm" data-title="Eliminar categoria?" data-text="Se eliminara esta categoria si no tiene productos." data-confirm="Si, eliminar">Eliminar</button></form></td></tr>
@empty <tr><td colspan="4" class="muted">No hay categorias.</td></tr> @endforelse
</tbody></table><div class="pagination">{{ $categories->links() }}</div>
@endsection
