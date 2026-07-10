@if(session('success'))
    <div class="mb-4 p-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 p-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="mb-4 p-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200">
        <ul class="list-disc ps-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
