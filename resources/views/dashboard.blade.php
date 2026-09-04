@extends('layouts.app')

@section('title', 'Panel')

@section('content')
<h1>Panel principal</h1>
<div class="grid grid-4">
    <div class="panel"><div class="muted">Ventas hoy</div><div class="metric">${{ number_format($todaySales, 0) }}</div></div>
    <div class="panel"><div class="muted">Ventas mes</div><div class="metric">${{ number_format($monthSales, 0) }}</div></div>
    <div class="panel"><div class="muted">Productos</div><div class="metric">{{ $productsCount }}</div></div>
    <div class="panel"><div class="muted">Stock bajo</div><div class="metric">{{ $lowStockCount }}</div></div>
</div>

<div class="grid grid-2" style="margin-top:16px;">
    <div class="panel">
        <h2>Ultimas ventas</h2>
        <table>
            <thead><tr><th>Factura</th><th>Total</th><th>Vendedor</th></tr></thead>
            <tbody>
            @forelse($recentSales as $sale)
                <tr>
                    <td><a href="{{ route('sales.show', $sale) }}">{{ $sale->invoice_number }}</a></td>
                    <td>${{ number_format($sale->total, 0) }}</td>
                    <td>{{ $sale->user->name }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">Sin ventas registradas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="panel">
        <h2>Productos mas vendidos</h2>
        <table>
            <thead><tr><th>Producto</th><th>Unidades</th></tr></thead>
            <tbody>
            @forelse($topProducts as $product)
                <tr><td>{{ $product->product_name }}</td><td>{{ $product->sold }}</td></tr>
            @empty
                <tr><td colspan="2" class="muted">Aun no hay datos.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
