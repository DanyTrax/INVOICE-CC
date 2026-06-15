{{-- Subida de archivos a la carpeta Drive del proceso (uno o varios). --}}
<form id="process-drive-upload-form"
      action="{{ route('admin.processes.documents.upload', $process) }}"
      method="POST"
      data-upload-version="3"
      class="mb-4"
      onsubmit="return false;">
    @csrf
    <label class="block text-sm font-medium text-gray-700 mb-2">Subir documento(s)</label>
    <div class="flex flex-col sm:flex-row sm:items-start gap-3">
        <div class="flex flex-1 flex-col items-center justify-center min-h-[7rem] border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 px-4 py-4"
             id="process-drive-dropzone"
             role="button"
             tabindex="0">
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
        </div>
        <button type="button"
                id="process-drive-upload-submit"
                class="shrink-0 px-4 py-2.5 bg-teal-600 text-white text-sm font-medium rounded-lg hover:bg-teal-700 disabled:opacity-50 disabled:cursor-not-allowed"
                disabled>
            <i class="fas fa-upload mr-2"></i> Subir
        </button>
    </div>
    <div id="process-drive-file-list" class="mt-3 space-y-2 hidden"></div>
    <div id="process-drive-upload-feedback" class="hidden mt-3 p-3 rounded-lg text-sm" role="alert"></div>
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
    if (window.__processDriveUploadBound) {
        return;
    }
    window.__processDriveUploadBound = true;

    var input = document.getElementById('process-document-files');
    var list = document.getElementById('process-drive-file-list');
    var submitBtn = document.getElementById('process-drive-upload-submit');
    var form = document.getElementById('process-drive-upload-form');
    var dropzone = document.getElementById('process-drive-dropzone');
    var feedback = document.getElementById('process-drive-upload-feedback');
    var maxBytes = 10 * 1024 * 1024;
    var defaultSubmitHtml = '<i class="fas fa-upload mr-2"></i> Subir';
    var selectedFiles = [];

    if (!input || !list || !submitBtn || !form || !dropzone) {
        return;
    }

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) {
            return meta.getAttribute('content') || '';
        }
        var tokenInput = form.querySelector('input[name="_token"]');
        return tokenInput ? tokenInput.value : '';
    }

    function showUploadFeedback(message, type) {
        if (!feedback) {
            alert(message);
            return;
        }
        feedback.textContent = message;
        feedback.classList.remove('hidden', 'bg-green-50', 'text-green-800', 'border-green-200', 'bg-red-50', 'text-red-800', 'border-red-200', 'border');
        if (type === 'success') {
            feedback.classList.add('bg-green-50', 'text-green-800', 'border', 'border-green-200');
        } else {
            feedback.classList.add('bg-red-50', 'text-red-800', 'border', 'border-red-200');
        }
        feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function clearUploadFeedback() {
        if (!feedback) {
            return;
        }
        feedback.textContent = '';
        feedback.classList.add('hidden');
    }

    function syncSubmitState() {
        submitBtn.disabled = selectedFiles.length === 0;
    }

    function setSelectedFiles(files) {
        selectedFiles = Array.from(files || []);
        renderFileList();
    }

    function renderFileList() {
        list.innerHTML = '';
        if (selectedFiles.length === 0) {
            list.classList.add('hidden');
            syncSubmitState();
            return;
        }
        list.classList.remove('hidden');
        selectedFiles.forEach(function (file, index) {
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
                selectedFiles = selectedFiles.filter(function (_, i) {
                    return i !== idx;
                });
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

    dropzone.addEventListener('click', function () {
        input.click();
    });
    dropzone.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            input.click();
        }
    });

    input.addEventListener('change', function () {
        if (input.files && input.files.length) {
            setSelectedFiles(input.files);
        }
        input.value = '';
    });

    ['dragenter', 'dragover'].forEach(function (ev) {
        dropzone.addEventListener(ev, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('border-teal-500', 'bg-teal-50');
        });
    });
    dropzone.addEventListener('dragleave', function (e) {
        e.preventDefault();
        dropzone.classList.remove('border-teal-500', 'bg-teal-50');
    });
    dropzone.addEventListener('drop', function (e) {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.remove('border-teal-500', 'bg-teal-50');
        if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
            setSelectedFiles(e.dataTransfer.files);
        }
    });

    function uploadFiles() {
        if (!selectedFiles.length) {
            showUploadFeedback('Seleccione al menos un archivo para subir.', 'error');
            return;
        }

        var oversized = selectedFiles.filter(function (file) {
            return file.size > maxBytes;
        });
        if (oversized.length) {
            showUploadFeedback('Cada archivo debe pesar máximo 10 MB: ' + oversized.map(function (f) { return f.name; }).join(', '), 'error');
            return;
        }

        clearUploadFeedback();
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Subiendo…';

        Promise.all(selectedFiles.map(readFileAsBase64))
            .then(function (contents) {
                var formData = new FormData();
                formData.append('_token', csrfToken());
                formData.append('upload_via', 'encoded');

                selectedFiles.forEach(function (file, index) {
                    formData.append('documents_payload[' + index + '][name]', file.name);
                    formData.append('documents_payload[' + index + '][mime]', file.type || 'application/octet-stream');
                    formData.append('documents_payload[' + index + '][content]', contents[index]);
                });

                return fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                    credentials: 'same-origin',
                });
            })
            .then(function (response) {
                return response.text().then(function (text) {
                    var data = null;
                    try {
                        data = text ? JSON.parse(text) : null;
                    } catch (parseError) {
                        data = null;
                    }

                    if (!data) {
                        if (response.status === 419) {
                            throw new Error('La sesión expiró. Recargue la página e intente de nuevo.');
                        }
                        if (response.status === 413) {
                            throw new Error('El archivo es demasiado grande para el servidor. Pida aumentar post_max_size a 48M.');
                        }
                        throw new Error('El servidor respondió de forma inesperada (código ' + response.status + ').');
                    }

                    if (!response.ok || !data.ok) {
                        throw new Error(data.message || 'No se pudo subir el archivo.');
                    }

                    return data;
                });
            })
            .then(function (data) {
                showUploadFeedback(data.message, 'success');
                selectedFiles = [];
                renderFileList();
                window.setTimeout(function () {
                    window.location.href = data.redirect || window.location.href;
                }, 900);
            })
            .catch(function (error) {
                var message = (error && error.message) ? error.message : 'No se pudo subir el archivo. Intente de nuevo.';
                showUploadFeedback(message, 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = defaultSubmitHtml;
                syncSubmitState();
            });
    }

    submitBtn.addEventListener('click', uploadFiles);

    try {
        var storedFlash = sessionStorage.getItem('driveUploadFlash');
        if (storedFlash) {
            sessionStorage.removeItem('driveUploadFlash');
            var flashData = JSON.parse(storedFlash);
            if (flashData && flashData.message) {
                showUploadFeedback(flashData.message, flashData.type === 'success' ? 'success' : 'error');
            }
        }
    } catch (storageError) {
        // ignore
    }
})();
</script>
@endpush
