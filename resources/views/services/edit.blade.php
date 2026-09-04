@extends('layouts.app')
@section('title', 'Editar servicio')
@section('content')
<h1>Editar servicio</h1>
<form class="panel" method="post" action="{{ route('services.update', $service) }}">@csrf @method('put') @include('services.form')</form>
@endsection
