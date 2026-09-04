@extends('layouts.app')
@section('title', 'Usuarios')
@section('content')
<div class="actions" style="justify-content:space-between;margin-bottom:14px;"><h1>Usuarios</h1><a class="btn" href="{{ route('users.create') }}">Nuevo usuario</a></div>
<table><thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th></th></tr></thead><tbody>
@forelse($users as $user)
<tr><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ $user->role }}</td><td>{{ $user->active ? 'Activo' : 'Inactivo' }}</td><td class="actions"><a class="btn light" href="{{ route('users.edit', $user) }}">Editar</a><form method="post" action="{{ route('users.destroy', $user) }}">@csrf @method('delete')<button class="btn danger swal-confirm" data-title="Eliminar usuario?" data-text="Si tiene ventas, se desactivara en lugar de eliminarse." data-confirm="Si, continuar">Eliminar</button></form></td></tr>
@empty <tr><td colspan="5" class="muted">No hay usuarios.</td></tr> @endforelse
</tbody></table><div class="pagination">{{ $users->links() }}</div>
@endsection
