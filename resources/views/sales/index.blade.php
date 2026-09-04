@extends('layouts.app')
@section('title', 'Ventas')
@section('content')
<div class="actions" style="justify-content:space-between;margin-bottom:14px;"><h1>Ventas</h1><a class="btn" href="{{ route('sales.create') }}">Nueva venta</a></div>
<table><thead><tr><th>Factura</th><th>Cliente</th><th>Vendedor</th><th>Total</th><th>Pago</th><th>Saldo</th><th>Estado</th><th>Fecha</th><th></th></tr></thead><tbody>
@forelse($sales as $sale)
<tr><td>{{ $sale->invoice_number }}</td><td>{{ $sale->customer->name ?? 'Consumidor final' }}</td><td>{{ $sale->user->name }}</td><td>${{ number_format($sale->total, 0) }}</td><td>{{ ucfirst($sale->payment_method) }}</td><td>${{ number_format($sale->balance, 0) }}</td><td>{{ $sale->status }}</td><td>{{ $sale->created_at->format('d/m/Y H:i') }}</td><td><a class="btn light" href="{{ route('sales.show', $sale) }}">Ver</a></td></tr>
@empty <tr><td colspan="9" class="muted">No hay ventas.</td></tr> @endforelse
</tbody></table><div class="pagination">{{ $sales->links() }}</div>
@endsection
