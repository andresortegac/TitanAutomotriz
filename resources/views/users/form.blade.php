<div class="form-grid">
    <label>Nombre<input name="name" value="{{ old('name', $user->name ?? '') }}" required></label>
    <label>Correo<input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required></label>
    <label>Rol<select name="role" required><option value="admin" @selected(old('role', $user->role ?? '') === 'admin')>Admin</option><option value="vendedor" @selected(old('role', $user->role ?? 'vendedor') === 'vendedor')>Vendedor</option></select></label>
    <label>Contrasena<input type="password" name="password" @if(!isset($user)) required @endif></label>
    <label style="display:flex;gap:8px;align-items:center;font-weight:400;"><input type="checkbox" name="active" value="1" style="width:auto;" @checked(old('active', $user->active ?? true))> Activo</label>
</div><div class="actions" style="margin-top:14px;"><button class="btn">Guardar</button><a class="btn light" href="{{ route('users.index') }}">Cancelar</a></div>
