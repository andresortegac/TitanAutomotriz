@extends('layouts.app')
@section('title', 'Nueva categoria')
@section('content')
<h1>Nueva categoria</h1><form class="panel" method="post" action="{{ route('categories.store') }}">@csrf @include('categories.form')</form>
@endsection
