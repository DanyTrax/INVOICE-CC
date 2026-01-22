<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex justify-between items-center w-full">
                <span>Calendario de Vencimientos</span>
                <div class="flex gap-2">
                    <button 
                        wire:click="previousMonth"
                        class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Mes Anterior
                    </button>
                    <button 
                        wire:click="nextMonth"
                        class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Mes Siguiente
                    </button>
                </div>
            </div>
        </x-slot>
        
        <div class="space-y-4">
            <div class="flex gap-4 mb-4">
                <span class="inline-flex items-center gap-2 text-sm text-gray-600">
                    <span class="w-3 h-3 bg-red-500 rounded"></span>
                    Vencimientos
                </span>
                <span class="inline-flex items-center gap-2 text-sm text-gray-600">
                    <span class="w-3 h-3 bg-blue-500 rounded"></span>
                    Límites de Respuesta
                </span>
            </div>
            
            @php
                $currentMonth = $this->getCurrentMonth();
                $daysInMonth = $currentMonth->daysInMonth;
                $firstDayOfWeek = ($currentMonth->dayOfWeek == 0) ? 6 : $currentMonth->dayOfWeek - 1;
                $events = $this->getEvents();
                $weeks = [];
                $day = 1;
                
                // Construir semanas
                for ($week = 0; $week < 6; $week++) {
                    $weeks[$week] = [];
                    for ($dow = 0; $dow < 7; $dow++) {
                        if (($week == 0 && $dow < $firstDayOfWeek) || $day > $daysInMonth) {
                            $weeks[$week][$dow] = null;
                        } else {
                            $date = $currentMonth->copy()->day($day)->format('Y-m-d');
                            $dayEvents = collect($events)->filter(fn($e) => $e['date'] === $date);
                            $weeks[$week][$dow] = [
                                'day' => $day,
                                'date' => $date,
                                'events' => $dayEvents,
                                'isWeekend' => $dow >= 5
                            ];
                            $day++;
                        }
                    }
                    if ($day > $daysInMonth) break;
                }
            @endphp
            
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                <table class="w-full" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                    <thead>
                        <tr>
                            @foreach(['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'] as $index => $dayName)
                                <th class="bg-gray-50 p-3 text-center text-xs font-bold text-gray-700 {{ $index >= 5 ? 'text-red-600' : '' }}" style="width: 14.28%; border: 1px solid #e5e7eb; border-top: none; {{ $index == 0 ? 'border-left: none;' : '' }} {{ $index == 6 ? 'border-right: none;' : '' }}">
                                    {{ $dayName }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($weeks as $week)
                            <tr>
                                @foreach($week as $index => $cell)
                                    <td class="p-2 align-top" style="height: 120px; vertical-align: top; border: 1px solid #e5e7eb; {{ $index == 0 ? 'border-left: none;' : '' }} {{ $index == 6 ? 'border-right: none;' : '' }} background-color: {{ $cell ? '#ffffff' : '#f9fafb' }};">
                                        @if($cell)
                                            <div class="h-full flex flex-col">
                                                <span class="text-sm font-semibold mb-2 {{ $cell['isWeekend'] ? 'text-red-600' : 'text-gray-900' }}">
                                                    {{ $cell['day'] }}
                                                </span>
                                                <div class="flex-1 space-y-1 overflow-y-auto">
                                                    @foreach($cell['events'] as $event)
                                                        <div class="text-[11px] px-2 py-1 rounded font-medium {{ $event['color'] === 'red' ? 'bg-red-100 border-l-2 border-red-500 text-red-800' : 'bg-blue-100 border-l-2 border-blue-500 text-blue-800' }}">
                                                            {{ $event['title'] }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="text-center text-sm text-gray-500">
                {{ $currentMonth->locale('es')->translatedFormat('F Y') }}
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
