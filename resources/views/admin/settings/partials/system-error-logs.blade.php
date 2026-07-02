<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
     x-data="{
        openId: null,
        copy(id) {
            const el = this.$refs['copy' + id];
            if (! el) return;
            navigator.clipboard.writeText(el.value).then(() => {
                this.copiedId = id;
                setTimeout(() => { if (this.copiedId === id) this.copiedId = null; }, 2000);
            });
        },
        copiedId: null,
     }">
    <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-bug text-red-500 mr-2"></i>
                Errores de la aplicación
            </h3>
            <p class="text-sm text-gray-600 mt-1">
                Fallos internos (error 500) registrados automáticamente. Revisa el detalle, cópialo para reportarlo y elimínalo cuando esté solucionado.
            </p>
        </div>
        @if($errorLogs && $errorLogs->total() > 0)
        <div class="flex flex-wrap gap-2">
            <form action="{{ route('admin.settings.error-logs.clear') }}" method="POST"
                  onsubmit="return confirm('¿Eliminar solo los errores marcados como solucionados?');">
                @csrf
                @method('DELETE')
                <input type="hidden" name="only_resolved" value="1">
                <button type="submit" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                    <i class="fas fa-broom mr-2"></i> Limpiar solucionados
                </button>
            </form>
            <form action="{{ route('admin.settings.error-logs.clear') }}" method="POST"
                  onsubmit="return confirm('¿Eliminar TODO el historial de errores? Esta acción no se puede deshacer.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-3 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                    <i class="fas fa-trash mr-2"></i> Vaciar historial
                </button>
            </form>
        </div>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 text-sm text-green-800 bg-green-50 border border-green-200 rounded-lg">{{ session('success') }}</div>
    @endif

    @if(! $errorLogs || $errorLogs->total() === 0)
        <div class="text-center py-10 text-gray-500">
            <i class="fas fa-check-circle text-3xl text-green-400 mb-3"></i>
            <p class="text-sm">No hay errores registrados. Todo funciona correctamente.</p>
        </div>
    @else
        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="w-full text-sm text-left text-gray-700">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                    <tr>
                        <th class="px-3 py-2 w-40">Fecha</th>
                        <th class="px-3 py-2">Mensaje / dónde</th>
                        <th class="px-3 py-2 w-48">Usuario</th>
                        <th class="px-3 py-2 w-44 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($errorLogs as $log)
                        <tr class="border-b border-gray-200 hover:bg-gray-50 {{ $log->resolved_at ? 'opacity-60' : '' }}">
                            <td class="px-3 py-2 align-top whitespace-nowrap text-gray-600">
                                {{ $log->created_at?->format('d/m/Y H:i:s') }}
                                @if($log->resolved_at)
                                    <span class="mt-1 block"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-800">Solucionado</span></span>
                                @endif
                            </td>
                            <td class="px-3 py-2 align-top">
                                <p class="font-medium text-gray-900 break-words">{{ Str::limit($log->message, 160) }}</p>
                                <p class="text-xs text-gray-500 mt-1 font-mono break-all">
                                    <i class="fas fa-code-branch mr-1"></i>{{ $log->short_location }}
                                </p>
                                @if($log->url)
                                    <p class="text-xs text-gray-500 mt-0.5 break-all">
                                        <i class="fas fa-link mr-1"></i>{{ $log->method }} {{ $log->url }}
                                    </p>
                                @endif
                                <button type="button" @click="openId = (openId === {{ $log->id }} ? null : {{ $log->id }})"
                                        class="mt-2 text-xs font-medium text-teal-600 hover:text-teal-800">
                                    <span x-show="openId !== {{ $log->id }}"><i class="fas fa-chevron-down mr-1"></i>Ver detalle</span>
                                    <span x-show="openId === {{ $log->id }}" x-cloak><i class="fas fa-chevron-up mr-1"></i>Ocultar detalle</span>
                                </button>
                                <div x-show="openId === {{ $log->id }}" x-cloak class="mt-2">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs font-semibold text-gray-600">Detalle completo</span>
                                        <button type="button" @click="copy({{ $log->id }})"
                                                class="text-xs font-medium text-teal-600 hover:text-teal-800">
                                            <span x-show="copiedId !== {{ $log->id }}"><i class="fas fa-copy mr-1"></i>Copiar</span>
                                            <span x-show="copiedId === {{ $log->id }}" x-cloak><i class="fas fa-check mr-1"></i>Copiado</span>
                                        </button>
                                    </div>
                                    <textarea x-ref="copy{{ $log->id }}" readonly rows="10"
                                              class="w-full text-xs font-mono bg-gray-900 text-gray-100 rounded-lg p-3 border border-gray-700 resize-y">{{ $log->copy_text }}</textarea>
                                </div>
                            </td>
                            <td class="px-3 py-2 align-top text-gray-700 break-words">
                                {{ $log->user_name ?? 'Invitado / sin sesión' }}
                                @if($log->ip)
                                    <span class="block text-xs text-gray-400 mt-0.5">{{ $log->ip }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex flex-col items-end gap-1">
                                    <form action="{{ route('admin.settings.error-logs.resolve', $log) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-xs font-medium {{ $log->resolved_at ? 'text-amber-600 hover:text-amber-800' : 'text-green-600 hover:text-green-800' }}">
                                            <i class="fas {{ $log->resolved_at ? 'fa-rotate-left' : 'fa-check' }} mr-1"></i>
                                            {{ $log->resolved_at ? 'Reabrir' : 'Marcar solucionado' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.settings.error-logs.destroy', $log) }}" method="POST"
                                          onsubmit="return confirm('¿Eliminar este registro de error?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash mr-1"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $errorLogs->links() }}
        </div>
    @endif
</div>
