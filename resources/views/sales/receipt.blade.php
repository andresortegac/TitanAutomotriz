<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recibo {{ $sale->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #f3f4f6;
            color: #111;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }
        .receipt-actions {
            display: flex;
            gap: 8px;
            justify-content: center;
            padding: 16px;
        }
        .btn {
            border: 0;
            border-radius: 6px;
            background: #e50909;
            color: #fff;
            cursor: pointer;
            font-weight: 700;
            padding: 10px 14px;
            text-decoration: none;
        }
        .btn.light { background: #e5e7eb; color: #111; }
        .receipt {
            width: 80mm;
            min-height: 100vh;
            margin: 0 auto 24px;
            padding: 10px;
            background: #fff;
        }
        .logo {
            display: block;
            width: 48mm;
            max-height: 24mm;
            object-fit: contain;
            margin: 0 auto 6px;
        }
        .center { text-align: center; }
        .muted { color: #444; }
        .title {
            margin: 0;
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .line {
            border-top: 1px dashed #111;
            margin: 8px 0;
        }
        .row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 3px 0;
            text-align: left;
            vertical-align: top;
        }
        th:last-child,
        td:last-child { text-align: right; }
        .product-name {
            display: block;
            max-width: 42mm;
            overflow-wrap: anywhere;
        }
        .totals {
            display: grid;
            gap: 4px;
            font-size: 13px;
        }
        .total {
            font-size: 16px;
            font-weight: 900;
        }
        @page {
            size: 80mm auto;
            margin: 0;
        }
        @media print {
            body { background: #fff; }
            .receipt-actions { display: none; }
            .receipt {
                width: 80mm;
                margin: 0;
                padding: 8px;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-actions">
        <button class="btn" type="button" onclick="window.print()">Imprimir</button>
        <a class="btn light" href="{{ route('sales.show', $sale) }}">Ver factura</a>
        <a class="btn light" href="{{ route('sales.create') }}">Nueva venta</a>
    </div>

    <main class="receipt">
        <img class="logo" src="{{ asset('images/titan-automotriz-logo.jpeg') }}" alt="Titan Automotriz">
        <div class="center">
            <p class="title">Titan Automotriz</p>
            <div class="muted">Repuestos de alta calidad</div>
            <div class="muted">Recibo de venta</div>
        </div>

        <div class="line"></div>

        <div class="row"><span>Factura:</span><strong>{{ $sale->invoice_number }}</strong></div>
        <div class="row"><span>Fecha:</span><span>{{ $sale->created_at->format('d/m/Y H:i') }}</span></div>
        <div class="row"><span>Vendedor:</span><span>{{ $sale->user->name }}</span></div>
        <div class="row"><span>Cliente:</span><span>{{ $sale->customer->name ?? 'Consumidor final' }}</span></div>
        <div class="row"><span>Pago:</span><span>{{ ucfirst($sale->payment_method) }}</span></div>
        @if($sale->payment_method === 'credito')
            <div class="row"><span>Vence:</span><span>{{ $sale->credit_due_date?->format('d/m/Y') }}</span></div>
        @endif

        <div class="line"></div>

        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cant.</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                    <tr>
                        <td>
                            <span class="product-name">{{ $item->product_name }}</span>
                            <small>{{ $item->quantity }} x ${{ number_format($item->unit_price, 0) }}</small>
                            @if($item->tax_rate > 0)
                                <small>IVA {{ number_format($item->tax_rate, 2) }}%: ${{ number_format($item->tax_amount, 0) }}</small>
                            @endif
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ number_format($item->total ?: $item->subtotal, 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="line"></div>

        <div class="totals">
            <div class="row"><span>Subtotal:</span><span>${{ number_format($sale->subtotal, 0) }}</span></div>
            <div class="row"><span>Descuento:</span><span>${{ number_format($sale->discount, 0) }}</span></div>
            <div class="row"><span>IVA:</span><span>${{ number_format($sale->tax, 0) }}</span></div>
            <div class="row total"><span>Total:</span><span>${{ number_format($sale->total, 0) }}</span></div>
            <div class="row"><span>Pagado:</span><span>${{ number_format($sale->paid_amount, 0) }}</span></div>
            @if($sale->payment_method === 'credito')
                <div class="row"><span>Saldo:</span><span>${{ number_format($sale->balance, 0) }}</span></div>
            @else
                <div class="row"><span>Cambio:</span><span>${{ number_format($sale->change_amount, 0) }}</span></div>
            @endif
        </div>

        <div class="line"></div>

        <div class="center">
            <strong>Gracias por su compra</strong>
            <div class="muted">Conserve este recibo.</div>
        </div>
    </main>

    @if(request()->boolean('print'))
        <script>
            window.addEventListener('load', () => setTimeout(() => window.print(), 350));
        </script>
    @endif
</body>
</html>
