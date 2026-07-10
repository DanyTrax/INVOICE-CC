@extends('layouts.admin-flowbite')

@section('title', 'Nuevo asociado')
@section('page-title', 'Nuevo asociado')

@section('content')
    @include('admin.partials.flash')
    @include('admin.associates._form', ['action' => route('admin.associates.store'), 'method' => 'POST'])
@endsection
