@extends('layouts.app')
@section('title', 'Cargue masivo de productos')
@section('content')
<div class="panel" style="max-width:760px;">
    <h1>Cargue masivo de productos</h1>
    <p class="muted">Carga un Excel (.xlsx) o CSV con: <strong>Código, Descripción, Cantidad, Valor Unitario, Precio de Venta, % IVA, Categoría y Stock Mínimo</strong>. El valor unitario será el costo y el producto no tendrá proveedor. Precio de Venta puede quedar vacío. Si la categoría no existe, se creará automáticamente. Cada producto recibe el siguiente código de barras interno.</p>
    <p><a class="btn light" href="{{ route('products.import.template') }}">Descargar plantilla para Excel</a></p>
    <form method="post" action="{{ route('products.import.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-grid">
            <label class="span-2">Archivo Excel o CSV<input type="file" name="file" accept=".xlsx,.csv,.txt" required></label>
        </div>
        <div class="actions" style="margin-top:14px;"><button class="btn">Importar productos</button><a class="btn light" href="{{ route('products.index') }}">Cancelar</a></div>
    </form>
</div>
@endsection
