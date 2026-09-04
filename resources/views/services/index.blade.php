@extends('layouts.app')
@section('title', 'Servicios')
@section('content')
<div class="actions" style="justify-content:space-between;margin-bottom:14px;"><h1>Servicios</h1>@if(auth()->user()->isAdmin())<a class="btn" href="{{ route('services.create') }}">Nuevo servicio</a>@endif</div>
<form class="actions" method="get" style="margin-bottom:14px;"><input name="search" placeholder="Buscar por nombre o codigo" value="{{ request('search') }}" style="max-width:360px;"><button class="btn light">Buscar</button></form>
<table>
    <thead><tr><th>Codigo</th><th>Servicio</th><th>Precio</th><th>IVA</th><th>Estado</th><th></th></tr></thead>
    <tbody>
        @forelse($services as $service)
            <tr>
                <td>{{ $service->code }}</td>
                <td>{{ $service->name }}</td>
                <td>${{ number_format($service->sale_price, 0) }}</td>
                <td>{{ number_format($service->tax_rate, 2) }}%</td>
                <td>{{ $service->active ? 'Activo' : 'Inactivo' }}</td>
                <td class="actions">
                    @if(auth()->user()->isAdmin())
                        <a class="btn light" href="{{ route('services.edit', $service) }}">Editar</a>
                        <form method="post" action="{{ route('services.destroy', $service) }}">@csrf @method('delete')<button class="btn danger swal-confirm" data-title="Eliminar servicio?" data-text="Se eliminara este servicio si no ha sido vendido." data-confirm="Si, eliminar">Eliminar</button></form>
                    @else
                        <span class="muted">Solo consulta</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted">No hay servicios.</td></tr>
        @endforelse
    </tbody>
</table>
<div class="pagination">{{ $services->links() }}</div>
@endsection
