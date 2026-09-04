<div class="form-grid">
    <label>Fecha<input type="date" name="expense_date" value="{{ old('expense_date', isset($expense) ? $expense->expense_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required></label>
    <label>Categoria<select name="category" required><option value="">Seleccione</option>@foreach($categories as $category)<option value="{{ $category }}" @selected(old('category', $expense->category ?? '') === $category)>{{ $category }}</option>@endforeach</select></label>
    <label class="span-2">Descripcion<input name="description" value="{{ old('description', $expense->description ?? '') }}" placeholder="Ej: Pago de arriendo local" required></label>
    <label>Valor<input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $expense->amount ?? '') }}" required></label>
    <label>Metodo de pago<select name="payment_method" required>
        @foreach(['efectivo' => 'Efectivo', 'transferencia' => 'Transferencia', 'tarjeta' => 'Tarjeta', 'otro' => 'Otro'] as $value => $label)
            <option value="{{ $value }}" @selected(old('payment_method', $expense->payment_method ?? 'efectivo') === $value)>{{ $label }}</option>
        @endforeach
    </select></label>
    <label class="span-2">Referencia<input name="reference" value="{{ old('reference', $expense->reference ?? '') }}" placeholder="Factura, comprobante, recibo o soporte"></label>
    <label class="span-2">Notas<textarea name="notes" placeholder="Detalle adicional del gasto">{{ old('notes', $expense->notes ?? '') }}</textarea></label>
</div>
<div class="actions" style="margin-top:14px;"><button class="btn">Guardar</button><a class="btn light" href="{{ route('expenses.index') }}">Cancelar</a></div>
