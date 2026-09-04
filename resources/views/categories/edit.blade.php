@extends('layouts.app')
@section('title', 'Editar categoria')
@section('content')
<h1>Editar categoria</h1><form class="panel" method="post" action="{{ route('categories.update', $category) }}">@csrf @method('put') @include('categories.form')</form>
@endsection
