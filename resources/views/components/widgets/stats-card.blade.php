@props([
    'title',
    'value',
    'icon' => 'chart-bar',
    'color' => 'blue',
    'link' => null,
    'subtitle' => null,
])

@php
    $colors = [
        'blue' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'link' => 'text-blue-600 hover:text-blue-700'],
        'red' => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'link' => 'text-red-600 hover:text-red-700'],
        'teal' => ['bg' => 'bg-teal-100', 'text' => 'text-teal-600', 'link' => 'text-teal-600 hover:text-teal-700'],
        'green' => ['bg' => 'bg-green-100', 'text' => 'text-green-600', 'link' => 'text-green-600 hover:text-green-700'],
        'yellow' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600', 'link' => 'text-yellow-600 hover:text-yellow-700'],
    ];
    $colorClasses = $colors[$color] ?? $colors['blue'];
@endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-600 mb-1">{{ $title }}</p>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($value) }}</p>
            @if($subtitle)
                <p class="text-xs text-gray-500 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="w-12 h-12 {{ $colorClasses['bg'] }} rounded-lg flex items-center justify-center">
            <i class="fas fa-{{ $icon }} {{ $colorClasses['text'] }} text-xl"></i>
        </div>
    </div>
    @if($link)
        <a href="{{ $link }}" class="{{ $colorClasses['link'] }} text-sm mt-4 inline-flex items-center">
            Ver más <i class="fas fa-arrow-right ml-2"></i>
        </a>
    @endif
</div>
