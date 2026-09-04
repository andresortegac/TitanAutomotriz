<div class="form-grid">
    <label>Codigo<input name="code" value="{{ old('code', $service->code ?? '') }}" required></label>
    <label>Nombre<input name="name" value="{{ old('name', $service->name ?? '') }}" required></label>
    <label>Precio venta<input type="number" step="0.01" min="0" name="sale_price" value="{{ old('sale_price', $service->sale_price ?? 0) }}" required></label>
    <label>IVA (%)<input type="number" step="0.01" min="0" max="100" name="tax_rate" value="{{ old('tax_rate', $service->tax_rate ?? 0) }}" required></label>
    <label class="span-2">Descripcion<textarea name="description">{{ old('description', $service->description ?? '') }}</textarea></label>
    <label style="display:flex;gap:8px;align-items:center;font-weight:400;"><input type="checkbox" name="active" value="1" style="width:auto;" @checked(old('active', $service->active ?? true))> Activo</label>
</div>
<div class="actions" style="margin-top:14px;"><button class="btn">Guardar</button><a class="btn light" href="{{ route('services.index') }}">Cancelar</a></div>
