<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex justify-between items-center w-full">
                <span>Calendario de Vencimientos</span>
                <div class="flex gap-2">
                    <button 
                        wire:click="previousMonth"
                        class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Mes Anterior
                    </button>
                    <button 
                        wire:click="nextMonth"
                        class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Mes Siguiente
                    </button>
                </div>
            </div>
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
            
            <div class="w-full overflow-x-auto bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                <table class="w-full border-collapse" style="table-layout: fixed;">
                    <thead>
                        <tr>
                            @php
                                $daysOfWeek = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
                            @endphp
                            @foreach($daysOfWeek as $index => $day)
                                <th class="bg-gray-50 dark:bg-gray-800 p-3 text-center text-xs font-bold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700 last:border-r-0 {{ $index >= 5 ? 'text-red-600 dark:text-red-400' : '' }}" style="width: 14.28%;">
                                    {{ $day }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $currentMonth = $this->getCurrentMonth();
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
                                            $isCurrentMonth = false;
                                        } else {
                                            $day = $cellIndex - $firstDayOfWeek + 1;
                                            $date = $currentMonth->copy()->day($day)->format('Y-m-d');
                                            $dayEvents = collect($events)->filter(fn($e) => $e['date'] === $date);
                                            $isCurrentMonth = true;
                                        }
                                    @endphp
                                    
                                    <td class="bg-white dark:bg-gray-900 border-b border-r border-gray-200 dark:border-gray-700 last:border-r-0 p-2 align-top hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors" style="height: 120px; vertical-align: top; min-width: 0;">
                                        @if($day)
                                            <div class="h-full flex flex-col">
                                                <span class="text-sm font-semibold mb-2 {{ $isWeekend ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100' }}">
                                                    {{ $day }}
                                                </span>
                                                <div class="flex-1 space-y-1 overflow-y-auto">
                                                    @foreach($dayEvents as $event)
                                                        <div class="text-[11px] px-2 py-1 rounded font-medium truncate cursor-pointer transition-all hover:shadow-sm
                                                            {{ $event['color'] === 'red' 
                                                                ? 'bg-red-100 dark:bg-red-900/40 border-l-2 border-red-500 text-red-800 dark:text-red-200' 
                                                                : 'bg-blue-100 dark:bg-blue-900/40 border-l-2 border-blue-500 text-blue-800 dark:text-blue-200' }}">
                                                            {{ $event['title'] }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <div class="h-full bg-gray-50 dark:bg-gray-800/50"></div>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            
            <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                {{ $currentMonth->locale('es')->translatedFormat('F Y') }}
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
