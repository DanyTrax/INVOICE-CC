@extends('layouts.admin-flowbite')

@section('title', 'Editar concepto')
@section('page-title', 'Editar concepto')

@section('content')
    @include('admin.partials.flash')
    @include('admin.concepts._form', ['action' => route('admin.concepts.update', $concept), 'method' => 'PUT', 'concept' => $concept])
@endsection
