@extends('layouts.app')
@section('title', 'Cargue masivo de productos')
@section('content')
<div class="panel" style="max-width:760px;">
    <h1>Cargue masivo de productos</h1>
    <p class="muted">Carga un Excel (.xlsx) o CSV con las columnas del archivo de compras: <strong>Código, Descripción, Cantidad, Valor Unitario y % IVA</strong>. El valor unitario se toma como costo y se calcula el precio de venta con el margen indicado. A cada producto se le asignará automáticamente el siguiente código de barras interno.</p>
    <p><a class="btn light" href="{{ route('products.import.template') }}">Descargar plantilla para Excel</a></p>
    <form method="post" action="{{ route('products.import.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-grid">
            <label class="span-2">Archivo Excel o CSV<input type="file" name="file" accept=".xlsx,.csv,.txt" required></label>
            <label>Categoría para todos los productos<select name="category_id" required><option value="">Seleccione</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>@endforeach</select></label>
            <label>Proveedor (opcional)<select name="supplier_id"><option value="">Sin proveedor</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>@endforeach</select></label>
            <label>Stock mínimo<select name="min_stock"><option value="0" @selected(old('min_stock', 1) == 0)>0</option><option value="1" @selected(old('min_stock', 1) == 1)>1</option><option value="5" @selected(old('min_stock', 1) == 5)>5</option><option value="10" @selected(old('min_stock', 1) == 10)>10</option></select></label>
            <label>Margen de venta (%)<input type="number" name="markup" min="0" step="0.01" value="{{ old('markup', 0) }}" required></label>
        </div>
        <div class="actions" style="margin-top:14px;"><button class="btn">Importar productos</button><a class="btn light" href="{{ route('products.index') }}">Cancelar</a></div>
    </form>
</div>
@endsection
