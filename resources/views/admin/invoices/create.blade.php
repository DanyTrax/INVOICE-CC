@extends('layouts.admin-flowbite')

@section('title', 'Nueva cuenta de cobro')
@section('page-title', 'Nueva cuenta de cobro')

@section('content')
    @include('admin.partials.flash')
    @include('admin.invoices._form', [
        'action' => route('admin.invoices.store'),
        'method' => 'POST',
        'associates' => $associates,
        'concepts' => $concepts,
    ])
@endsection
