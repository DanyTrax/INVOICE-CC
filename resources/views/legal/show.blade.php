@extends('layouts.legal')

@section('title', $title)

@section('content')
<h2 class="text-2xl font-semibold text-gray-900 mb-4">{{ $heading }}</h2>
<p class="text-sm text-gray-500 mb-6">Última actualización: {{ now()->format('d/m/Y') }}</p>

<div class="space-y-6 text-gray-700">
    {!! $bodyHtml !!}
</div>
@endsection
