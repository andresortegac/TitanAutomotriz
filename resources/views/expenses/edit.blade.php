@extends('layouts.app')
@section('title', 'Editar gasto')
@section('content')
<h1>Editar gasto</h1>
<form class="panel" method="post" action="{{ route('expenses.update', $expense) }}">@csrf @method('put') @include('expenses.form')</form>
@endsection
