@extends('layouts.app')
@section('title', 'Nueva venta')
@section('content')
<h1>Nueva venta</h1>
<form class="panel" method="post" action="{{ route('sales.store') }}" id="saleForm">
    @csrf
    <div class="form-grid">
        <label>Tipo de factura<select name="invoice_type" id="invoiceType" required onchange="toggleInvoiceType()"><option value="normal" @selected(old('invoice_type') === 'normal')>Factura normal</option><option value="electronica" @selected(old('invoice_type') === 'electronica')>Factura electrónica</option></select></label>
        <label>Cliente<select name="customer_id" id="customerId"><option value="">Consumidor final</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->name }} {{ $customer->document ? '- '.$customer->document : '' }}</option>@endforeach</select></label>
        <label>Metodo de pago<select name="payment_method" id="paymentMethod" required onchange="toggleCreditFields();calculate()"><option value="efectivo">Efectivo</option><option value="transferencia">Transferencia</option><option value="tarjeta">Tarjeta</option><option value="mixto">Mixto</option><option value="credito">Credito</option></select></label>
        <label id="creditDueField" style="display:none;">Fecha vencimiento credito<input type="date" name="credit_due_date" id="creditDueDate" value="{{ now()->addDays(30)->format('Y-m-d') }}"></label>
    </div>
    <p class="muted" id="electronicNotice" style="display:none;margin-top:10px;">La factura electrónica se enviará a Factus. El cliente es obligatorio y debe tener sus datos fiscales completos.</p>
    <div class="panel" style="margin-top:18px;background:#111;color:#fff;border-color:#ef1d25;">
        <label>Escanear o escribir codigo
            <input id="barcodeInput" autocomplete="off" placeholder="Escanea el codigo y presiona Enter" style="margin-top:8px;">
        </label>
        <div id="scanMessage" style="margin-top:10px;font-weight:700;"></div>
    </div>
    <h2 style="margin-top:18px;">Productos y servicios</h2>
    <div id="items" class="grid"></div>
    <div class="actions" style="margin-top:10px;">
        <button class="btn light" type="button" onclick="addItem()">Agregar producto</button>
        <button class="btn secondary" type="button" onclick="addServiceItem()">Agregar servicio</button>
    </div>
    <div class="form-grid" style="margin-top:18px;">
        <label>Descuento<input type="number" step="0.01" min="0" name="discount" id="discount" value="0" oninput="calculate()"></label>
        <label>Valor pagado<input type="number" step="0.01" min="0" name="paid_amount" id="paid" value="0" oninput="calculate()" required></label>
    </div>
    <div class="grid grid-4" style="margin-top:16px;">
        <div class="panel"><div class="muted">Subtotal</div><div class="metric" id="subtotal">$0</div></div>
        <div class="panel"><div class="muted">IVA</div><div class="metric" id="tax">$0</div></div>
        <div class="panel"><div class="muted">Total</div><div class="metric" id="total">$0</div></div>
        <div class="panel"><div class="muted" id="changeLabel">Cambio</div><div class="metric" id="change">$0</div></div>
    </div>
    <div class="actions" style="margin-top:16px;"><button class="btn">Registrar venta</button><a class="btn light" href="{{ route('sales.index') }}">Cancelar</a></div>
