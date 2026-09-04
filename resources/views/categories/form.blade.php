<div class="form-grid">
    <label>Nombre<input name="name" value="{{ old('name', $category->name ?? '') }}" required></label>
    <label style="display:flex;gap:8px;align-items:end;font-weight:400;"><input type="checkbox" name="active" value="1" style="width:auto;" @checked(old('active', $category->active ?? true))> Activa</label>
    <label class="span-2">Descripcion<textarea name="description">{{ old('description', $category->description ?? '') }}</textarea></label>
</div>
<div class="actions" style="margin-top:14px;"><button class="btn">Guardar</button><a class="btn light" href="{{ route('categories.index') }}">Cancelar</a></div>
