@extends('layouts.admin-flowbite')

@section('title', 'Editar cuenta de cobro')
@section('page-title', 'Editar cuenta de cobro')

@section('content')
    @include('admin.partials.flash')
    @include('admin.invoices._form', [
        'action' => route('admin.invoices.update', $invoice),
        'method' => 'PUT',
        'invoice' => $invoice,
        'associates' => $associates,
        'concepts' => $concepts,
        'statuses' => $statuses,
    ])
@endsection
