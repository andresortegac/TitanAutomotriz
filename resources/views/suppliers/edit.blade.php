@extends('layouts.app')
@section('title', 'Editar proveedor')
@section('content')
<h1>Editar proveedor</h1><form class="panel" method="post" action="{{ route('suppliers.update', $supplier) }}">@csrf @method('put') @include('suppliers.form')</form>
@endsection
