<div class="form-grid">
    <label>Nombre<input name="name" value="{{ old('name', $customer->name ?? '') }}" required></label>
    <label>Documento<input name="document" value="{{ old('document', $customer->document ?? '') }}"></label>
    <label>Telefono<input name="phone" value="{{ old('phone', $customer->phone ?? '') }}"></label>
    <label>Correo<input type="email" name="email" value="{{ old('email', $customer->email ?? '') }}"></label>
    <label class="span-2">Direccion<input name="address" value="{{ old('address', $customer->address ?? '') }}"></label>
</div><div class="actions" style="margin-top:14px;"><button class="btn">Guardar</button><a class="btn light" href="{{ route('customers.index') }}">Cancelar</a></div>
