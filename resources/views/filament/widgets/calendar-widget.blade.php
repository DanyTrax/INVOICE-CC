<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Calendario de Vencimientos
        </x-slot>
        
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <div class="flex gap-2">
                    <span class="inline-flex items-center gap-2 text-sm">
                        <span class="w-3 h-3 bg-red-500 rounded"></span>
                        Vencimientos
                    </span>
                    <span class="inline-flex items-center gap-2 text-sm">
                        <span class="w-3 h-3 bg-blue-500 rounded"></span>
                        Límites de Respuesta
                    </span>
                </div>
            </div>
            
            <div class="grid grid-cols-7 gap-1 border border-gray-200 rounded-lg overflow-hidden">
                @php
                    $daysOfWeek = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
                    $currentMonth = now()->startOfMonth();
                    $daysInMonth = $currentMonth->daysInMonth;
                    $firstDayOfWeek = $currentMonth->dayOfWeek;
                    $events = $this->getEvents();
                @endphp
                
                @foreach($daysOfWeek as $day)
                    <div class="bg-gray-50 p-2 text-center text-xs font-bold text-gray-500">
                        {{ $day }}
                    </div>
                @endforeach
                
                @for($i = 0; $i < $firstDayOfWeek; $i++)
                    <div class="bg-white h-20"></div>
                @endfor
                
                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $date = $currentMonth->copy()->day($day)->format('Y-m-d');
                        $dayEvents = collect($events)->filter(fn($e) => $e['date'] === $date);
                    @endphp
                    <div class="bg-white h-20 p-1 border border-gray-100 hover:bg-gray-50 relative">
                        <span class="text-xs text-gray-400">{{ $day }}</span>
                        @foreach($dayEvents as $event)
                            <div class="mt-1 text-[10px] px-1 rounded truncate cursor-pointer
                                {{ $event['color'] === 'red' ? 'bg-red-100 border border-red-200 text-red-700' : 'bg-blue-100 border border-blue-200 text-blue-700' }}">
                                {{ $event['title'] }}
                            </div>
                        @endforeach
                    </div>
                @endfor
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
