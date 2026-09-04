@extends('layouts.app')
@section('title', 'Nuevo proveedor')
@section('content')
<h1>Nuevo proveedor</h1><form class="panel" method="post" action="{{ route('suppliers.store') }}">@csrf @include('suppliers.form')</form>
@endsection
