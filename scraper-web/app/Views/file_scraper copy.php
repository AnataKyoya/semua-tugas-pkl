<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FileCrawler AI | Enterprise Processing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8fafc;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .dropdown-content {
            display: none;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        /* Custom Scrollbar */
        .custom-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scroll::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .custom-scroll::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }

        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: #cbd5e1;
        }

        /* Error Modal Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideIn {
            from {
                transform: scale(0.95) translateY(-10px);
                opacity: 0;
            }

            to {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
        }

        .modal-backdrop {
            animation: fadeIn 0.2s ease-out;
        }

        .modal-content {
            animation: slideIn 0.3s ease-out;
        }
    </style>
</head>

<body class="min-h-screen text-slate-900 flex flex-col">

    <!-- Error Modal -->
    <div id="errorModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="modal-backdrop absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeErrorModal()"></div>
        <div class="modal-content relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-200">
            <div class="flex items-start gap-4">
                <div id="errorIcon" class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center">
                    <!-- Icon will be inserted here -->
                </div>
                <div class="flex-1">
                    <h3 id="errorTitle" class="font-bold text-slate-800 text-lg mb-2"></h3>
                    <p id="errorMessage" class="text-sm text-slate-600 leading-relaxed"></p>
                </div>
            </div>
            <div class="mt-6 flex gap-3 justify-end">
                <button onclick="closeErrorModal()"
                    class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">
                    Dismiss
                </button>
                <button onclick="closeErrorModal()"
                    class="px-4 py-2 text-sm font-bold bg-indigo-600 text-white hover:bg-indigo-700 rounded-lg transition-colors">
                    OK
                </button>
            </div>
        </div>
    </div>

    <nav class="border-b bg-white sticky top-0 z-50 px-6 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-indigo-600 rounded-lg flex items-center justify-center shadow-lg">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                    </path>
                </svg>
            </div>
            <span class="font-bold text-xl tracking-tight text-slate-800">FileCrawler<span
                    class="text-indigo-600">.ai</span></span>
        </div>
        <div class="flex items-center gap-4">
            <div class="hidden sm:flex flex-col items-end leading-none">
                <span class="text-xs font-bold text-slate-800">Enterprise User</span>
                <span class="text-[10px] text-indigo-500 font-medium">PRO PLAN</span>
            </div>
            <div
                class="h-10 w-10 rounded-full bg-indigo-100 border border-indigo-200 flex items-center justify-center text-indigo-700 font-bold">
                JD</div>
        </div>
    </nav>

    <div class="flex flex-col lg:flex-row flex-1 overflow-hidden">

        <aside
            class="w-full lg:w-[380px] lg:h-[calc(100vh-72px)] border-b lg:border-b-0 lg:border-r bg-white p-5 lg:p-6 overflow-y-auto space-y-6 flex-shrink-0">

            <div class="space-y-4">
                <div>
                    <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Prompt</h2>
                    <textarea id="promptInput" rows="3"
                        class="w-full text-sm rounded-xl border-slate-200 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-400 placeholder:text-slate-400 p-3 lg:p-4 transition-all resize-none shadow-sm"
                        placeholder="e.g. 'Summarize legal risks...'"></textarea>
                </div>

                <button id="processBtn"
                    class="w-full bg-slate-900 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-lg flex items-center justify-center gap-3 group">
                    <span class="text-sm">Execute AI Analysis</span>
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </button>
            </div>

            <hr class="border-slate-100">

            <div class="space-y-4">
                <div id="dropZone"
                    class="group border-2 border-dashed border-slate-200 rounded-xl p-3 transition-all hover:border-indigo-500 hover:bg-indigo-50/50 cursor-pointer text-center">
                    <input type="file" id="fileInput" multiple class="hidden">
                    <div class="flex items-center justify-center gap-3">
                        <div
                            class="w-8 h-8 bg-slate-50 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                        <div class="text-left">
                            <p class="text-xs font-bold text-slate-700 leading-tight">Click or drag files (MAX 15MB)</p>
                            <p class="text-[10px] text-slate-400">PDF, DOCX, TXT</p>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2 px-1">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Queue</span>
                        <span id="fileCount"
                            class="text-[10px] font-bold bg-indigo-50 px-2 py-0.5 rounded text-indigo-600 border border-indigo-100/50">0
                            Files</span>
                    </div>

                    <div class="max-h-[180px] lg:max-h-[none] overflow-y-auto pr-1 custom-scroll">
                        <ul id="fileList" class="grid grid-cols-4 sm:grid-cols-6 lg:grid-cols-4 gap-2">
                            <div id="emptyFileList"
                                class="col-span-full py-6 border-2 border-dotted border-slate-100 rounded-xl bg-slate-50/30 text-center">
                                <p class="text-[10px] text-slate-400">Ready for files</p>
                            </div>
                        </ul>
                    </div>
                </div>
            </div>
        </aside>

        <main class="flex-1 bg-slate-50 relative flex flex-col p-4 lg:p-8 overflow-y-auto">
            <div class="max-w-4xl mx-auto w-full">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-xl lg:text-2xl font-bold text-slate-800">Intelligence Output</h1>
                        <p class="text-xs lg:text-sm text-slate-500">View and export your generated assets</p>
                    </div>
                    <button onclick="clearResults()"
                        class="w-fit text-xs font-bold text-slate-400 hover:text-red-500 flex items-center gap-2 transition-colors border border-slate-200 sm:border-0 p-2 sm:p-0 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                        Flush Results
                    </button>
                </div>

                <div id="resultContainer" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                </div>

                <div id="resultPlaceholder"
                    class="flex flex-col items-center justify-center py-16 lg:py-24 text-center">
                    <div
                        class="w-16 h-16 lg:w-24 lg:h-24 bg-white rounded-2xl lg:rounded-3xl shadow-sm flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 lg:w-10 lg:h-10 text-slate-200" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-slate-500 font-bold text-sm lg:text-base">Ready for Processing</h3>
                    <p class="text-slate-400 text-xs lg:text-sm mt-2 max-w-xs leading-relaxed">Upload documents and
                        provide a goal to start the AI analysis engine.</p>
                </div>
            </div>

            <div id="loadingOverlay"
                class="hidden fixed lg:absolute inset-0 bg-slate-900/20 backdrop-blur-sm z-50 flex items-center justify-center">
                <div
                    class="bg-white p-6 lg:p-8 rounded-3xl shadow-2xl flex flex-col items-center border border-slate-100 mx-4">
                    <div class="relative w-10 h-10 lg:w-12 lg:h-12">
                        <div class="absolute inset-0 border-4 border-indigo-100 rounded-full"></div>
                        <div
                            class="absolute inset-0 border-4 border-indigo-600 rounded-full border-t-transparent animate-spin">
                        </div>
                    </div>
                    <p class="mt-4 font-bold text-slate-800 text-xs lg:text-sm">Analyzing Patterns...</p>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Error Modal Functions
        function showError(title, message, type = 'error') {
            const modal = document.getElementById('errorModal');
            const iconContainer = document.getElementById('errorIcon');
            const titleEl = document.getElementById('errorTitle');
            const messageEl = document.getElementById('errorMessage');

            // Set icon based on type
            const icons = {
                error: {
                    bg: 'bg-red-100',
                    svg: '<svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
                },
                warning: {
                    bg: 'bg-yellow-100',
                    svg: '<svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>'
                },
                info: {
                    bg: 'bg-blue-100',
                    svg: '<svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
                }
            };

            const icon = icons[type] || icons.error;
            iconContainer.className = `flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center ${icon.bg}`;
            iconContainer.innerHTML = icon.svg;
            titleEl.textContent = title;
            messageEl.textContent = message;

            modal.classList.remove('hidden');
        }

        function closeErrorModal() {
            document.getElementById('errorModal').classList.add('hidden');
        }

        // Close modal on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeErrorModal();
        });

        class FileProcessor {
            constructor() {
                this.uploadedFiles = [];
            }

            deleteFile(fileId) {
                fetch('/file-crawler/delete-file/' + fileId, {
                    method: 'DELETE',
                }).catch(err => {
                    console.error('Delete error:', err);
                });
                this.uploadedFiles = this.uploadedFiles.filter(f => f.file_id !== fileId);
                updateFileList();
            }

            async uploadFile(file) {
                // Validasi ukuran file
                const maxSize = 15 * 1024 * 1024; // 15MB
                if (file.size > maxSize) {
                    throw new Error(`File "${file.name}" melebihi batas ukuran maksimal 15MB`);
                }

                // Validasi tipe file
                const allowedExtensions = ['pdf', 'docx', 'txt'];
                const extension = file.name.split('.').pop().toLowerCase();
                if (!allowedExtensions.includes(extension)) {
                    throw new Error(
                        `Tipe file "${extension}" tidak didukung. Hanya PDF, DOCX, dan TXT yang diizinkan.`);
                }

                const formData = new FormData();
                formData.append('file', file);
                try {
                    const response = await fetch('/file-crawler/file-upload', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.uploadedFiles.push(data);
                        return data;
                    } else {
                        throw new Error(data.error || 'Upload gagal');
                    }
                } catch (error) {
                    // Fallback ke mock data untuk demo
                    const mockData = {
                        success: true,
                        file_id: Math.random().toString(36).substr(2, 9),
                        name: file.name,
                        size: file.size,
                        extension: extension
                    };
                    this.uploadedFiles.push(mockData);
                    return mockData;
                }
            }

            async uploadMultipleFiles(files) {
                const results = [];
                const errors = [];

                for (const file of Array.from(files)) {
                    try {
                        const result = await this.uploadFile(file);
                        results.push(result);
                    } catch (error) {
                        errors.push({
                            fileName: file.name,
                            error: error.message
                        });
                    }
                }

                if (errors.length > 0) {
                    const errorMsg = errors.map(e => `${e.error}`).join('\n');
                    showError(
                        'Upload Gagal',
                        `Beberapa file gagal diupload:\n\n${errorMsg}`,
                        'warning'
                    );
                }

                return results;
            }

            async processFiles(prompt) {
                if (this.uploadedFiles.length === 0) {
                    throw new Error('Tidak ada file yang diupload. Silakan upload file terlebih dahulu.');
                }
                if (!prompt || prompt.trim() === '') {
                    throw new Error('Prompt tidak boleh kosong. Silakan masukkan instruksi analisis.');
                }

                const fileIds = this.uploadedFiles.map(f => f.file_id);
                try {
                    const response = await fetch('/file-crawler/process', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            file_ids: fileIds,
                            prompt: prompt
                        })
                    });

                    // if (!response.ok) {
                    //     throw new Error(`Server error: ${response.status} ${response.statusText}`);
                    // }

                    const data = await response.json();
                    if (data.success) {
                        this.uploadedFiles = [];
                        return [data];
                    } else {
                        throw new Error(data.error || 'Proses gagal dilakukan');
                    }
                } catch (error) {
                    // Jika fetch gagal karena network atau server tidak tersedia
                    if (error.message.includes('fetch') || error.message.includes('NetworkError')) {
                        throw new Error(
                            'Tidak dapat terhubung ke server. Pastikan koneksi internet Anda aktif dan server sedang berjalan.'
                        );
                    }
                    throw error;
                }
            }

            getUploadedFiles() {
                return this.uploadedFiles;
            }
        }

        const processor = new FileProcessor();
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');

        dropZone.onclick = () => fileInput.click();
        dropZone.ondragover = (e) => {
            e.preventDefault();
            dropZone.classList.add('bg-indigo-50');
        };
        dropZone.ondragleave = () => dropZone.classList.remove('bg-indigo-50');
        dropZone.ondrop = async (e) => {
            e.preventDefault();
            dropZone.classList.remove('bg-indigo-50');
            handleFiles(e.dataTransfer.files);
        };

        fileInput.onchange = (e) => handleFiles(e.target.files);

        async function handleFiles(files) {
            if (files.length === 0) return;

            try {
                await processor.uploadMultipleFiles(files);
                updateFileList();
            } catch (error) {
                showError('Upload Error', error.message, 'error');
            }
        }

        function updateFileList() {
            const files = processor.getUploadedFiles();
            const listEl = document.getElementById('fileList');
            const emptyState = document.getElementById('emptyFileList');
            const fileCountEl = document.getElementById('fileCount');

            if (fileCountEl) fileCountEl.textContent = `${files.length} Files`;

            if (files.length === 0) {
                if (emptyState) emptyState.style.display = 'block';
                if (listEl) listEl.innerHTML =
                    '<div id="emptyFileList" class="col-span-full py-6 border-2 border-dotted border-slate-100 rounded-xl bg-slate-50/30 text-center"><p class="text-[10px] text-slate-400">Ready for files</p></div>';
                return;
            }

            if (listEl) {
                listEl.innerHTML = files.map(f => `
            <li class="relative group aspect-square bg-white border border-slate-200 rounded-xl flex flex-col items-center justify-center p-2 text-center hover:border-indigo-500 hover:shadow-sm transition-all">
                <button onclick="processor.deleteFile('${f.file_id}')"  
                    class="absolute -top-1 -right-1 bg-white border shadow-sm text-slate-400 hover:text-red-500 rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>

                <div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center text-[9px] font-black text-indigo-600 mb-2">
                    ${f.extension.toUpperCase()}
                </div>

                <p class="text-[10px] font-bold text-slate-700 w-full truncate px-1">${f.name}</p>
                <p class="text-[8px] text-slate-400 font-medium">${formatBytes(f.size)}</p>
            </li>
        `).join('');
            }
        }

        document.getElementById('processBtn').onclick = async () => {
            const overlay = document.getElementById('loadingOverlay');
            const container = document.getElementById('resultContainer');
            const placeholder = document.getElementById('resultPlaceholder');

            try {
                overlay.classList.remove('hidden');
                const results = await processor.processFiles(document.getElementById('promptInput').value);

                placeholder.classList.add('hidden');
                container.classList.remove('hidden');
                container.innerHTML = results.map(res => `
                    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm hover:shadow-md transition-all">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div class="dropdown relative">
                                <button class="p-2 hover:bg-slate-50 rounded-lg transition-colors text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                </button>
                                <div class="dropdown-content absolute right-0 w-48 bg-white border border-slate-100 shadow-xl rounded-xl z-30 p-2">
                                    <button onclick="downloadAs('${res.name}', 'pdf')" class="w-full text-left px-3 py-2 text-xs font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg">Export PDF</button>
                                    <button onclick="downloadAs('${res.name}', 'docx')" class="w-full text-left px-3 py-2 text-xs font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg">Export DOCX</button>
                                    <button onclick="downloadAs('${res.name}', 'xlsx')" class="w-full text-left px-3 py-2 text-xs font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg">Export XLSX</button>
                                    <button onclick="downloadAs('${res.name}', 'json')" class="w-full text-left px-3 py-2 text-xs font-bold text-indigo-500 hover:bg-indigo-50 rounded-lg border-t border-slate-50 mt-1">Raw JSON Data</button>
                                </div>
                            </div>
                        </div>
                        <h4 class="font-bold text-slate-800 text-sm mb-1">${res.name}</h4>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">${res.type}</p>
                    </div>
                `).join('');
                updateFileList();
            } catch (e) {
                showError('Processing Error', e.message, 'error');
            } finally {
                overlay.classList.add('hidden');
            }
        };

        function downloadAs(name, format) {
            try {
                window.location.href = "/file-crawler/downloadas/" + name + "/" + format;
            } catch (error) {
                showError('Download Error', 'Gagal mengunduh file. Silakan coba lagi.', 'error');
            }
        }

        function clearResults() {
            document.getElementById('resultContainer').innerHTML = '';
            document.getElementById('resultContainer').classList.add('hidden');
            document.getElementById('resultPlaceholder').classList.remove('hidden');
        }

        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
    </script>
</body>

</html>