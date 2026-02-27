<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTML Stage Manager</title>
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script src="https://unpkg.com/alpinejs" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        .progress-transition {
            transition: width 0.5s ease-in-out;
        }

        /* Animasi halus untuk floating button */
        .fab-enter {
            transform: translateY(100px);
            opacity: 0;
        }

        .fab-enter-active {
            transition: all 0.3s ease-out;
        }

        .json-editor {
            font-family: monospace;
            line-height: 1.5;
        }

        .json-error {
            border-color: #ef4444 !important;
            background-color: #fff5f5;
        }

        .json-valid {
            border-color: #22c55e !important;
            background-color: #f0fdf4;
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen p-6 pb-32 text-slate-800">

    <div class="max-w-5xl mx-auto" x-data="htmlFileManager()">
        <header class="mb-8 flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-blue-800">HTML Stage Manager</h1>
                <p class="text-slate-500 text-sm">Upload file .html dan proses secara berurutan.</p>
            </div>

            <a href="<?= base_url('scraper/file/panduan') ?>" target="_blank"
                class="flex items-center space-x-2 bg-white border border-slate-200 px-4 py-2 rounded-xl shadow-sm hover:bg-blue-50 hover:border-blue-200 transition-all font-bold text-sm text-slate-600 group">
                <svg class="w-5 h-5 text-blue-600 group-hover:scale-110 transition-transform" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Buka Panduan Analisa</span>
            </a>
        </header>

        <div class="border-2 border-dashed border-blue-200 rounded-2xl p-10 text-center bg-white hover:border-blue-400 transition-all cursor-pointer mb-8 shadow-sm"
            @click="$refs.fileInput.click()">
            <input type="file" x-ref="fileInput" class="hidden" multiple accept=".html,text/html"
                @change="handleFileUpload">
            <div class="text-blue-500 mb-3">
                <svg class="w-10 h-10 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                    </path>
                </svg>
            </div>
            <p class="font-semibold text-slate-700">Pilih atau Drag file HTML ke sini</p>
        </div>

        <template x-for="(file, fIndex) in files" :key="file.id">
            <div class="bg-white rounded-xl shadow-md border border-slate-200 mb-8 overflow-hidden transition-all"
                :class="file.status === 'error' ? 'border-red-300' : (file.status === 'success' ? 'border-green-300' : '')">

                <div class="bg-slate-800 px-6 py-4 flex justify-between items-center text-white">
                    <div class="flex items-center space-x-3 flex-1">
                        <span class="bg-orange-500 px-2 py-0.5 rounded text-[10px] font-black italic">HTML</span>
                        <div class="flex-1">
                            <input type="text" x-model="file.customName"
                                class="bg-transparent border-b border-slate-600 focus:border-blue-400 outline-none font-medium truncate w-full"
                                placeholder="Nama Dokumen">

                            <div x-show="file.status === 'processing' && file.progress < 100"
                                class="w-full bg-slate-700 h-1 rounded-full mt-4 overflow-hidden">
                                <div class="h-full progress-transition bg-gradient-to-r from-blue-600 via-blue-400 to-cyan-300 shadow-[0_0_8px_rgba(59,130,246,0.4)]"
                                    :style="`width: ${file.progress}%`" x-cloak>
                                </div>
                            </div>

                            <div x-show="file.status === 'success'" class="mt-3">
                                <p class="text-[10px] text-green-400 font-bold italic tracking-widest animate-pulse">
                                    ✓ SELESAI DIPROSES
                                </p>
                            </div>

                            <div x-show="file.status === 'error'" class="mt-3">
                                <p class="text-[10px] text-red-400 font-bold" x-text="'ERROR: ' + file.errorMessage">
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button x-show="file.status === 'success'" @click="downloadCsv(file)"
                            class="flex items-center bg-green-600 hover:bg-green-700 text-white text-[10px] font-bold py-1.5 px-3 rounded-lg transition shadow-lg">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            DOWNLOAD CSV
                        </button>

                        <button @click="removeFile(fIndex)" class="text-slate-400 hover:text-red-400 ml-2"
                            :disabled="file.status === 'processing'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-5" x-show="file.status !== 'success'">
                    <div class="flex justify-between items-center mb-5">
                        <h3 class="font-bold text-slate-700 italic">CONFIGURASI STAGES</h3>
                        <button @click="addStage(fIndex)"
                            class="text-xs bg-blue-600 text-white font-bold px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                            + TAMBAH STAGE
                        </button>
                    </div>

                    <div x-show="file.stages.length === 0"
                        class="py-10 text-center border border-dashed border-slate-200 rounded-xl text-slate-400 text-sm">
                        Belum ada stage. Klik "+ Tambah Stage".
                    </div>

                    <div class="grid gap-4">
                        <template x-for="(stage, sIndex) in file.stages" :key="sIndex">
                            <div class="border border-slate-100 rounded-xl p-5 bg-slate-50 relative">
                                <button @click="duplicateStage(fIndex, sIndex)"
                                    class="text-slate-400 hover:text-blue-600 font-bold text-sm">
                                    ⧉
                                </button>

                                <button @click="removeStage(fIndex, sIndex)"
                                    class="absolute top-4 right-4 text-slate-400 hover:text-red-500 font-bold">&times;</button>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 mt-2">
                                    <div>
                                        <label
                                            class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Type</label>
                                        <select x-model="stage.type"
                                            class="w-full mt-1 border border-slate-200 rounded-lg p-2 text-sm bg-white outline-none">
                                            <option value="list">List</option>
                                            <option value="detail">Detail</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label
                                            class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Parent
                                            Selector</label>
                                        <input type="text" x-model="stage.parent"
                                            class="w-full mt-1 border border-slate-200 rounded-lg p-2 text-sm outline-none"
                                            placeholder=".item-wrapper atau tr">
                                        <p class="text-[9px] text-blue-600 font-semibold mt-1 mb-0.5">Multiple parent:
                                            pisahkan dengan koma ','</p>
                                    </div>
                                </div>

                                <!-- GROUP CONFIG SECTION -->
                                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center space-x-2">
                                            <span
                                                class="text-[10px] font-black text-blue-600 uppercase tracking-widest">GROUP
                                                CONFIG</span>
                                            <span
                                                class="text-[9px] bg-blue-200 text-blue-800 px-2 py-0.5 rounded-full font-bold">OPTIONAL</span>
                                        </div>
                                        <button @click="toggleGroupHelp(fIndex, sIndex)"
                                            class="text-blue-500 hover:text-blue-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Help Text -->
                                    <div x-show="stage.showGroupHelp" x-collapse
                                        class="mb-3 p-3 bg-white rounded border border-blue-200 text-xs text-slate-600">
                                        <p class="font-semibold mb-1">Untuk mengelompokkan elemen flat sebelum
                                            parsing:</p>
                                        <ul class="list-disc list-inside space-y-1 text-[11px]">
                                            <li><strong>Numeric:</strong> Kelompokkan setiap N elemen (misal: 3)
                                            </li>
                                            <li><strong>Selector Range:</strong> Dari selector start sampai end
                                                (misal: div.h22 → div.h21)</li>
                                        </ul>
                                    </div>

                                    <div class="grid grid-cols-3 gap-2 mb-3">
                                        <label class="flex items-center space-x-2 cursor-pointer">
                                            <input type="radio" :name="'groupMode_' + fIndex + '_' + sIndex"
                                                value="none" x-model="stage.groupMode"
                                                class="text-blue-600 focus:ring-blue-500">
                                            <span class="text-xs text-slate-600">None</span>
                                        </label>
                                        <label class="flex items-center space-x-2 cursor-pointer">
                                            <input type="radio" :name="'groupMode_' + fIndex + '_' + sIndex"
                                                value="numeric" x-model="stage.groupMode"
                                                class="text-blue-600 focus:ring-blue-500">
                                            <span class="text-xs text-slate-600">Numeric</span>
                                        </label>
                                        <label class="flex items-center space-x-2 cursor-pointer">
                                            <input type="radio" :name="'groupMode_' + fIndex + '_' + sIndex"
                                                value="range" x-model="stage.groupMode"
                                                class="text-blue-600 focus:ring-blue-500">
                                            <span class="text-xs text-slate-600">Selector Range</span>
                                        </label>
                                    </div>

                                    <!-- Numeric Input -->
                                    <div x-show="stage.groupMode === 'numeric'" x-collapse>
                                        <label class="text-[10px] font-bold text-slate-500 mb-1 block">Group Size
                                            (angka)</label>
                                        <input type="number" x-model.number="stage.groupNumeric" min="1"
                                            class="w-full border border-blue-200 rounded-lg p-2 text-sm outline-none focus:border-blue-400"
                                            placeholder="Misal: 3 (kelompokkan setiap 3 elemen)">
                                    </div>

                                    <!-- Selector Range Inputs -->
                                    <div x-show="stage.groupMode === 'range'" x-collapse class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="text-[10px] font-bold text-slate-500 mb-1 block">Start
                                                Selector</label>
                                            <input type="text" x-model="stage.groupStart"
                                                class="w-full border border-blue-200 rounded-lg p-2 text-sm outline-none focus:border-blue-400"
                                                placeholder="div.t.h22">
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-bold text-slate-500 mb-1 block">End
                                                Selector</label>
                                            <input type="text" x-model="stage.groupEnd"
                                                class="w-full border border-blue-200 rounded-lg p-2 text-sm outline-none focus:border-blue-400"
                                                placeholder="div.t.h21">
                                        </div>
                                    </div>
                                </div>
                                <!-- END GROUP CONFIG -->

                                <div class="bg-white rounded-lg border border-slate-200 p-4">
                                    <div class="flex justify-between items-center mb-3">

                                        <span
                                            class="text-[10px] font-black text-slate-400 tracking-widest">FIELDS</span>

                                        <div class="flex items-center gap-2">

                                            <!-- MODE SWITCH -->
                                            <div
                                                class="inline-flex rounded-lg border overflow-hidden text-xs font-bold">
                                                <button
                                                    @click="stage.fieldMode = 'selector'; convertRawFieldsToRows(stage)"
                                                    :class="stage.fieldMode === 'selector' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600'"
                                                    class="px-3 py-1 transition">
                                                    Selector
                                                </button>

                                                <button @click="stage.fieldMode = 'raw'; convertRowsToRawFields(stage)"
                                                    :class="stage.fieldMode === 'raw' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600'"
                                                    class="px-3 py-1 transition">
                                                    RAW JSON
                                                </button>
                                            </div>

                                            <!-- ADD FIELD -->
                                            <button x-show="stage.fieldMode === 'selector'"
                                                @click="addField(fIndex, sIndex)"
                                                class="text-xs font-bold text-blue-600 hover:underline">
                                                + ADD FIELD
                                            </button>

                                        </div>
                                    </div>

                                    <!--<template x-for="(field, fiIndex) in stage.fields" :key="fiIndex">-->
                                    <!-- <div class="border border-slate-200 rounded-lg p-3 mb-2 bg-white space-y-2"> -->

                                    <!-- NORMAL MODE -->
                                    <div x-show="stage.fieldMode === 'selector'"
                                        class="border rounded-lg overflow-hidden">

                                        <div class="grid grid-cols-12 text-[10px] font-bold bg-slate-100 p-2">
                                            <div class="col-span-4">NAME</div>
                                            <div class="col-span-7">SELECTOR</div>
                                            <div class="col-span-1 text-right">DEL</div>
                                        </div>

                                        <template x-for="(field, fiIndex) in stage.fields" :key="fiIndex">
                                            <div class="flex gap-2 mb-2">
                                                <input type="text" x-model="field.name" placeholder="Name"
                                                    class="flex-1 border border-slate-100 rounded p-2 text-xs outline-none">
                                                <input type="text" x-model="field.selector" placeholder="Selector"
                                                    class="flex-1 border border-slate-100 rounded p-2 text-xs outline-none">
                                                <button @click="removeField(fIndex, sIndex, fiIndex)"
                                                    class="text-slate-300 hover:text-red-500 px-1">&times;</button>
                                            </div>
                                        </template>

                                    </div>

                                    <!-- RAW MODE -->
                                    <div x-show="stage.fieldMode === 'raw'" class="space-y-2">

                                        <div class="flex border rounded overflow-hidden text-xs">

                                            <!-- LINE NUMBERS -->
                                            <pre class="bg-slate-100 text-slate-500 px-2 py-2 text-right select-none"
                                                x-text="stage.rawFieldsJson.split('\n').map((_, i) => i+1).join('\n')"></pre>

                                            <!-- JSON EDITOR -->
                                            <textarea x-model="stage.rawFieldsJson" @input="validateFieldsJson(stage)"
                                                rows="10" spellcheck="false" class="flex-1 p-2 outline-none font-mono"
                                                :class="stage.rawFieldsJson ? (stage.isValidFieldsJson ? 'json-valid' : 'json-error') : ''"
                                                placeholder='{"title": ".title", "price": ".price"}'></textarea>

                                        </div>

                                        <div class="text-[11px] font-semibold">
                                            <span x-show="stage.fieldsJsonError" class="text-red-600"
                                                x-text="stage.fieldsJsonError"></span>
                                            <span x-show="stage.isValidFieldsJson" class="text-green-600">✓ VALID
                                                FIELDS JSON</span>
                                        </div>

                                    </div>

                                    <!-- </div> -->
                                    <!--</template>-->
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        <div x-show="files.length > 0" x-transition:enter="fab-enter-active" x-transition:enter-start="fab-enter"
            class="fixed bottom-8 right-8 z-50">
            <button @click="submitConfigs" :disabled="isUploading"
                class="flex items-center bg-blue-600 hover:bg-blue-700 disabled:bg-slate-400 text-white px-8 py-4 rounded-full font-bold shadow-2xl transition-all transform hover:scale-105 active:scale-95 group">

                <svg x-show="isUploading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>

                <svg x-show="!isUploading" class="w-5 h-5 mr-2 group-hover:translate-x-1 transition-transform"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3">
                    </path>
                </svg>

                <span x-text="isUploading ? 'SEDANG MEMPROSES...' : 'MULAI PROSES SEMUA FILE'"></span>
            </button>
        </div>

        <div x-show="showWarning"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-cloak
            x-transition>
            <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center">
                <div
                    class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4 text-red-600">
                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2" x-text="warningTitle">Warning</h3>
                <p class="text-sm text-gray-500 mb-6" x-text="warningMessage"></p>
                <button @click="showWarning = false"
                    class="w-full bg-slate-800 hover:bg-black text-white font-bold py-3 rounded-xl transition">Mengerti</button>
            </div>
        </div>
    </div>

    <script>
        function htmlFileManager() {
            return {
                files: [],
                showWarning: false,
                warningTitle: '',
                warningMessage: '',
                isUploading: false,

                handleFileUpload(event) {
                    const uploaded = Array.from(event.target.files);
                    uploaded.forEach(file => {
                        if (file.name.toLowerCase().endsWith('.html')) {
                            this.files.push({
                                id: 'id-' + Math.random().toString(36).substr(2, 9),
                                fileBlob: file,
                                originalName: file.name,
                                customName: file.name,
                                stages: [],
                                status: 'idle',
                                progress: 0,
                                errorMessage: '',
                                csvLink: null
                            });
                        }
                    });
                    event.target.value = '';
                },

                addStage(fIndex) {
                    this.files[fIndex].stages.push({
                        type: 'list',
                        parent: '',
                        groupMode: 'none',
                        groupNumeric: null,
                        groupStart: '',
                        groupEnd: '',
                        showGroupHelp: false,

                        fieldMode: 'selector', // selector | raw

                        rawFieldsJson: '',
                        fieldsJsonError: '',
                        isValidFieldsJson: false,

                        fields: [{
                            name: '',
                            selector: ''
                        }]
                    });
                },

                duplicateStage(fIndex, sIndex) {
                    const original = this.files[fIndex].stages[sIndex];

                    // Deep clone agar tidak share reference
                    const clonedStage = JSON.parse(JSON.stringify(original));

                    // Optional: reset help toggle
                    clonedStage.showGroupHelp = false;

                    // Insert duplicated stage right after original
                    this.files[fIndex].stages.splice(sIndex + 1, 0, clonedStage);
                },


                removeStage(fIndex, sIndex) {
                    this.files[fIndex].stages.splice(sIndex, 1);
                },

                addField(fIndex, sIndex) {
                    this.files[fIndex].stages[sIndex].fields.push({
                        name: '',
                        selector: '',
                    });
                },

                removeField(fIndex, sIndex, fiIndex) {
                    this.files[fIndex].stages[sIndex].fields.splice(fiIndex, 1);
                },

                convertRowsToRawFields(stage) {
                    const obj = {};
                    stage.fields.forEach(f => {
                        if (f.name && f.selector) obj[f.name] = f.selector;
                    });

                    stage.rawFieldsJson = JSON.stringify(obj, null, 2);
                    stage.isValidFieldsJson = true;
                    stage.fieldsJsonError = '';
                },

                convertRawFieldsToRows(stage) {
                    if (!stage.rawFieldsJson.trim()) return;
                    if (!stage.isValidFieldsJson) return; // jangan paksa parse

                    try {
                        const parsed = JSON.parse(stage.rawFieldsJson);

                        stage.fields = Object.entries(parsed).map(([k, v]) => ({
                            name: k,
                            selector: v
                        }));

                        stage.fieldsJsonError = '';
                    } catch {
                        // ignore
                    }
                },

                validateFieldsJson(stage) {

                    stage.fieldsJsonError = '';
                    stage.isValidFieldsJson = false;

                    // kosong → tidak error
                    if (!stage.rawFieldsJson.trim()) return;

                    let parsed;

                    // 1. Try parse JSON
                    try {
                        parsed = JSON.parse(stage.rawFieldsJson);
                    } catch (err) {
                        // Jangan format — biarkan user bebas ngetik
                        const match = err.message.match(/position (\d+)/);
                        if (match) {
                            const pos = parseInt(match[1]);
                            const line = stage.rawFieldsJson.slice(0, pos).split('\n').length;
                            stage.fieldsJsonError = `Syntax error on line ${line}`;
                        } else {
                            stage.fieldsJsonError = "Syntax error";
                        }
                        return;
                    }

                    // 2. Must be object only
                    if (typeof parsed !== 'object' || Array.isArray(parsed)) {
                        stage.fieldsJsonError = `"fields" must be an object`;
                        return;
                    }

                    // 3. Validate values must be string selector
                    for (let key in parsed) {
                        if (typeof parsed[key] !== 'string') {
                            const line = this.findLineForKey(stage.rawFieldsJson, key);
                            stage.fieldsJsonError = `Error on line ${line}: "${key}" must be string selector`;
                            return;
                        }
                    }

                    // 4. JSON valid → NOW format
                    stage.rawFieldsJson = JSON.stringify(parsed, null, 2);

                    // 5. Mark valid
                    stage.isValidFieldsJson = true;
                },

                findLineForKey(jsonText, key) {
                    const lines = jsonText.split('\n');
                    const index = lines.findIndex(line => line.includes(`"${key}"`));
                    return index >= 0 ? index + 1 : '?';
                },

                removeFile(index) {
                    this.files.splice(index, 1);
                },

                toggleGroupHelp(fIndex, sIndex) {
                    this.files[fIndex].stages[sIndex].showGroupHelp = !this.files[fIndex].stages[sIndex].showGroupHelp;
                },

                downloadCsv(file) {
                    if (!file.csvLink) return alert("Link download tidak tersedia.");
                    const link = document.createElement('a');
                    link.href = file.csvLink;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                },

                async submitConfigs() {
                    if (this.files.length === 0) return;

                    this.isUploading = true;

                    for (let i = 0; i < this.files.length; i++) {
                        let fileItem = this.files[i];
                        if (fileItem.status === 'success') continue;

                        try {
                            fileItem.status = 'processing';
                            fileItem.progress = 20;

                            const stagesData = fileItem.stages.map(s => {
                                const mappedFields = {};

                                if (s.fieldMode === 'selector') {
                                    s.fields.forEach(f => {
                                        if (f.name) mappedFields[f.name] = f.selector;
                                    });
                                }

                                if (s.fieldMode === 'raw') {
                                    if (!s.isValidFieldsJson) {
                                        throw new Error("FIELDS RAW JSON invalid — fix errors");
                                    }

                                    Object.assign(mappedFields, JSON.parse(s.rawFieldsJson));
                                }




                                // Process parent selector - split by comma if multiple
                                let parentValue = s.parent.trim();
                                if (parentValue.includes(',')) {
                                    // Multiple parents: split, trim, filter empty
                                    parentValue = parentValue
                                        .split(',')
                                        .map(p => p.trim())
                                        .filter(p => p !== '');
                                }

                                const stageConfig = {
                                    'name': s.type,
                                    'parent': parentValue,
                                    'fields': mappedFields
                                };

                                // Add group config if specified
                                if (s.groupMode === 'numeric' && s.groupNumeric) {
                                    stageConfig.group = parseInt(s.groupNumeric);
                                } else if (s.groupMode === 'range' && s.groupStart && s.groupEnd) {
                                    stageConfig.group = [s.groupStart, s.groupEnd];
                                }

                                return stageConfig;
                            });

                            const configObject = [{
                                customName: fileItem.customName,
                                stages: stagesData
                            }];

                            const jsonString = JSON.stringify(configObject);
                            // Langkah 2: Encode ke Base64 (btoa tidak suka karakter unicode, pakai unescape dulu)
                            const base64Config = btoa(unescape(encodeURIComponent(jsonString)));

                            const formData = new FormData();
                            formData.append('data', base64Config);
                            formData.append('html_files[]', fileItem.fileBlob);

                            const response = await fetch('/scraper/file/set', {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            const text = await response.text();
                            let result = JSON.parse(text);

                            if (result.status === 'success' || response.ok) {
                                fileItem.status = 'success';
                                fileItem.progress = 100;
                                fileItem.csvLink = result.link[0] || null;
                            } else {
                                throw new Error(result.message || "Gagal memproses");
                            }

                        } catch (error) {
                            fileItem.status = 'error';
                            fileItem.errorMessage = error.message;
                            fileItem.progress = 0;
                        }
                    }
                    this.isUploading = false;
                }
            }
        }
    </script>
</body>

</html>