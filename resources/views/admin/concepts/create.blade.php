@extends('layouts.admin-flowbite')

@section('title', 'Nuevo concepto')
@section('page-title', 'Nuevo concepto')

@section('content')
    @include('admin.partials.flash')
    @include('admin.concepts._form', ['action' => route('admin.concepts.store'), 'method' => 'POST'])
@endsection
