@extends('layouts.admin-flowbite')

@section('title', 'Backups del Sistema - RAMS')

@section('page-title', 'Backups del Sistema')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Backups</span>
        </div>
    </li>
@endsection

@section('content')
    @if (session('success'))
        <div class="mb-4 p-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-4 text-sm text-red-800 bg-red-50 border border-red-200 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-database mr-2 text-teal-600"></i>
                    Historial de Backups
                </h2>
                <form method="POST" action="{{ route('admin.backups.store') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-teal-600 text-white text-sm font-medium rounded-lg hover:bg-teal-700">
                        <i class="fas fa-cloud-upload-alt mr-2"></i>
                        Crear Backup
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Tamaño</th>
                            <th class="px-4 py-3">Creado por</th>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($backups as $backup)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $backup->name }}</td>
                                <td class="px-4 py-3">
                                    @if($backup->size_bytes)
                                        {{ number_format($backup->size_bytes / 1024, 1) }} KB
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    {{ $backup->user?->name ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ $backup->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form action="{{ route('admin.backups.restore', $backup) }}" method="POST"
                                              onsubmit="return confirm('Atención: restaurar este backup de Drive reemplazará todos los datos. ¿Deseas continuar?');">
                                            @csrf
                                            <button type="submit"
                                                    class="text-orange-600 hover:text-orange-800"
                                                    title="Restaurar desde Drive">
                                                <i class="fas fa-upload"></i>
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.backups.download', $backup) }}"
                                           class="text-blue-600 hover:text-blue-800"
                                           title="Descargar backup">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <form action="{{ route('admin.backups.destroy', $backup) }}"
                                              method="POST"
                                              onsubmit="return confirm('¿Eliminar este backup? No se borrarán datos del sistema, solo el archivo.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-600 hover:text-red-800"
                                                    title="Eliminar backup">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                    No hay backups registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($backups->hasPages())
                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $backups->links() }}
                </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-red-200 p-6">
            <h2 class="text-lg font-semibold text-red-700 mb-2">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Reinicio de Datos
            </h2>
            <p class="text-sm text-red-700 mb-3">
                Esta acción <strong>eliminará todos los datos de empresas, expedientes, documentos, logs y usuarios</strong>,
                conservando únicamente los usuarios con rol <strong>super_admin</strong>.
            </p>
            <p class="text-sm text-gray-600 mb-4">
                Úsalo solo después de haber creado y descargado un backup. No se puede deshacer.
            </p>

            <div class="mb-6 border border-gray-200 rounded-lg p-4 bg-gray-50">
                <h3 class="text-base font-semibold text-gray-700 mb-2">Importar backup (restaurar datos)</h3>
                <form method="POST" action="{{ route('admin.backups.import') }}" enctype="multipart/form-data"
                      onsubmit="return confirm('Atención: la importación restablecerá TODO con los datos del backup seleccionado. ¿Deseas continuar?');">
                    @csrf

                    <label class="block text-sm font-medium text-gray-700 mb-1" for="backup_file">Archivo JSON de backup</label>
                    <div class="flex items-center gap-3 mb-2">
                        <label for="backup_file" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-slate-800 text-white text-sm font-medium cursor-pointer hover:bg-slate-900 transition-colors">
                            <i class="fas fa-file-upload mr-2"></i>
                            Seleccionar archivo
                        </label>
                        <span id="backup_file_name" class="text-xs text-gray-500 truncate">Ningún archivo seleccionado</span>
                    </div>
                    <input id="backup_file" name="backup_file" type="file" accept="application/json" required
                           class="hidden"
                           onchange="document.getElementById('backup_file_name').innerText = this.files.length ? this.files[0].name : 'Ningún archivo seleccionado';" />

                    <label class="flex items-center text-sm mb-3">
                        <input type="checkbox" name="confirm_restore" value="1" required class="mr-2">
                        Confirmo que deseo restablecer todos los datos con el backup importado.
                    </label>

                    <button type="submit"
                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-orange-600 text-white text-sm font-semibold rounded-lg hover:bg-orange-700">
                        <i class="fas fa-file-import mr-2"></i>
                        Importar y restaurar backup
                    </button>
                </form>
            </div>

            <form method="POST" action="{{ route('admin.backups.wipe') }}"
                  onsubmit="return confirm('¿Estás completamente seguro? Esta acción borrará todos los datos.');">
                @csrf

                <div class="mb-3">
                    <label class="inline-flex items-center text-sm text-gray-700">
                        <input type="checkbox" name="preserve_current_user" value="1" checked class="mr-2" />
                        Conservar usuario actual autenticado
                    </label>
                </div>

                <div class="mb-3">
                    <label class="inline-flex items-center text-sm text-gray-700">
                        <input type="checkbox" name="preserve_roles_permissions" value="1" checked class="mr-2" />
                        Conservar roles y permisos (roles, permisos, asignaciones)
                    </label>
                </div>

                <button type="submit"
                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700">
                    <i class="fas fa-broom mr-2"></i>
                    Borrar datos de negocio (conservar super_admin)
                </button>
            </form>
        </div>
    </div>
@endsection

