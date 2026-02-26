<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParseAI - SaaS Analysis</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fira+Code:wght@400;500&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .terminal-bg {
            background-color: #0f172a;
        }

        .terminal-text {
            font-family: 'Fira Code', monospace;
        }

        .typing-cursor::after {
            content: '|';
            animation: blink 1s infinite;
        }

        @keyframes blink {
            50% {
                opacity: 0;
            }
        }

        .sidebar-item-active {
            background: #f1f5f9;
            color: #2563eb;
            border-right: 3px solid #2563eb;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 10px;
        }

        @keyframes shimmer {
            0% {
                opacity: 0.5;
            }

            50% {
                opacity: 1;
            }

            100% {
                opacity: 0.5;
            }
        }

        .skeleton {
            opacity: 1;
            animation: shimmer 1.5s infinite;
            border-radius: 4px;
        }
    </style>
</head>

<body class="bg-[#f8fafc] h-screen overflow-hidden flex">

    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col shrink-0 hidden md:flex">
        <div class="p-6 border-b border-slate-100 flex items-center gap-2">
            <div class="bg-indigo-600 p-1.5 rounded-lg text-white">
                <i data-lucide="terminal" class="w-5 h-5"></i>
            </div>
            <span class="text-xl font-bold tracking-tight">Parse<span class="text-indigo-600">AI</span></span>
        </div>

        <nav class="flex-1 p-4 space-y-2 mt-4">
            <a href="#"
                class="sidebar-item-active flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-semibold transition">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Analysis Desk
            </a>
            <!-- <a href="#"
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-slate-500 hover:bg-slate-50 transition">
                <i data-lucide="folder-kanban" class="w-4 h-4"></i> Projects
            </a>
            <a href="#"
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-slate-500 hover:bg-slate-50 transition">
                <i data-lucide="history" class="w-4 h-4"></i> History
            </a>
            <a href="#"
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-slate-500 hover:bg-slate-50 transition">
                <i data-lucide="settings" class="w-4 h-4"></i> Settings
            </a> -->
        </nav>

        <!-- <div class="p-4 border-t border-slate-100">
            <div class="bg-slate-900 rounded-xl p-4 text-white">
                <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">Tokens Used</p>
                <div class="w-full bg-slate-700 h-1.5 rounded-full mb-2">
                    <div class="bg-indigo-500 h-1.5 rounded-full w-3/4"></div>
                </div>
                <p class="text-[10px]">750 / 1000 Premium</p>
            </div>
        </div> -->
    </aside>

    <main class="flex-1 flex flex-col min-w-0">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 shrink-0">
            <h2 class="text-sm font-semibold text-slate-600">System / <span class="text-slate-900">Analyzer</span></h2>
            <div class="flex items-center gap-4">
                <button class="p-2 text-slate-400 hover:text-indigo-600"><i data-lucide="bell"
                        class="w-5 h-5"></i></button>
                <div
                    class="h-8 w-8 rounded-full bg-indigo-100 border border-indigo-200 flex items-center justify-center text-indigo-600 font-bold text-xs">
                    JD</div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 grid grid-cols-12 gap-8 custom-scrollbar">

            <div class="col-span-12 lg:col-span-4 space-y-6">
                <div id="drop-zone"
                    class="bg-white border-2 border-dashed border-slate-200 rounded-[2rem] p-8 text-center hover:border-indigo-400 transition-all cursor-pointer group shadow-sm">
                    <input type="file" id="file-input" class="hidden" accept=".pdf">
                    <div
                        class="bg-indigo-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="upload-cloud" class="text-indigo-600 w-8 h-8"></i>
                    </div>
                    <p class="text-sm font-bold text-slate-700">Drop your PDF here</p>
                    <p class="text-xs text-slate-400 mt-2 italic">Max file size: 25MB</p>
                </div>

                <div id="file-info" class="hidden bg-white p-5 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="bg-indigo-100 p-2 rounded-lg text-indigo-600"><i data-lucide="file"
                                class="w-5 h-5"></i></div>
                        <div class="overflow-hidden">
                            <p id="file-name" class="text-xs font-bold truncate"></p>
                            <p class="text-[10px] text-slate-400 uppercase tracking-widest">Ready to analyze</p>
                        </div>
                    </div>
                    <button id="extract-btn"
                        class="w-full bg-slate-900 text-white py-3 rounded-xl text-xs font-bold hover:bg-indigo-600 transition flex items-center justify-center gap-2">
                        <i data-lucide="play" class="w-3 h-3 fill-current"></i> EXECUTE ANALYSIS
                    </button>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-8">
                <div
                    class="bg-[#0f172a] rounded-2xl shadow-2xl overflow-hidden h-[400px] flex flex-col border border-slate-800">

                    <div class="bg-[#1e293b] px-4 py-3 flex items-center justify-between border-b border-slate-800">
                        <div class="flex gap-2">
                            <div class="w-3 h-3 rounded-full bg-[#ff5f56]"></div>
                            <div class="w-3 h-3 rounded-full bg-[#ffbd2e]"></div>
                            <div class="w-3 h-3 rounded-full bg-[#27c93f]"></div>
                        </div>
                        <div class="flex items-center gap-2 text-slate-400">
                            <i data-lucide="terminal" class="w-3 h-3"></i>
                            <span class="text-[10px] font-mono tracking-widest uppercase">Analysis_Output_v2</span>
                        </div>
                        <div class="w-10"></div>
                    </div>

                    <div class="terminalEl flex-1 p-8 overflow-y-auto custom-scrollbar font-mono">

                        <div id="placeholder-text" class="h-full flex flex-col items-center justify-center opacity-20">
                            <i data-lucide="command" class="w-12 h-12 text-slate-400 mb-4"></i>
                            <p class="text-slate-400 text-sm italic">Waiting for execution command...</p>
                        </div>

                        <div id="state" class="hidden space-y-8 text-wrap mb-4">

                        </div>

                        <div id="analysis-result" class="hidden space-y-8 text-wrap">

                            <section>
                                <div class="flex items-center gap-2 text-indigo-400 mb-3">
                                    <span class="text-xs">●</span>
                                    <h4 class="text-xs font-bold uppercase tracking-widest">
                                        Executive_Summary
                                    </h4>
                                </div>

                                <div class="pl-5 border-l border-slate-800 space-y-4">

                                    <!-- Structured output -->
                                    <div id="res-summary" class="text-slate-300 text-sm leading-relaxed space-y-6">
                                    </div>

                                    <!-- Typing preview -->
                                    <div id="typing-wrapper">
                                        <p id="typing-preview"
                                            class="text-slate-400 text-sm leading-relaxed whitespace-pre-wrap"></p>
                                        <span id="cursor"
                                            class="inline-block w-2 bg-indigo-400 animate-pulse ml-1"></span>
                                    </div>
                                </div>
                            </section>

                            <!-- <section>
                                <div class="flex items-center gap-2 text-emerald-400 mb-3">
                                    <span class="text-xs">●</span>
                                    <h4 class="text-xs font-bold uppercase tracking-widest">Extracted_Entities</h4>
                                </div>
                                <div id="res-entities" class="pl-5 flex flex-wrap gap-2">
                                </div>
                            </section> -->

                            <!-- <section>
                                <div class="flex items-center gap-2 text-rose-500 mb-3">
                                    <span class="text-xs">●</span>
                                    <h4 class="text-xs font-bold uppercase tracking-widest">Risk_Assessment</h4>
                                </div>
                                <div id="res-risks" class="pl-5 space-y-2">
                                </div>
                            </section>

                            <section>
                                <div class="flex items-center gap-2 text-emerald-500 mb-3">
                                    <span class="text-xs">●</span>
                                    <h4 class="text-xs font-bold uppercase tracking-widest">Suggestion</h4>
                                </div>
                                <div id="res-suggest" class="pl-5 space-y-2">
                                </div>
                            </section> -->

                            <div
                                class="pt-6 mt-6 border-t border-slate-800 text-[10px] text-slate-500 flex justify-between">
                                <span>STATUS: SUCCESSFUL_ANALYSIS</span>
                                <span>TIMESTAMP: 2026-02-18_13:13</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script>
        lucide.createIcons();
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

        const fileInput = document.getElementById('file-input');
        const dropZone = document.getElementById('drop-zone');
        const extractBtn = document.getElementById('extract-btn');
        const terminalBody = document.getElementById('terminal-body');
        const loading = document.getElementById('loading');
        const resultView = document.getElementById('analysis-result');

        dropZone.onclick = () => fileInput.click();

        fileInput.onchange = (e) => {
            const file = e.target.files[0];
            if (file) {
                document.getElementById('file-info').classList.remove('hidden');
                document.getElementById('file-name').textContent = file.name;
            }
        };

        // Logika untuk menampilkan data secara "Normal" (langsung muncul)
        function displayAnalysisNormal(data) {
            // Hide placeholder, show result
            document.getElementById('placeholder-text').classList.add('hidden');

            setTimeout(() => {
                const extractPDF = document.getElementById('state');
                const resultArea = document.getElementById('analysis-result');

                resultArea.classList.remove('hidden');
                extractPDF.classList.add('hidden');

                // 1. Render Summary
                document.getElementById('res-summary').textContent = data.summary;

                // 2. Render Entities (Statis)
                // const entityContainer = document.getElementById('res-entities');
                // entityContainer.innerHTML = data.entities.map(item =>
                //     `<span class="bg-slate-800 text-emerald-400 border border-emerald-900/50 px-3 py-1 rounded text-[11px]">${item}</span>`
                // ).join('');

                // 3. Render Risks (Statis)
                const riskContainer = document.getElementById('res-risks');
                riskContainer.innerHTML = data.risks.map(risk =>
                    `<div class="flex items-start gap-3 text-slate-400 text-sm">
            <span class="text-rose-500">>></span>
            <span>${risk}</span>
            </div>`
                ).join('');

                // 4. Render Suggest (Statis)
                const suggestContainer = document.getElementById('res-suggest');
                suggestContainer.innerHTML = data.suggestion.map(item =>
                    `<div class="flex items-start gap-3 text-slate-400 text-sm">
            <span class="text-emerald-500">>></span>
            <span>${item}</span>
            </div>`
                ).join('');
            }, 1000)
        }

        function displayBMCAnalysis(bmcData) {
            document.getElementById('placeholder-text').classList.add('hidden');

            const extractPDF = document.getElementById('state');
            const resultArea = document.getElementById('analysis-result');

            resultArea.classList.remove('hidden');
            extractPDF.classList.add('hidden');


            let html = `<div class="flex flex-col gap-3">`;

            // Kesimpulan
            html += '<ul>';
            bmcData.kesimpulan_dan_rekomendasi_prioritas.forEach(item => {
                html += `
        <li class="flex items-start gap-3 text-sm">
            <span class="text-indigo-400">>></span>
            <span>${item}</span>
        </li>`;
            });
            html += '</ul>';

            // Blok-blok BMC
            bmcData.business_model_canvas_analysis.forEach(blok => {

                html += `<div>`; // FIXED: sekarang benar

                html += `<h4 class="text-2xl font-bold tracking-wide">[${blok.blok}]</h4>`;

                // Data
                html += '<p><strong>Data:</strong></p>';
                html += '<ul>';
                blok.data.forEach(d => {
                    html += `
            <li class="flex items-start gap-3 text-sm">
                <span class="text-indigo-400">>></span>
                <span>${d}</span>
            </li>`;
                });
                html += '</ul>';

                // Analisis
                html += '<p class="text-rose-400"><strong>Analisis Nyata:</strong></p>';
                html += '<ul>';
                blok.analisis_nyata.forEach(a => {
                    html += `<li>${a}</li>`;
                });
                html += '</ul>';

                // Saran
                html += '<p class="text-emerald-400"><strong>Saran Konkret:</strong></p>';
                html += '<ul>';
                blok.saran_konkret.forEach(s => {
                    html += `<li>${s}</li>`;
                });
                html += '</ul>';

                html += `</div>`;
            });

            html += `</div>`;

            document.getElementById('res-summary').innerHTML = html; // misal pakai elemen summary
        }

        let typingQueue = [];
        let isTyping = false;

        const typingArea = document.getElementById('typing-preview');
        const terminalEl = document.querySelector('.terminalEl');

        function autoScroll() {
            if (terminalEl) terminalEl.scrollTop = terminalEl.scrollHeight;
        }

        function enqueueText(text) {
            for (const char of text) {
                typingQueue.push(char);
            }
            if (!isTyping) processQueue();
        }

        function processQueue() {
            if (typingQueue.length === 0) {
                isTyping = false;
                return;
            }

            isTyping = true;
            const char = typingQueue.shift();
            typingArea.textContent += char;
            autoScroll();

            setTimeout(processQueue, 10); // delay per karakter, sesuaikan
        }

        async function streamBMC(rawText) {
            const API_ENDPOINT = "<?= base_url() ?>bmc/stream";

            document.getElementById('placeholder-text').classList.add('hidden');
            document.getElementById('analysis-result').classList.remove('hidden');

            const typingWrapper = document.getElementById('typing-wrapper');
            const root = document.getElementById('res-summary');

            typingWrapper.classList.remove('hidden');
            typingArea.textContent = '';
            root.innerHTML = '';

            try {
                const response = await fetch(API_ENDPOINT, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        text: rawText
                    })
                });

                if (!response.ok) throw new Error("Server error: " + response.status);

                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let lineBuffer = '';

                while (true) {
                    const {
                        value,
                        done
                    } = await reader.read();
                    if (done) break;

                    lineBuffer += decoder.decode(value, {
                        stream: true
                    });
                    const lines = lineBuffer.split('\n');
                    lineBuffer = lines.pop(); // simpan baris belum lengkap

                    for (const line of lines) {

                        if (line.startsWith('RAW:')) {

                            // Filter noise delimiter agar tidak muncul di typing area
                            const raw = line.slice(4);
                            if (!raw.includes('###')) {
                                enqueueText(raw);
                            }

                        } else if (line.startsWith('JSON:')) {
                            try {
                                const obj = JSON.parse(line.slice(5));

                                if (obj.type !== 'done') {
                                    // Flush queue langsung, kosongkan typing area
                                    typingQueue = [];
                                    isTyping = false;
                                    typingArea.textContent = '';

                                    renderStructured(obj, root);
                                    autoScroll();
                                }

                            } catch (e) {
                                console.warn('Skipped malformed JSON:', e);
                            }

                        } else if (line === 'DONE') {
                            typingQueue = [];
                            isTyping = false;

                            // Sembunyikan typing area, semua sudah terrender
                            typingWrapper.classList.add('hidden');
                            document.getElementById('state').classList.add('hidden');

                            if (terminalEl) terminalEl.scrollTop = 0;
                        }
                    }
                }

            } catch (error) {
                typingWrapper.classList.add('hidden');
                root.innerHTML += `
            <p class="text-rose-500 font-bold mt-4">ERROR: STREAM_FAILED</p>
            <p class="text-slate-500 text-xs mt-1">${error.message}</p>
        `;
            }
        }

        function renderStructured(obj, root) {

            if (obj.type === "kesimpulan") {

                const ul = document.createElement("ul");
                ul.className = "space-y-2";

                obj.data.forEach(item => {
                    const li = document.createElement("li");
                    li.className = "flex items-start gap-3 text-sm opacity-0 transition-opacity duration-500";

                    const icon = document.createElement("span");
                    icon.className = "text-indigo-400";
                    icon.textContent = ">>";

                    const text = document.createElement("span");
                    text.textContent = item;

                    li.appendChild(icon);
                    li.appendChild(text);
                    ul.appendChild(li);

                    setTimeout(() => li.classList.remove("opacity-0"), 50);
                });

                root.appendChild(ul);
            }

            if (obj.type === "blok") {

                const container = document.createElement("div");
                container.className = "space-y-4 opacity-0 transition-all duration-500";

                const title = document.createElement("h4");
                title.className = "text-2xl font-bold tracking-wide";
                title.textContent = `[${obj.blok}]`;
                container.appendChild(title);

                function createSection(label, colorClass, items) {
                    const wrapper = document.createElement("div");

                    const p = document.createElement("p");
                    if (colorClass) p.className = colorClass;

                    const strong = document.createElement("strong");
                    const updatedLabel = label.replace(/<blok_name>/g, obj.blok);
                    strong.textContent = updatedLabel;
                    p.appendChild(strong);

                    const ul = document.createElement("ul");
                    ul.className = "space-y-1";

                    items.forEach(item => {
                        const li = document.createElement("li");
                        li.className = "flex items-start gap-3 text-sm";

                        const icon = document.createElement("span");
                        icon.textContent = ">>";

                        const text = document.createElement("span");
                        text.textContent = item;

                        li.appendChild(icon);
                        li.appendChild(text);
                        ul.appendChild(li);
                    });

                    wrapper.appendChild(p);
                    wrapper.appendChild(ul);

                    return wrapper;
                }

                container.appendChild(
                    createSection("Data:", null, obj.data)
                );

                container.appendChild(
                    createSection("Analisis Blok <blok_name> Anda:", "text-rose-400", obj.analisis_blok)
                );

                container.appendChild(
                    createSection("Saran / Tambahan:", "text-emerald-400", obj.saran)
                );

                root.appendChild(container);

                setTimeout(() => container.classList.remove("opacity-0"), 50);
            }
        }

        // 1. Fungsi Utama Ekstraksi (Tetap di Frontend)
        async function extractPDFText(file) {
            const arrayBuffer = await file.arrayBuffer();
            const pdf = await pdfjsLib.getDocument(arrayBuffer).promise;
            let fullText = "";

            for (let i = 1; i <= pdf.numPages; i++) {
                const page = await pdf.getPage(i);
                const content = await page.getTextContent();
                const strings = content.items.map(item => item.str);
                fullText += strings.join(' ') + "\n";
            }
            return fullText;
        }

        // 2. Fungsi Kirim ke Backend
        async function sendToBackend(rawText) {
            const API_ENDPOINT = "<?= base_url() ?>bmc/process"; // Ganti dengan URL backend-mu

            try {
                const response = await fetch(API_ENDPOINT, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        text: rawText,
                        timestamp: new Date().toISOString()
                    })
                });

                if (!response.ok) throw new Error("Server_Error: " + response.status);

                const data = await response.json();
                return data; // Data ini yang akan di-mapping ke terminal
            } catch (error) {
                console.error("Backend_Failure:", error);
                throw error;
            }
        }

        // 3. Orchestrator (Saat Tombol Diklik)
        document.getElementById('extract-btn').onclick = async () => {
            const file = fileInput.files[0];
            if (!file) return;

            // UI: Tampilkan loading di terminal
            document.getElementById('placeholder-text').classList.add('hidden');
            // document.getElementById('loading').classList.remove('hidden');
            document.getElementById('analysis-result').classList.add('hidden');

            const extractPDF = document.getElementById('state');
            extractPDF.classList.remove('hidden');
            extractPDF.innerHTML = ""

            try {
                // Step A: Ekstrak PDF (Lokal)
                extractPDF.innerHTML +=
                    `<span class="skeleton text-indigo-400 px-3 py-1 rounded text-[11px]">Starting extraction...</span> <br/>`;
                const extractedText = await extractPDFText(file);

                // Step B: Kirim ke AI (Backend)
                extractPDF.innerHTML =
                    `<span class="text-indigo-400 px-3 py-1 rounded text-[11px]">Starting extraction...</span> <br/>`;
                extractPDF.innerHTML +=
                    `<span class="skeleton text-indigo-400 px-3 py-1 rounded text-[11px]">Analyzing, please wait...</span> <br/>`;
                await streamBMC(extractedText);

                // Step C: Tampilkan hasil normal di Terminal
                // document.getElementById('loading').classList.add('hidden');
                // extractPDF.innerHTML =
                //     `<span class="text-indigo-400 px-3 py-1 rounded text-[11px]">Starting extraction...</span> <br/>`;
                // extractPDF.innerHTML +=
                //     `<span class="text-indigo-400 px-3 py-1 rounded text-[11px]">Analyzing, please wait...</span> <br/>`;
                // extractPDF.innerHTML +=
                //     `<span class="text-emerald-400 px-3 py-1 rounded text-[11px]">Complete</span> <br/>`;

                // displayBMCAnalysis(aiResult.res);

            } catch (err) {
                document.getElementById('state').classList.add('hidden');
                // Tampilkan error di terminal
                const resArea = document.getElementById('analysis-result');
                resArea.classList.remove('hidden');
                resArea.innerHTML = `<p class="text-rose-500 font-bold">ERROR_CODE: 500</p>
                             <p class="text-slate-500 text-xs">${err.message}</p>`;
            }
        };

        // Contoh Pemanggilan saat tombol diklik
        // document.getElementById('extract-btn').onclick = () => {
        //     const dummyAI = {
        //         summary: "Analisis mendeteksi dokumen ini sebagai Surat Perjanjian Sewa Guna Usaha (Leasing) dengan fokus pada aset digital.",
        //         entities: ["PT. TEKNOLOGI JAYA", "IDR 2.500.000.000", "CLOUD_INFRASTRUCTURE"],
        //         risks: [
        //             "Terminasi sepihak tanpa pemberitahuan 30 hari.",
        //             "Suku bunga mengambang tidak memiliki batas atas (cap).",
        //             "Yurisdiksi hukum berada di luar wilayah operasional utama."
        //         ]
        //     };

        //     displayAnalysisNormal(dummyAI);
        // };
    </script>
</body>

</html>