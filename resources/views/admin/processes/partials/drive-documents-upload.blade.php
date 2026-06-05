{{-- Subida de archivos a la carpeta Drive del proceso (uno o varios). --}}
<form id="process-drive-upload-form"
      action="{{ route('admin.processes.documents.upload', $process) }}"
      method="POST"
      class="mb-4">
    @csrf
    <label class="block text-sm font-medium text-gray-700 mb-2">Subir documento(s)</label>
    <div class="flex flex-col sm:flex-row sm:items-start gap-3">
        <label for="process-document-files"
               class="flex flex-1 flex-col items-center justify-center min-h-[7rem] border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 px-4 py-4">
            <i class="fas fa-cloud-upload-alt text-2xl text-teal-600 mb-2"></i>
            <p class="mb-1 text-sm text-gray-600 text-center">
                <span class="font-semibold text-gray-800">Clic para seleccionar</span> o arrastra archivos aquí
            </p>
            <p class="text-xs text-gray-500 text-center">Uno o varios archivos · PDF, Office, imágenes · Máx. 10 MB c/u</p>
            <input type="file"
                   id="process-document-files"
                   multiple
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,image/*"
                   class="sr-only">
        </label>
        <button type="submit"
                id="process-drive-upload-submit"
                class="shrink-0 px-4 py-2.5 bg-teal-600 text-white text-sm font-medium rounded-lg hover:bg-teal-700 disabled:opacity-50 disabled:cursor-not-allowed"
                disabled>
            <i class="fas fa-upload mr-2"></i> Subir
        </button>
    </div>
    <div id="process-drive-file-list" class="mt-3 space-y-2 hidden"></div>
    @error('documents')
        <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
    @enderror
    @error('documents.*')
        <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
    @enderror
    @error('document')
        <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
    @enderror
</form>

@push('scripts')
<script>
(function () {
    var input = document.getElementById('process-document-files');
    var list = document.getElementById('process-drive-file-list');
    var submitBtn = document.getElementById('process-drive-upload-submit');
    var form = document.getElementById('process-drive-upload-form');
    var maxBytes = 10 * 1024 * 1024;
    var defaultSubmitHtml = '<i class="fas fa-upload mr-2"></i> Subir';

    if (!input || !list || !submitBtn || !form) {
        return;
    }

    function syncSubmitState() {
        var hasFiles = input.files && input.files.length > 0;
        submitBtn.disabled = !hasFiles;
    }

    function renderFileList() {
        list.innerHTML = '';
        if (!input.files || input.files.length === 0) {
            list.classList.add('hidden');
            syncSubmitState();
            return;
        }
        list.classList.remove('hidden');
        Array.from(input.files).forEach(function (file, index) {
            var row = document.createElement('div');
            row.className = 'flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm';
            var sizeMb = (file.size / 1024 / 1024).toFixed(2);
            row.innerHTML =
                '<div class="flex items-center gap-2 min-w-0">' +
                '<i class="fas fa-file text-teal-600 shrink-0"></i>' +
                '<div class="min-w-0"><p class="font-medium text-gray-900 truncate">' + file.name + '</p>' +
                '<p class="text-xs text-gray-500">' + sizeMb + ' MB</p></div></div>' +
                '<button type="button" class="text-red-600 hover:text-red-800 shrink-0 ml-2" data-index="' + index + '" aria-label="Quitar archivo">' +
                '<i class="fas fa-times"></i></button>';
            list.appendChild(row);
        });
        list.querySelectorAll('button[data-index]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var idx = parseInt(btn.getAttribute('data-index'), 10);
                var dt = new DataTransfer();
                Array.from(input.files).forEach(function (f, i) {
                    if (i !== idx) {
                        dt.items.add(f);
                    }
                });
                input.files = dt.files;
                renderFileList();
            });
        });
        syncSubmitState();
    }

    function readFileAsBase64(file) {
        return new Promise(function (resolve, reject) {
            var reader = new FileReader();
            reader.onload = function () {
                resolve(reader.result);
            };
            reader.onerror = function () {
                reject(new Error('No se pudo leer el archivo.'));
            };
            reader.readAsDataURL(file);
        });
    }

    input.addEventListener('change', renderFileList);

    ['dragenter', 'dragover'].forEach(function (ev) {
        form.addEventListener(ev, function (e) {
            e.preventDefault();
            e.stopPropagation();
        });
    });
    form.addEventListener('drop', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
            input.files = e.dataTransfer.files;
            renderFileList();
        }
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var files = Array.from(input.files || []);
        if (!files.length) {
            alert('Seleccione al menos un archivo para subir.');
            return;
        }

        var oversized = files.filter(function (file) {
            return file.size > maxBytes;
        });
        if (oversized.length) {
            alert('Cada archivo debe pesar máximo 10 MB. Revise: ' + oversized.map(function (f) { return f.name; }).join(', '));
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Subiendo…';

        Promise.all(files.map(readFileAsBase64))
            .then(function (contents) {
                var formData = new FormData();
                var tokenInput = form.querySelector('input[name="_token"]');
                if (tokenInput) {
                    formData.append('_token', tokenInput.value);
                }

                files.forEach(function (file, index) {
                    formData.append('documents_payload[' + index + '][name]', file.name);
                    formData.append('documents_payload[' + index + '][mime]', file.type || 'application/octet-stream');
                    formData.append('documents_payload[' + index + '][content]', contents[index]);
                });

                return fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    redirect: 'follow',
                });
            })
            .then(function (response) {
                window.location.href = response.url;
            })
            .catch(function () {
                submitBtn.disabled = false;
                submitBtn.innerHTML = defaultSubmitHtml;
                alert('No se pudo subir el archivo. Intente de nuevo.');
            });
    });
})();
</script>
@endpush
