@extends('layouts.app')
@section('title', 'Editar cliente')
@section('content')
<h1>Editar cliente</h1><form class="panel" method="post" action="{{ route('customers.update', $customer) }}">@csrf @method('put') @include('customers.form')</form>
@endsection
