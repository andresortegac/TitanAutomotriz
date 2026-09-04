@extends('layouts.app')
@section('title', 'Nuevo usuario')
@section('content')
<h1>Nuevo usuario</h1><form class="panel" method="post" action="{{ route('users.store') }}">@csrf @include('users.form')</form>
@endsection
