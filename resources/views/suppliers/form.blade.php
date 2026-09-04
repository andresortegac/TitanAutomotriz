<div class="form-grid">
    <label>Nombre<input name="name" value="{{ old('name', $supplier->name ?? '') }}" required></label>
    <label>NIT<input name="nit" value="{{ old('nit', $supplier->nit ?? '') }}"></label>
    <label>Telefono<input name="phone" value="{{ old('phone', $supplier->phone ?? '') }}"></label>
    <label>Correo<input type="email" name="email" value="{{ old('email', $supplier->email ?? '') }}"></label>
    <label class="span-2">Direccion<input name="address" value="{{ old('address', $supplier->address ?? '') }}"></label>
    <label style="display:flex;gap:8px;align-items:center;font-weight:400;"><input type="checkbox" name="active" value="1" style="width:auto;" @checked(old('active', $supplier->active ?? true))> Activo</label>
</div><div class="actions" style="margin-top:14px;"><button class="btn">Guardar</button><a class="btn light" href="{{ route('suppliers.index') }}">Cancelar</a></div>
