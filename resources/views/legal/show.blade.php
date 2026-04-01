@extends('layouts.legal')

@section('title', $title)

@section('content')
<div class="space-y-6 text-gray-700">
    {!! $bodyHtml !!}
</div>
@endsection
