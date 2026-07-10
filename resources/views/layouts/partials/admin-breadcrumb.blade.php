@php
    $adminBreadcrumbTrail = \App\Support\AdminBreadcrumb::resolve();
@endphp

@foreach ($adminBreadcrumbTrail as $crumb)
    <li class="inline-flex items-center">
        <i class="fas fa-chevron-right text-gray-400 dark:text-slate-500 mx-1.5 text-[10px]" aria-hidden="true"></i>
        @if (! empty($crumb['url']))
            <a href="{{ $crumb['url'] }}" class="text-gray-600 dark:text-slate-400 hover:text-teal-700 dark:hover:text-teal-400">
                {{ $crumb['label'] }}
            </a>
        @else
            <span class="font-medium text-gray-700 dark:text-slate-200" aria-current="page">{{ $crumb['label'] }}</span>
        @endif
    </li>
@endforeach
