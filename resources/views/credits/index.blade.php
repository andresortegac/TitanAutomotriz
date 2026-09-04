@extends('layouts.app')
@section('title', 'Creditos')
@section('content')
<div class="actions" style="justify-content:space-between;margin-bottom:14px;"><h1>Creditos</h1><a class="btn" href="{{ route('sales.create') }}">Nueva venta</a></div>

<div class="grid grid-4" style="margin-bottom:16px;">
    <div class="panel"><div class="muted">Total credito</div><div class="metric">${{ number_format($summary->total_credit, 0) }}</div></div>
    <div class="panel"><div class="muted">Abonado</div><div class="metric">${{ number_format($summary->total_paid, 0) }}</div></div>
    <div class="panel"><div class="muted">Saldo</div><div class="metric">${{ number_format($summary->total_balance, 0) }}</div></div>
    <div class="panel"><div class="muted">Creditos</div><div class="metric">{{ $credits->total() }}</div></div>
</div>

<form class="panel" method="get" style="margin-bottom:14px;">
    <div class="form-grid">
        <label>Buscar<input name="search" placeholder="Factura, cliente o documento" value="{{ request('search') }}"></label>
        <label>Estado<select name="status"><option value="">Todos</option>@foreach(['pendiente' => 'Pendiente', 'abonado' => 'Abonado', 'pagado' => 'Pagado', 'vencido' => 'Vencido'] as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></label>
    </div>
    <div class="actions" style="margin-top:14px;"><button class="btn">Filtrar</button><a class="btn light" href="{{ route('credits.index') }}">Limpiar</a></div>
</form>

<table>
    <thead>
        <tr>
            <th>Factura</th>
            <th>Cliente</th>
            <th>Total</th>
            <th>Abonado</th>
            <th>Saldo</th>
            <th>Vence</th>
            <th>Estado</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse($credits as $sale)
            <tr>
                <td>{{ $sale->invoice_number }}</td>
                <td>{{ $sale->customer->name ?? 'Sin cliente' }}</td>
                <td>${{ number_format($sale->total, 0) }}</td>
                <td>${{ number_format($sale->paid_amount, 0) }}</td>
                <td>${{ number_format($sale->balance, 0) }}</td>
                <td>{{ $sale->credit_due_date?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ ucfirst($sale->credit_status) }}</td>
                <td class="actions">
                    @if($sale->balance > 0)
                        <a class="btn" href="{{ route('credits.payment', $sale) }}">Abonar</a>
                    @endif
                    <a class="btn light" href="{{ route('sales.show', $sale) }}">Factura</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="muted">No hay creditos registrados.</td></tr>
        @endforelse
    </tbody>
</table>
<div class="pagination">{{ $credits->links() }}</div>
@endsection
