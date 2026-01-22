<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Calendario de Vencimientos
        </x-slot>
        
        <div class="space-y-4">
            <div class="flex justify-between items-center mb-4">
                <div class="flex gap-4">
                    <span class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <span class="w-3 h-3 bg-red-500 rounded"></span>
                        Vencimientos
                    </span>
                    <span class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <span class="w-3 h-3 bg-blue-500 rounded"></span>
                        Límites de Respuesta
                    </span>
                </div>
            </div>
            
            <div class="w-full overflow-x-auto">
                <table class="w-full border-collapse border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden" style="table-layout: fixed;">
                    <thead>
                        <tr>
                            @php
                                $daysOfWeek = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
                            @endphp
                            @foreach($daysOfWeek as $index => $day)
                                <th class="bg-gray-50 dark:bg-gray-800 p-2 text-center text-xs font-bold text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 {{ $index >= 5 ? 'text-red-600 dark:text-red-400' : '' }}" style="width: 14.28%;">
                                    {{ $day }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $currentMonth = now()->startOfMonth();
                            $daysInMonth = $currentMonth->daysInMonth;
                            // Carbon dayOfWeek: 0=Sunday, 1=Monday, etc. Ajustamos para que lunes=0
                            $firstDayOfWeek = ($currentMonth->dayOfWeek == 0) ? 6 : $currentMonth->dayOfWeek - 1;
                            $events = $this->getEvents();
                            $totalCells = $firstDayOfWeek + $daysInMonth;
                            $weeks = ceil($totalCells / 7);
                        @endphp
                        
                        @for($week = 0; $week < $weeks; $week++)
                            <tr>
                                @for($dayOfWeek = 0; $dayOfWeek < 7; $dayOfWeek++)
                                    @php
                                        $cellIndex = $week * 7 + $dayOfWeek;
                                        $isWeekend = $dayOfWeek >= 5;
                                        
                                        if ($cellIndex < $firstDayOfWeek || $cellIndex >= $firstDayOfWeek + $daysInMonth) {
                                            // Celda vacía
                                            $day = null;
                                            $date = null;
                                            $dayEvents = collect([]);
                                        } else {
                                            $day = $cellIndex - $firstDayOfWeek + 1;
                                            $date = $currentMonth->copy()->day($day)->format('Y-m-d');
                                            $dayEvents = collect($events)->filter(fn($e) => $e['date'] === $date);
                                        }
                                    @endphp
                                    
                                    <td class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-1 align-top" style="height: 100px; vertical-align: top;">
                                        @if($day)
                                            <div class="h-full flex flex-col">
                                                <span class="text-xs font-medium mb-1 {{ $isWeekend ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400' }}">
                                                    {{ $day }}
                                                </span>
                                                <div class="flex-1 space-y-0.5 overflow-hidden">
                                                    @foreach($dayEvents->take(2) as $event)
                                                        <div class="text-[10px] px-1.5 py-0.5 rounded truncate cursor-pointer font-medium
                                                            {{ $event['color'] === 'red' ? 'bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300' : 'bg-blue-100 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300' }}">
                                                            {{ Str::limit($event['title'], 18) }}
                                                        </div>
                                                    @endforeach
                                                    @if($dayEvents->count() > 2)
                                                        <div class="text-[10px] text-gray-500 dark:text-gray-400 px-1">
                                                            +{{ $dayEvents->count() - 2 }} más
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
