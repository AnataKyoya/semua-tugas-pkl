<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        .config-editor {
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.4;
        }

        .result-panel {
            max-height: 600px;
            overflow-y: auto;
        }

        .file-item:hover {
            background-color: #f8f9fa;
        }

        .template-card {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }

        .template-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .template-card.active {
            border-color: #0d6efd;
            background-color: #f0f7ff;
        }

        .preview-area {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }

        .group-badge {
            font-size: 0.75em;
        }
    </style>
</head>

<body>


    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-code-branch me-2"></i><?= $page_title ?>
            </h1>
            <div>
                <button class="btn btn-outline-secondary" onclick="refreshFileList()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>

        <div class="row">
            <!-- Left Panel: File Selection & Configuration -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-file-upload me-2"></i>File Selection</h5>
                    </div>
                    <div class="card-body">
                        <!-- Upload Section -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Upload HTML File</label>
                            <div class="input-group">
                                <input type="file" class="form-control" id="htmlUpload" accept=".html,.htm">
                                <button class="btn btn-success" type="button" id="uploadBtn">
                                    <i class="fas fa-upload"></i> Upload
                                </button>
                            </div>
                            <div class="progress mt-2 d-none" id="uploadProgress" style="height: 6px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                    style="width: 0%"></div>
                            </div>
                        </div>

                        <!-- File List -->
                        <div class="mb-4">
                            <label class="form-label fw-bold d-flex justify-content-between">
                                <span>Available Files</span>
                                <span class="badge bg-secondary" id="fileCount"><?= count($files) ?> files</span>
                            </label>
                            <div class="border rounded" style="max-height: 250px; overflow-y: auto;">
                                <div class="list-group list-group-flush" id="fileList">
                                    <?php if (empty($files)): ?>
                                        <div class="list-group-item text-center text-muted py-4">
                                            <i class="fas fa-folder-open fa-2x mb-2"></i><br>
                                            No HTML files found. Upload one to get started.
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($files as $index => $file): ?>
                                            <div class="list-group-item file-item py-2 <?= $index === 0 ? 'active' : '' ?>"
                                                data-file="<?= $file['name'] ?>">
                                                <div class="form-check d-flex align-items-center">
                                                    <input class="form-check-input me-2 file-radio" type="radio"
                                                        name="selectedFile" id="file_<?= md5($file['name']) ?>"
                                                        <?= $index === 0 ? 'checked' : '' ?> value="<?= $file['name'] ?>">
                                                    <label class="form-check-label flex-grow-1"
                                                        for="file_<?= md5($file['name']) ?>">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="fw-semibold"><?= $file['name'] ?></span>
                                                            <small class="text-muted"><?= $file['size_formatted'] ?></small>
                                                        </div>
                                                        <small class="text-muted d-block">
                                                            <i class="far fa-clock me-1"></i><?= $file['modified'] ?>
                                                        </small>
                                                    </label>
                                                </div>
                                                <div class="mt-2">
                                                    <button class="btn btn-sm btn-outline-info preview-btn"
                                                        data-file="<?= $file['name'] ?>">
                                                        <i class="fas fa-eye"></i> Preview
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger float-end delete-btn"
                                                        data-file="<?= $file['name'] ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuration Templates -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Configuration Templates</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3" id="templateContainer">
                            <!-- Templates will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Configuration & Results -->
            <div class="col-lg-6">
                <!-- Configuration Editor -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Parser Configuration</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Configuration JSON</label>
                            <textarea class="form-control config-editor" id="configJson"
                                rows="12"><?= json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></textarea>
                            <div class="form-text">
                                <strong>Syntax:</strong>
                                <code class="text-success">["start", "end"]</code> for range grouping,
                                <code class="text-success">5</code> for numeric grouping,
                                <code class="text-success">:group</code> to combine elements,
                                <code class="text-success">:text(n)</code> to extract nth text part.
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-outline-primary" id="validateJson">
                                <i class="fas fa-check-circle"></i> Validate JSON
                            </button>
                            <button class="btn btn-primary flex-grow-1" id="runTest">
                                <i class="fas fa-play-circle"></i> Run Test
                            </button>
                            <button class="btn btn-outline-secondary" id="resetConfig">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Results Panel -->
                <div class="card" id="resultsCard" style="display: none;">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Test Results</h5>
                        <div>
                            <button class="btn btn-sm btn-light me-2" id="exportJson">
                                <i class="fas fa-file-export"></i> JSON
                            </button>
                            <button class="btn btn-sm btn-light" id="exportCsv">
                                <i class="fas fa-file-csv"></i> CSV
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Statistics -->
                        <div class="row mb-4" id="statsRow">
                            <!-- Stats will be loaded here -->
                        </div>

                        <!-- Results Display -->
                        <div class="mb-3">
                            <h6>Parsed Data</h6>
                            <div class="result-panel border rounded p-3 bg-light" id="resultContainer">
                                <!-- Results will be displayed here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-code me-2"></i>HTML Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="viewMode" id="viewRaw" autocomplete="off"
                                checked>
                            <label class="btn btn-outline-primary" for="viewRaw">Raw HTML</label>

                            <input type="radio" class="btn-check" name="viewMode" id="viewFormatted" autocomplete="off">
                            <label class="btn btn-outline-primary" for="viewFormatted">Formatted</label>

                            <input type="radio" class="btn-check" name="viewMode" id="viewInfo" autocomplete="off">
                            <label class="btn btn-outline-primary" for="viewInfo">File Info</label>
                        </div>
                    </div>
                    <div id="previewContent" class="preview-area p-3" style="max-height: 500px; overflow-y: auto;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 id="loadingMessage">Processing...</h5>
                    <p class="text-muted mb-0">Please wait</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Load Bootstrap JS FIRST -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Load SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize
            loadTemplates();
            setupEventListeners();

            // Load first file details if exists
            const firstFile = document.querySelector('.file-radio:checked');
            if (firstFile) {
                loadFileDetails(firstFile.value);
            }
        });

        function setupEventListeners() {
            // Upload file
            document.getElementById('uploadBtn').addEventListener('click', uploadFile);
            document.getElementById('htmlUpload').addEventListener('change', handleFileSelect);

            // File selection
            document.querySelectorAll('.file-radio').forEach(radio => {
                radio.addEventListener('change', function() {
                    document.querySelectorAll('.file-item').forEach(item => {
                        item.classList.remove('active');
                    });
                    this.closest('.file-item').classList.add('active');
                    loadFileDetails(this.value);
                });
            });

            // Preview buttons
            document.querySelectorAll('.preview-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const filename = this.dataset.file;
                    previewFile(filename);
                });
            });

            // Delete buttons
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const filename = this.dataset.file;
                    deleteFile(filename);
                });
            });

            // Configuration buttons
            document.getElementById('validateJson').addEventListener('click', validateJson);
            document.getElementById('runTest').addEventListener('click', runTest);
            document.getElementById('resetConfig').addEventListener('click', resetConfig);

            // Export buttons
            document.getElementById('exportJson').addEventListener('click', () => exportResults('json'));
            document.getElementById('exportCsv').addEventListener('click', () => exportResults('csv'));

            // View mode switcher
            document.querySelectorAll('input[name="viewMode"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    updatePreviewView();
                });
            });
        }

        // Load configuration templates
        function loadTemplates() {
            showLoading('Loading templates...');

            fetch('<?= site_url('test-group/templates') ?>')
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    const container = document.getElementById('templateContainer');
                    container.innerHTML = '';

                    Object.entries(data).forEach(([key, template]) => {
                        const col = document.createElement('div');
                        col.className = 'col-md-6';
                        col.innerHTML = `
                    <div class="card template-card h-100" 
                         data-config='${JSON.stringify(template.config)}'
                         onclick="selectTemplate(this, '${key}')">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-clipboard-list me-2"></i>${template.name}
                            </h6>
                            <p class="card-text small text-muted">${template.description}</p>
                            <div class="mt-2">
                                ${getGroupBadge(template.config.group)}
                            </div>
                        </div>
                    </div>
                `;
                        container.appendChild(col);
                    });

                    hideLoading();
                })
                .catch(error => {
                    console.error('Error loading templates:', error);
                    hideLoading();
                    showError('Failed to load templates');
                });
        }

        function getGroupBadge(groupConfig) {
            if (Array.isArray(groupConfig)) {
                return `<span class="badge bg-primary group-badge">Range: ${groupConfig[0]} → ${groupConfig[1]}</span>`;
            } else if (typeof groupConfig === 'number') {
                return `<span class="badge bg-success group-badge">Numeric: ${groupConfig} items</span>`;
            } else {
                return `<span class="badge bg-secondary group-badge">No Grouping</span>`;
            }
        }

        function selectTemplate(card, templateKey) {
            // Remove active class from all cards
            document.querySelectorAll('.template-card').forEach(c => {
                c.classList.remove('active');
            });

            // Add active class to selected card
            card.classList.add('active');

            // Update configuration editor
            const config = JSON.parse(card.dataset.config);
            document.getElementById('configJson').value = JSON.stringify(config, null, 2);

            // Highlight syntax
            highlightJson();
        }

        function uploadFile() {
            const fileInput = document.getElementById('htmlUpload');
            const file = fileInput.files[0];

            if (!file) {
                showError('Please select a file first');
                return;
            }

            // Validate file type
            const validExtensions = ['.html', '.htm'];
            const fileName = file.name.toLowerCase();
            const isValid = validExtensions.some(ext => fileName.endsWith(ext));

            if (!isValid) {
                showError('Only HTML files are allowed (.html, .htm)');
                return;
            }

            const formData = new FormData();
            formData.append('html_file', file);

            const progressBar = document.getElementById('uploadProgress');
            progressBar.classList.remove('d-none');

            showLoading('Uploading file...');

            fetch('<?= site_url('test-group/upload') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Upload failed');
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        showSuccess('File uploaded successfully');
                        refreshFileList();
                        // Select the newly uploaded file
                        setTimeout(() => {
                            const newFileRadio = document.querySelector(`input[value="${data.filename}"]`);
                            if (newFileRadio) {
                                newFileRadio.checked = true;
                                newFileRadio.dispatchEvent(new Event('change'));
                            }
                        }, 500);
                    } else {
                        showError(data.message || 'Upload failed');
                    }
                })
                .catch(error => {
                    console.error('Upload error:', error);
                    showError('Upload failed: ' + error.message);
                })
                .finally(() => {
                    progressBar.classList.add('d-none');
                    hideLoading();
                    fileInput.value = ''; // Reset file input
                });
        }

        function handleFileSelect(e) {
            const file = e.target.files[0];
            if (file) {
                document.getElementById('uploadBtn').disabled = false;
            }
        }

        function refreshFileList() {
            showLoading('Refreshing file list...');

            fetch('<?= site_url('test-group/file-list') ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        updateFileList(data.files);
                        document.getElementById('fileCount').textContent = data.count + ' files';
                    }
                })
                .catch(error => {
                    console.error('Error refreshing file list:', error);
                    showError('Failed to refresh file list');
                })
                .finally(() => {
                    hideLoading();
                });
        }

        function updateFileList(files) {
            const fileList = document.getElementById('fileList');

            if (files.length === 0) {
                fileList.innerHTML = `
            <div class="list-group-item text-center text-muted py-4">
                <i class="fas fa-folder-open fa-2x mb-2"></i><br>
                No HTML files found. Upload one to get started.
            </div>
        `;
                return;
            }

            let html = '';
            files.forEach((file, index) => {
                html += `
            <div class="list-group-item file-item py-2 ${index === 0 ? 'active' : ''}" 
                 data-file="${file.name}">
                <div class="form-check d-flex align-items-center">
                    <input class="form-check-input me-2 file-radio" 
                           type="radio" name="selectedFile" 
                           id="file_${btoa(file.name)}"
                           ${index === 0 ? 'checked' : ''}
                           value="${file.name}">
                    <label class="form-check-label flex-grow-1" 
                           for="file_${btoa(file.name)}">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold">${file.name}</span>
                            <small class="text-muted">${file.size_formatted || formatBytes(file.size)}</small>
                        </div>
                        <small class="text-muted d-block">
                            <i class="far fa-clock me-1"></i>${file.modified}
                        </small>
                    </label>
                </div>
                <div class="mt-2">
                    <button class="btn btn-sm btn-outline-info preview-btn" 
                            data-file="${file.name}">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                    <button class="btn btn-sm btn-outline-danger float-end delete-btn" 
                            data-file="${file.name}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
            });

            fileList.innerHTML = html;

            // Reattach event listeners
            document.querySelectorAll('.file-radio').forEach(radio => {
                radio.addEventListener('change', function() {
                    document.querySelectorAll('.file-item').forEach(item => {
                        item.classList.remove('active');
                    });
                    this.closest('.file-item').classList.add('active');
                    loadFileDetails(this.value);
                });
            });

            document.querySelectorAll('.preview-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const filename = this.dataset.file;
                    previewFile(filename);
                });
            });

            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const filename = this.dataset.file;
                    deleteFile(filename);
                });
            });
        }

        function loadFileDetails(filename) {
            // Could load additional file details here if needed
            console.log('Selected file:', filename);
        }

        function previewFile(filename) {
            showLoading('Loading file preview...');

            fetch(`<?= site_url('test-group/preview') ?>/${encodeURIComponent(filename)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const modal = new bootstrap.Modal(document.getElementById('previewModal'));
                        window.previewData = data;
                        updatePreviewView();
                        modal.show();
                    } else {
                        showError(data.message || 'Preview failed');
                    }
                })
                .catch(error => {
                    console.error('Preview error:', error);
                    showError('Failed to preview file');
                })
                .finally(() => {
                    hideLoading();
                });
        }

        function updatePreviewView() {
            if (!window.previewData) return;

            const contentDiv = document.getElementById('previewContent');
            const viewMode = document.querySelector('input[name="viewMode"]:checked').id;

            switch (viewMode) {
                case 'viewRaw':
                    contentDiv.innerHTML =
                        `<pre class="mb-0" style="white-space: pre-wrap;">${escapeHtml(window.previewData.content)}</pre>`;
                    break;

                case 'viewFormatted':
                    contentDiv.innerHTML = `<div class="formatted-html">${window.previewData.content}</div>`;
                    break;

                case 'viewInfo':
                    const info = window.previewData.file_info;
                    contentDiv.innerHTML = `
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <strong>Filename:</strong> ${info.name}
                    </div>
                    <div class="list-group-item">
                        <strong>Size:</strong> ${formatBytes(info.size)}
                    </div>
                    <div class="list-group-item">
                        <strong>Last Modified:</strong> ${info.modified}
                    </div>
                    <div class="list-group-item">
                        <strong>Path:</strong> <small class="text-muted">${info.path}</small>
                    </div>
                </div>
            `;
                    break;
            }
        }

        function deleteFile(filename) {
            if (!confirm(`Are you sure you want to delete "${filename}"?`)) {
                return;
            }

            showLoading('Deleting file...');

            fetch(`<?= site_url('test-group/deleteFile') ?>/${encodeURIComponent(filename)}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showSuccess('File deleted successfully');
                        refreshFileList();
                    } else {
                        showError(data.message || 'Delete failed');
                    }
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    showError('Failed to delete file');
                })
                .finally(() => {
                    hideLoading();
                });
        }

        function validateJson() {
            const configText = document.getElementById('configJson').value;

            try {
                const config = JSON.parse(configText);
                showSuccess('JSON configuration is valid!');
                highlightJson();
            } catch (error) {
                showError('Invalid JSON: ' + error.message);
                highlightJsonError(error);
            }
        }

        function highlightJson() {
            const textarea = document.getElementById('configJson');
            const json = textarea.value;

            try {
                const parsed = JSON.parse(json);
                const highlighted = syntaxHighlight(JSON.stringify(parsed, null, 2));

                // Create a temporary div to show highlighted version
                const preview = document.createElement('div');
                preview.innerHTML = highlighted;
                preview.style.cssText = textarea.style.cssText;
                preview.className = 'config-editor';
                preview.style.height = textarea.clientHeight + 'px';
                preview.style.overflow = 'auto';
                preview.style.whiteSpace = 'pre';
                preview.style.fontFamily = 'monospace';
                preview.style.padding = '0.375rem 0.75rem';
                preview.style.borderRadius = '0.375rem';
                preview.style.backgroundColor = '#f8f9fa';
                preview.style.border = '1px solid #ced4da';

                // Replace textarea with highlighted version temporarily
                textarea.parentNode.insertBefore(preview, textarea);
                textarea.style.display = 'none';

                // Return to textarea after 3 seconds
                setTimeout(() => {
                    preview.parentNode.removeChild(preview);
                    textarea.style.display = 'block';
                    textarea.focus();
                }, 3000);

            } catch (e) {
                // If JSON is invalid, don't highlight
            }
        }

        function syntaxHighlight(json) {
            json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return json.replace(
                /("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g,
                function(match) {
                    let cls = 'text-dark';
                    if (/^"/.test(match)) {
                        if (/:$/.test(match)) {
                            cls = 'text-primary fw-bold';
                        } else {
                            cls = 'text-success';
                        }
                    } else if (/true|false/.test(match)) {
                        cls = 'text-warning';
                    } else if (/null/.test(match)) {
                        cls = 'text-danger';
                    } else if (/^\d/.test(match)) {
                        cls = 'text-info';
                    }
                    return '<span class="' + cls + '">' + match + '</span>';
                });
        }

        function highlightJsonError(error) {
            const textarea = document.getElementById('configJson');
            const lines = textarea.value.split('\n');

            // Simple error highlighting - could be improved
            textarea.style.borderColor = '#dc3545';
            textarea.style.boxShadow = '0 0 0 0.25rem rgba(220, 53, 69, 0.25)';

            setTimeout(() => {
                textarea.style.borderColor = '';
                textarea.style.boxShadow = '';
            }, 3000);
        }

        function runTest() {
            const configText = document.getElementById('configJson').value;
            const fileRadio = document.querySelector('input[name="selectedFile"]:checked');

            if (!fileRadio) {
                showError('Please select an HTML file first');
                return;
            }

            // Validate JSON first
            try {
                JSON.parse(configText);
            } catch (error) {
                showError('Invalid JSON configuration: ' + error.message);
                return;
            }

            const formData = new FormData();
            formData.append('config', configText);
            formData.append('html_file', fileRadio.value);

            showLoading('Processing HTML file...', true);

            fetch('<?= site_url('test-group/process') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    hideLoading();

                    if (data.status === 'success') {
                        displayResults(data);
                    } else {
                        showError(data.message || 'Test failed');
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Test error:', error);
                    showError('Test failed: ' + error.message);
                })
        }

        function displayResults(data) {
            const resultsCard = document.getElementById('resultsCard');
            const statsRow = document.getElementById('statsRow');
            const resultContainer = document.getElementById('resultContainer');

            // Show results card
            resultsCard.style.display = 'block';

            // Display statistics
            statsRow.innerHTML = `
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h2 class="text-primary">${data.stats.total_records}</h2>
                    <small class="text-muted">Records</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h6 class="text-success">${data.stats.file_size > 1024 ? (data.stats.file_size / 1024).toFixed(1) + ' KB' : data.stats.file_size + ' B'}</h6>
                    <small class="text-muted">File Size</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-info">
                <div class="card-body">
                    <small class="text-muted d-block">File:</small>
                    <small class="text-truncate d-block">${data.stats.file_name}</small>
                    <small class="text-muted d-block mt-1">Path:</small>
                    <small class="text-truncate d-block">${data.stats.file_path}</small>
                </div>
            </div>
        </div>
    `;

            // Display results
            if (data.data.length === 0) {
                resultContainer.innerHTML = `
            <div class="alert alert-warning text-center py-4">
                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                <h5>No Data Found</h5>
                <p class="mb-0">The parser returned no results with this configuration.</p>
            </div>
        `;
                window.testResults = [];
                return;
            }

            let html = `<div class="accordion" id="resultsAccordion">`;

            data.data.slice(0, 10).forEach((record, index) => {
                const accordionId = `record_${index}`;
                html += `
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button ${index > 0 ? 'collapsed' : ''}" 
                            type="button" data-bs-toggle="collapse" 
                            data-bs-target="#${accordionId}">
                        <strong>Record ${index + 1}</strong>
                        <span class="badge bg-secondary ms-2">${Object.keys(record).length} fields</span>
                    </button>
                </h2>
                <div id="${accordionId}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}" 
                     data-bs-parent="#resultsAccordion">
                    <div class="accordion-body">
                        <pre class="mb-0 small" style="max-height: 200px; overflow-y: auto;">${JSON.stringify(record, null, 2)}</pre>
                    </div>
                </div>
            </div>
        `;
            });

            html += `</div>`;

            if (data.data.length > 10) {
                html += `
            <div class="alert alert-info mt-3 text-center">
                <i class="fas fa-info-circle me-2"></i>
                Showing 10 of ${data.data.length} records
                <button class="btn btn-sm btn-outline-info ms-2" onclick="showAllRecords(${JSON.stringify(data.data)})">
                    Show All
                </button>
            </div>
        `;
            }

            resultContainer.innerHTML = html;

            // Store results for export
            window.testResults = data.data;

            // Scroll to results
            resultsCard.scrollIntoView({
                behavior: 'smooth'
            });

            // Initialize accordion
            new bootstrap.Collapse(document.querySelector('.accordion-collapse.show'));
        }

        function showAllRecords(allData) {
            const resultContainer = document.getElementById('resultContainer');
            let html = `<div class="table-responsive"><table class="table table-sm table-bordered">`;

            // Create header from first record
            const headers = Object.keys(allData[0]);
            html += `<thead><tr>`;
            html += `<th>#</th>`;
            headers.forEach(header => {
                html += `<th>${header}</th>`;
            });
            html += `</tr></thead><tbody>`;

            // Create rows
            allData.forEach((record, index) => {
                html += `<tr>`;
                html += `<td class="text-center fw-bold">${index + 1}</td>`;
                headers.forEach(header => {
                    const value = record[header] || '';
                    html += `<td title="${escapeHtml(value)}">${truncateText(value, 50)}</td>`;
                });
                html += `</tr>`;
            });

            html += `</tbody></table></div>`;

            resultContainer.innerHTML = html;
        }

        function exportResults(format) {
            if (!window.testResults || window.testResults.length === 0) {
                showError('No results to export');
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `<?= site_url('test-group/export') ?>/${format}`;
            form.target = '_blank';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'results';
            input.value = JSON.stringify(window.testResults);

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        function resetConfig() {
            if (confirm('Reset configuration to default?')) {
                const defaultConfig = <?= json_encode($config) ?>;
                document.getElementById('configJson').value = JSON.stringify(defaultConfig, null, 2);

                // Remove active template
                document.querySelectorAll('.template-card').forEach(card => {
                    card.classList.remove('active');
                });

                showSuccess('Configuration reset to default');
            }
        }

        // Utility functions
        function showLoading(message = 'Loading...', blocking = false) {
            const modalElement = document.getElementById('loadingModal');
            const messageElement = document.getElementById('loadingMessage');

            messageElement.textContent = message;

            if (blocking) {
                const modal = new bootstrap.Modal(modalElement, {
                    backdrop: 'static',
                    keyboard: false
                });
                modal.show();
            } else {
                // Show non-blocking loading indicator
                // You could implement a toast or small spinner here
            }
        }

        function hideLoading() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('loadingModal'));
            if (modal) {
                modal.hide();
            }
        }

        function showSuccess(message) {
            // Using Bootstrap toast or alert
            const toast = document.createElement('div');
            toast.className = 'position-fixed top-0 end-0 p-3';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
        <div class="toast show" role="alert">
            <div class="toast-header bg-success text-white">
                <strong class="me-auto"><i class="fas fa-check-circle"></i> Success</strong>
                <button type="button" class="btn-close btn-close-white" onclick="this.closest('.toast').remove()"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
            document.body.appendChild(toast);

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 3000);
        }

        function showError(message) {
            const toast = document.createElement('div');
            toast.className = 'position-fixed top-0 end-0 p-3';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
        <div class="toast show" role="alert">
            <div class="toast-header bg-danger text-white">
                <strong class="me-auto"><i class="fas fa-exclamation-circle"></i> Error</strong>
                <button type="button" class="btn-close btn-close-white" onclick="this.closest('.toast').remove()"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
            document.body.appendChild(toast);

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 5000);
        }

        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';

            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];

            const i = Math.floor(Math.log(bytes) / Math.log(k));

            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function truncateText(text, maxLength) {
            if (text.length <= maxLength) return escapeHtml(text);
            return escapeHtml(text.substring(0, maxLength)) + '...';
        }
    </script>

</body>

</html>