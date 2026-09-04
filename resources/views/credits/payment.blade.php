@extends('layouts.app')
@section('title', 'Registrar abono')
@section('content')
<div class="actions" style="justify-content:space-between;margin-bottom:14px;"><h1>Registrar abono</h1><a class="btn light" href="{{ route('credits.index') }}">Volver</a></div>

<div class="grid grid-2">
    <div class="panel">
        <h2>Credito</h2>
        <p><strong>Factura:</strong> {{ $sale->invoice_number }}</p>
        <p><strong>Cliente:</strong> {{ $sale->customer->name }}</p>
        <p><strong>Total:</strong> ${{ number_format($sale->total, 0) }}</p>
        <p><strong>Abonado:</strong> ${{ number_format($sale->paid_amount, 0) }}</p>
        <p><strong>Saldo:</strong> ${{ number_format($sale->balance, 0) }}</p>
        <p><strong>Vence:</strong> {{ $sale->credit_due_date?->format('d/m/Y') }}</p>
    </div>

    <form class="panel" method="post" action="{{ route('credits.payment.store', $sale) }}">
        @csrf
        <h2>Nuevo abono</h2>
        <div class="form-grid">
            <label>Fecha<input type="date" name="payment_date" value="{{ now()->format('Y-m-d') }}" required></label>
            <label>Valor<input type="number" step="0.01" min="0.01" max="{{ $sale->balance }}" name="amount" value="{{ old('amount', $sale->balance) }}" required></label>
            <label>Metodo<select name="payment_method" required><option value="efectivo">Efectivo</option><option value="transferencia">Transferencia</option><option value="tarjeta">Tarjeta</option><option value="otro">Otro</option></select></label>
            <label>Referencia<input name="reference" value="{{ old('reference') }}" placeholder="Comprobante o recibo"></label>
            <label class="span-2">Notas<textarea name="notes">{{ old('notes') }}</textarea></label>
        </div>
        <div class="actions" style="margin-top:14px;"><button class="btn">Guardar abono</button></div>
    </form>
</div>

<div class="panel" style="margin-top:16px;">
    <h2>Historial de abonos</h2>
    <table>
        <thead><tr><th>Fecha</th><th>Valor</th><th>Metodo</th><th>Referencia</th><th>Usuario</th></tr></thead>
        <tbody>
            @forelse($sale->creditPayments as $payment)
                <tr><td>{{ $payment->payment_date->format('d/m/Y') }}</td><td>${{ number_format($payment->amount, 0) }}</td><td>{{ ucfirst($payment->payment_method) }}</td><td>{{ $payment->reference ?? '-' }}</td><td>{{ $payment->user->name }}</td></tr>
            @empty
                <tr><td colspan="5" class="muted">Aun no hay abonos.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
