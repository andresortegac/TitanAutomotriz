@extends('layouts.app')
@section('title', 'Nuevo gasto')
@section('content')
<h1>Nuevo gasto</h1>
<form class="panel" method="post" action="{{ route('expenses.store') }}">@csrf @include('expenses.form')</form>
@endsection
