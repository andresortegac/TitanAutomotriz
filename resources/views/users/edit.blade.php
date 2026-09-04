@extends('layouts.app')
@section('title', 'Editar usuario')
@section('content')
<h1>Editar usuario</h1><form class="panel" method="post" action="{{ route('users.update', $user) }}">@csrf @method('put') @include('users.form')</form>
@endsection
