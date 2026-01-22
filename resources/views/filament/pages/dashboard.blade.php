<x-filament-panels::page>
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                Resumen General
            </h2>
        </div>
        
        {{-- Widgets de Estadísticas --}}
        <x-filament-widgets::widgets
            :widgets="$this->getHeaderWidgets()"
            :columns="$this->getHeaderWidgetsColumns()"
        />
        
        {{-- Calendario --}}
        <x-filament-widgets::widgets
            :widgets="$this->getFooterWidgets()"
            :columns="$this->getFooterWidgetsColumns()"
        />
    </div>
</x-filament-panels::page>
