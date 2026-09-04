@extends('layouts.app')
@section('title', 'Productos')
@section('content')
<style>
    .product-photo {
        width: 54px;
        height: 54px;
        border-radius: 8px;
        border: 1px solid var(--line);
        background: #f8fafc;
        object-fit: cover;
        transition: transform .18s ease, box-shadow .18s ease;
        transform-origin: left center;
    }
    .product-photo:hover {
        position: relative;
        z-index: 5;
        transform: scale(2.35);
        box-shadow: 0 14px 34px rgba(0,0,0,.25);
    }
    .product-photo-empty {
        width: 54px;
        height: 54px;
        border-radius: 8px;
        border: 1px dashed var(--line);
        display: grid;
        place-items: center;
        color: var(--muted);
        font-size: 12px;
        background: #f8fafc;
    }
</style>
<div class="actions" style="justify-content:space-between;margin-bottom:14px;"><h1>Productos</h1>@if(auth()->user()->isAdmin())<a class="btn" href="{{ route('products.create') }}">Nuevo producto</a>@endif</div>
<form class="actions" method="get" style="margin-bottom:14px;"><input name="search" placeholder="Buscar por nombre, codigo, SKU o barras" value="{{ request('search') }}" style="max-width:360px;"><button class="btn light">Buscar</button></form>
<table><thead><tr><th>Imagen</th><th>Codigo</th><th>SKU</th><th>Barras</th><th>Producto</th><th>Categoria</th><th>Stock</th><th>Precio</th><th>IVA</th><th></th></tr></thead><tbody>
@forelse($products as $product)
<tr><td>@if($product->image_path)<img class="product-photo" src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}">@else<div class="product-photo-empty">Sin foto</div>@endif</td><td>{{ $product->code }}</td><td>{{ $product->sku ?? 'Pendiente' }}</td><td>{{ $product->primaryBarcode->code ?? 'Pendiente' }}</td><td>{{ $product->name }}</td><td>{{ $product->category->name }}</td><td>@if($product->stock <= $product->min_stock)<span class="badge warn">{{ $product->stock }}</span>@else {{ $product->stock }} @endif</td><td>${{ number_format($product->sale_price, 0) }}</td><td>{{ number_format($product->tax_rate, 2) }}%</td><td class="actions">@if(auth()->user()->isAdmin())<a class="btn light" href="{{ route('products.edit', $product) }}">Editar</a><form method="post" action="{{ route('products.destroy', $product) }}">@csrf @method('delete')<button class="btn danger swal-confirm" data-title="Eliminar producto?" data-text="Solo se eliminara si no tiene ventas registradas." data-confirm="Si, eliminar">Eliminar</button></form>@else <span class="muted">Solo consulta</span> @endif</td></tr>
@empty <tr><td colspan="10" class="muted">No hay productos.</td></tr> @endforelse
</tbody></table><div class="pagination">{{ $products->links() }}</div>
@endsection