</form>
<script>
let products = @json($productOptions);
let services = @json($serviceOptions);
let index = 0;
function money(value) { return '$' + Math.round(value).toLocaleString('es-CO'); }
function productLabel(product) {
    const reference = product.barcode || product.sku || product.code;
    return `${reference} - ${product.name} ($${Math.round(product.price).toLocaleString('es-CO')}, IVA ${Number(product.tax_rate || 0).toLocaleString('es-CO')}%, stock ${product.stock})`;
}
function serviceLabel(service) {
    return `${service.code} - ${service.name} ($${Math.round(service.price).toLocaleString('es-CO')}, IVA ${Number(service.tax_rate || 0).toLocaleString('es-CO')}%)`;
}
function addItem(productId = '') {
    const row = document.createElement('div');
    row.className = 'form-grid';
    row.innerHTML = `<input type="hidden" name="items[${index}][item_type]" value="product"><label>Producto<select name="items[${index}][product_id]" onchange="calculate()" required><option value="">Seleccione</option>${products.map(p => `<option value="${p.id}" data-price="${p.price}" data-tax-rate="${p.tax_rate || 0}" data-stock="${p.stock}" ${String(p.id) === String(productId) ? 'selected' : ''}>${productLabel(p)}</option>`).join('')}</select></label><label>Cantidad<input type="number" min="1" name="items[${index}][quantity]" value="1" oninput="calculate()" required></label><button class="btn danger" type="button" onclick="this.parentElement.remove();calculate()">Quitar</button>`;
    document.getElementById('items').appendChild(row);
    index++;
    calculate();
    return row;
}
function addServiceItem(serviceId = '') {
    const row = document.createElement('div');
    row.className = 'form-grid';
    row.innerHTML = `<input type="hidden" name="items[${index}][item_type]" value="service"><label>Servicio<select name="items[${index}][service_id]" onchange="calculate()" required><option value="">Seleccione</option>${services.map(s => `<option value="${s.id}" data-price="${s.price}" data-tax-rate="${s.tax_rate || 0}" ${String(s.id) === String(serviceId) ? 'selected' : ''}>${serviceLabel(s)}</option>`).join('')}</select></label><label>Cantidad<input type="number" min="1" name="items[${index}][quantity]" value="1" oninput="calculate()" required></label><button class="btn danger" type="button" onclick="this.parentElement.remove();calculate()">Quitar</button>`;
    document.getElementById('items').appendChild(row);
    index++;
    calculate();
    return row;
}
function calculate() {
    let subtotal = 0;
    let tax = 0;
    document.querySelectorAll('#items .form-grid').forEach(row => {
        const select = row.querySelector('select');
        const quantity = parseInt(row.querySelector('input[type="number"]').value || '0', 10);
        const price = parseFloat(select.selectedOptions[0]?.dataset.price || '0');
        const taxRate = parseFloat(select.selectedOptions[0]?.dataset.taxRate || '0');
        subtotal += price * quantity;
        tax += (price * quantity) * (taxRate / 100);
    });
    const discount = parseFloat(document.getElementById('discount').value || '0');
    const total = Math.max(subtotal - discount, 0) + tax;
    const paid = parseFloat(document.getElementById('paid').value || '0');
    document.getElementById('subtotal').textContent = money(subtotal);
    document.getElementById('tax').textContent = money(tax);
    document.getElementById('total').textContent = money(total);
    if (document.getElementById('paymentMethod').value === 'credito') {
        document.getElementById('changeLabel').textContent = 'Saldo';
        document.getElementById('change').textContent = money(Math.max(total - paid, 0));
    } else {
        document.getElementById('changeLabel').textContent = 'Cambio';
        document.getElementById('change').textContent = money(Math.max(paid - total, 0));
    }
}
function toggleCreditFields() {
    const isCredit = document.getElementById('paymentMethod').value === 'credito';
    document.getElementById('creditDueField').style.display = isCredit ? 'grid' : 'none';
    document.getElementById('creditDueDate').required = isCredit;
}
function toggleInvoiceType() {
    const electronic = document.getElementById('invoiceType').value === 'electronica';
    document.getElementById('customerId').required = electronic;
    document.getElementById('customerId').querySelector('option[value=""]').disabled = electronic;
    document.getElementById('electronicNotice').style.display = electronic ? 'block' : 'none';
}
function setScanMessage(message, type = 'ok') {
    const scanMessage = document.getElementById('scanMessage');
    scanMessage.textContent = message;
    scanMessage.style.color = type === 'error' ? '#fecaca' : '#bbf7d0';
}
function findProductRow(productId) {
    return Array.from(document.querySelectorAll('#items .form-grid')).find(row => {
        if (row.querySelector('input[type="hidden"]').value !== 'product') {
            return false;
        }

        return String(row.querySelector('select').value) === String(productId);
    });
}
function quantityInCart(productId) {
    return Array.from(document.querySelectorAll('#items .form-grid')).reduce((total, row) => {
        if (row.querySelector('input[type="hidden"]').value !== 'product') {
            return total;
        }

        if (String(row.querySelector('select').value) !== String(productId)) {
            return total;
        }

        return total + parseInt(row.querySelector('input[type="number"]').value || '0', 10);
    }, 0);
}
function upsertProductOption(product) {
    if (! products.some(item => String(item.id) === String(product.id))) {
        products.push(product);
    }
}
async function scanProduct(code) {
    if (! code) {
        return;
    }

    try {
        const response = await fetch(`{{ route('products.lookup') }}?code=${encodeURIComponent(code)}`, {
            headers: { 'Accept': 'application/json' }
        });
        const data = await response.json();

        if (! response.ok || ! data.success) {
            setScanMessage(data.message || 'Producto no encontrado.', 'error');
            return;
        }

        const product = data.product;
        upsertProductOption(product);

        if (quantityInCart(product.id) >= product.stock) {
            setScanMessage('No hay suficiente inventario.', 'error');
            return;
        }

        const existingRow = findProductRow(product.id);

        if (existingRow) {
            const quantityInput = existingRow.querySelector('input[type="number"]');
            quantityInput.value = parseInt(quantityInput.value || '0', 10) + 1;
        } else {
            addItem(product.id);
        }

        calculate();
        setScanMessage(`${product.name} agregado. Stock disponible: ${product.stock}.`);
    } catch (error) {
        setScanMessage('No se pudo buscar el producto.', 'error');
    }
}
document.getElementById('barcodeInput').addEventListener('keydown', function (event) {
    if (event.key !== 'Enter') {
        return;
    }

    event.preventDefault();
    const code = this.value.trim();
    this.value = '';
    scanProduct(code);
});
document.getElementById('barcodeInput').focus();
addItem();
toggleCreditFields();
toggleInvoiceType();
</script>
@endsection
