@extends('layouts.app')
@section('title', 'Nuevo servicio')
@section('content')
<h1>Nuevo servicio</h1>
<form class="panel" method="post" action="{{ route('services.store') }}">@csrf @include('services.form')</form>
@endsection
