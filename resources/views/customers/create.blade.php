@extends('layouts.app')
@section('title', 'Nuevo cliente')
@section('content')
<h1>Nuevo cliente</h1><form class="panel" method="post" action="{{ route('customers.store') }}">@csrf @include('customers.form')</form>
@endsection
