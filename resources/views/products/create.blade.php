@extends('layouts.app')
@section('title', 'Nuevo producto')
@section('content')
<h1>Nuevo producto</h1><form class="panel" method="post" action="{{ route('products.store') }}" enctype="multipart/form-data">@csrf @include('products.form')</form>
@endsection
