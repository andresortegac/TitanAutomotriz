@extends('layouts.app')
@section('title', 'Factura')
@section('content')
<div class="actions" style="justify-content:space-between;margin-bottom:14px;"><h1>Factura {{ $sale->invoice_number }}</h1><div class="actions"><a class="btn" href="{{ route('sales.receipt', ['sale' => $sale, 'print' => 1]) }}" target="_blank">Imprimir factura</a><a class="btn light" href="{{ route('sales.index') }}">Volver</a></div></div>
<div class="panel">
    <p><strong>Cliente:</strong> {{ $sale->customer->name ?? 'Consumidor final' }}</p>
    <p><strong>Vendedor:</strong> {{ $sale->user->name }} | <strong>Fecha:</strong> {{ $sale->created_at->format('d/m/Y H:i') }} | <strong>Estado:</strong> {{ $sale->status }}</p>
    <p><strong>Tipo:</strong> {{ $sale->invoice_type === 'electronica' ? 'Factura electrónica' : 'Factura normal' }}</p>
    @if($sale->invoice_type === 'electronica')
        <p><strong>Factura electrónica:</strong> {{ $sale->electronic_number ?: 'En proceso' }} | <strong>Estado DIAN:</strong> {{ $sale->electronic_status ?: 'Pendiente' }}</p>
        @if($sale->electronic_cufe)<p><strong>CUFE:</strong> <span style="overflow-wrap:anywhere;">{{ $sale->electronic_cufe }}</span></p>@endif
        @if($sale->electronic_qr_url)<p><a class="btn" href="{{ $sale->electronic_qr_url }}" target="_blank" rel="noopener">Consultar factura electrónica</a></p>@endif
    @endif
    @if($sale->payment_method === 'credito')
        <p><strong>Credito:</strong> {{ ucfirst($sale->credit_status) }} | <strong>Vence:</strong> {{ $sale->credit_due_date?->format('d/m/Y') }} | <strong>Saldo:</strong> ${{ number_format($sale->balance, 0) }}</p>
    @endif
    <table><thead><tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>IVA</th><th>Subtotal</th><th>Total</th></tr></thead><tbody>
    @foreach($sale->items as $item)<tr><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td><td>${{ number_format($item->unit_price, 0) }}</td><td>{{ number_format($item->tax_rate, 2) }}% (${{ number_format($item->tax_amount, 0) }})</td><td>${{ number_format($item->subtotal, 0) }}</td><td>${{ number_format($item->total ?: $item->subtotal, 0) }}</td></tr>@endforeach
    </tbody></table>
    <div style="max-width:360px;margin-left:auto;margin-top:16px;">
        <p><strong>Subtotal:</strong> ${{ number_format($sale->subtotal, 0) }}</p>
        <p><strong>Descuento:</strong> ${{ number_format($sale->discount, 0) }}</p>
        <p><strong>IVA:</strong> ${{ number_format($sale->tax, 0) }}</p>
        <p><strong>Total:</strong> ${{ number_format($sale->total, 0) }}</p>
        <p><strong>Pagado:</strong> ${{ number_format($sale->paid_amount, 0) }}</p>
        @if($sale->payment_method === 'credito')
            <p><strong>Saldo:</strong> ${{ number_format($sale->balance, 0) }}</p>
        @else
            <p><strong>Cambio:</strong> ${{ number_format($sale->change_amount, 0) }}</p>
        @endif
    </div>
    @if($sale->status !== 'anulada' && auth()->user()->isAdmin())
    <form method="post" action="{{ route('sales.destroy', $sale) }}">@csrf @method('delete')<button class="btn danger swal-confirm" data-title="Anular esta venta?" data-text="El stock de los productos se restaurara." data-confirm="Si, anular">Anular venta</button></form>
    @endif
</div>
@endsection
