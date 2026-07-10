@extends('layouts.admin-flowbite')

@section('title', 'Editar asociado')
@section('page-title', 'Editar asociado')

@section('content')
    @include('admin.partials.flash')
    @include('admin.associates._form', ['action' => route('admin.associates.update', $associate), 'method' => 'PUT', 'associate' => $associate])
@endsection
