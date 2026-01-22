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
            
            <div class="overflow-x-auto">
                <div class="inline-block min-w-full">
                    <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        @php
                            $daysOfWeek = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
                            $currentMonth = now()->startOfMonth();
                            $daysInMonth = $currentMonth->daysInMonth;
                            // Carbon dayOfWeek: 0=Sunday, 1=Monday, etc. Ajustamos para que lunes=0
                            $firstDayOfWeek = ($currentMonth->dayOfWeek == 0) ? 6 : $currentMonth->dayOfWeek - 1;
                            $events = $this->getEvents();
                        @endphp
                        
                        {{-- Headers de días de la semana --}}
                        @foreach($daysOfWeek as $index => $day)
                            <div class="bg-gray-50 dark:bg-gray-800 p-2 text-center text-xs font-bold text-gray-700 dark:text-gray-300 {{ $index >= 5 ? 'text-red-600 dark:text-red-400' : '' }}">
                                {{ $day }}
                            </div>
                        @endforeach
                        
                        {{-- Días vacíos al inicio --}}
                        @for($i = 0; $i < $firstDayOfWeek; $i++)
                            <div class="bg-white dark:bg-gray-900 min-h-[80px] p-1"></div>
                        @endfor
                        
                        {{-- Días del mes --}}
                        @for($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $date = $currentMonth->copy()->day($day)->format('Y-m-d');
                                $dayEvents = collect($events)->filter(fn($e) => $e['date'] === $date);
                                $isWeekend = ($firstDayOfWeek + $day - 1) % 7 >= 5;
                            @endphp
                            <div class="bg-white dark:bg-gray-900 min-h-[80px] p-1 border-r border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 relative">
                                <span class="text-xs font-medium {{ $isWeekend ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400' }}">{{ $day }}</span>
                                <div class="mt-1 space-y-0.5">
                                    @foreach($dayEvents->take(2) as $event)
                                        <div class="text-[10px] px-1.5 py-0.5 rounded truncate cursor-pointer font-medium
                                            {{ $event['color'] === 'red' ? 'bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300' : 'bg-blue-100 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300' }}">
                                            {{ Str::limit($event['title'], 20) }}
                                        </div>
                                    @endforeach
                                    @if($dayEvents->count() > 2)
                                        <div class="text-[10px] text-gray-500 dark:text-gray-400 px-1">
                                            +{{ $dayEvents->count() - 2 }} más
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
