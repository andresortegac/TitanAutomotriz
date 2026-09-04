@extends('layouts.app')
@section('title', 'Gastos')
@section('content')
<div class="actions" style="justify-content:space-between;margin-bottom:14px;"><h1>Gastos</h1><a class="btn" href="{{ route('expenses.create') }}">Nuevo gasto</a></div>

<form class="panel" method="get" style="margin-bottom:14px;">
    <div class="form-grid">
        <label>Buscar<input name="search" placeholder="Descripcion, referencia o nota" value="{{ request('search') }}"></label>
        <label>Categoria<select name="category"><option value="">Todas</option>@foreach($categories as $category)<option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>@endforeach</select></label>
        <label>Desde<input type="date" name="from" value="{{ request('from') }}"></label>
        <label>Hasta<input type="date" name="to" value="{{ request('to') }}"></label>
    </div>
    <div class="actions" style="margin-top:14px;"><button class="btn">Filtrar</button><a class="btn light" href="{{ route('expenses.index') }}">Limpiar</a></div>
</form>

<div class="grid grid-2" style="margin-bottom:14px;">
    <div class="panel"><div class="muted">Total gastos filtrados</div><div class="metric">${{ number_format($total, 0) }}</div></div>
    <div class="panel"><div class="muted">Registros en pantalla</div><div class="metric">{{ $expenses->count() }}</div></div>
</div>

<table>
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Categoria</th>
            <th>Descripcion</th>
            <th>Pago</th>
            <th>Referencia</th>
            <th>Valor</th>
            <th>Usuario</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse($expenses as $expense)
            <tr>
                <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                <td>{{ $expense->category }}</td>
                <td>{{ $expense->description }}</td>
                <td>{{ ucfirst($expense->payment_method) }}</td>
                <td>{{ $expense->reference ?? '-' }}</td>
                <td>${{ number_format($expense->amount, 0) }}</td>
                <td>{{ $expense->user->name }}</td>
                <td class="actions">
                    <a class="btn light" href="{{ route('expenses.edit', $expense) }}">Editar</a>
                    <form method="post" action="{{ route('expenses.destroy', $expense) }}">@csrf @method('delete')<button class="btn danger swal-confirm" data-title="Eliminar este gasto?" data-text="Se eliminara el registro del gasto." data-confirm="Si, eliminar">Eliminar</button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="muted">No hay gastos registrados.</td></tr>
        @endforelse
    </tbody>
</table>
<div class="pagination">{{ $expenses->links() }}</div>
@endsection
