@extends('layouts.legal')

@section('title', $title)

@section('content')
@if(! empty(trim($pageTitle ?? '')))
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">{{ $pageTitle }}</h1>
@endif
<div class="space-y-6 text-gray-700">
    {!! $bodyHtml !!}
</div>
@endsection
