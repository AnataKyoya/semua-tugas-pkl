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
        href="https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
    :root {
        --bg: #0b0f1a;
        --surface: #111827;
        --surface2: #1a2236;
        --border: #1f2d44;
        --accent: #4f8ef7;
        --accent2: #7c3aed;
        --green: #34d399;
        --rose: #f87171;
        --amber: #fbbf24;
        --text: #e2e8f0;
        --muted: #64748b;
    }

    [data-theme="light"] {
        --bg: #f1f5f9;
        --surface: #ffffff;
        --surface2: #f8fafc;
        --border: #e2e8f0;
        --accent: #2563eb;
        --accent2: #7c3aed;
        --green: #059669;
        --rose: #dc2626;
        --amber: #d97706;
        --text: #0f172a;
        --muted: #94a3b8;
    }

    * {
        transition: background 0.2s, color 0.2s, border-color 0.2s;
    }

    .theme-toggle {
        display: flex;
        align-items: center;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 99px;
        padding: 3px;
        gap: 2px;
    }

    .theme-btn {
        width: 28px;
        height: 28px;
        border-radius: 99px;
        border: none;
        background: transparent;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        color: var(--muted);
    }

    .theme-btn.active {
        background: var(--accent);
        color: #fff;
    }

    * {
        box-sizing: border-box;
    }

    body {
        font-family: 'DM Sans', sans-serif;
        background: var(--bg);
        color: var(--text);
        min-height: 100vh;
        overflow-x: hidden;
    }

    .mono {
        font-family: 'Space Mono', monospace;
    }

    /* Scrollbar */
    ::-webkit-scrollbar {
        width: 4px;
        height: 4px;
    }

    ::-webkit-scrollbar-track {
        background: transparent;
    }

    ::-webkit-scrollbar-thumb {
        background: var(--border);
        border-radius: 99px;
    }

    /* Upload zone */
    .drop-zone {
        border: 1.5px dashed var(--border);
        border-radius: 16px;
        transition: border-color 0.2s, background 0.2s;
    }

    .drop-zone:hover,
    .drop-zone.drag-over {
        border-color: var(--accent);
        background: rgba(79, 142, 247, 0.05);
    }

    /* Mode pill */
    .mode-pill {
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 99px;
        cursor: pointer;
        transition: all 0.2s;
        color: var(--muted);
        font-size: 12px;
        padding: 6px 14px;
    }

    .mode-pill.active {
        background: var(--accent);
        border-color: var(--accent);
        color: #fff;
    }

    /* Tab */
    .tab-btn {
        font-family: 'Space Mono', monospace;
        font-size: 10px;
        padding: 8px 14px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
        color: var(--muted);
        white-space: nowrap;
        background: transparent;
    }

    .tab-btn:hover {
        color: var(--text);
        background: var(--surface2);
    }

    .tab-btn.active {
        background: var(--accent);
        color: #fff;
        border-color: var(--accent);
    }

    .tab-btn.kesimpulan-tab {
        border-color: var(--accent2);
        color: var(--accent2);
    }

    .tab-btn.kesimpulan-tab.active {
        background: var(--accent2);
        color: #fff;
    }

    /* Panel */
    .panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
    }

    /* Btn */
    .btn-primary {
        background: var(--accent);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 10px 20px;
        font-family: 'Space Mono', monospace;
        font-size: 11px;
        cursor: pointer;
        transition: opacity 0.2s, transform 0.1s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary:hover {
        opacity: 0.88;
    }

    .btn-primary:active {
        transform: scale(0.97);
    }

    .btn-primary:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    /* Skeleton */
    @keyframes shimmer {

        0%,
        100% {
            opacity: .3
        }

        50% {
            opacity: .7
        }
    }

    .skeleton {
        animation: shimmer 1.5s infinite;
        background: var(--surface2);
        border-radius: 6px;
    }

    /* Fade in */
    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(12px)
        }

        to {
            opacity: 1;
            transform: none
        }
    }

    .fade-up {
        animation: fadeUp 0.4s ease forwards;
    }

    /* Tag */
    .tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-family: 'Space Mono', monospace;
        font-size: 10px;
        padding: 4px 10px;
        border-radius: 6px;
        border: 1px solid;
    }

    /* List item */
    .list-item {
        display: flex;
        align-items: flex-start;
        padding: 8px 0;
        border-bottom: 1px solid var(--border);
        font-size: 13px;
        line-height: 1.6;
        color: var(--text);
    }

    .list-item:last-child {
        border-bottom: none;
    }

    /* Cursor */
    @keyframes blink {
        50% {
            opacity: 0
        }
    }

    .cursor {
        display: inline-block;
        width: 8px;
        height: 14px;
        background: var(--accent);
        border-radius: 2px;
        animation: blink 1s infinite;
        vertical-align: middle;
        margin-left: 3px;
    }

    /* Section header */
    .section-label {
        font-family: 'Space Mono', monospace;
        font-size: 9px;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 10px;
    }

    .tab-scroll {
        overflow-x: auto;
        padding-bottom: 4px;
    }

    .tab-scroll::-webkit-scrollbar {
        height: 0;
    }

    /* Hide native scrollbar on tab-bar, use custom */
    #tab-bar::-webkit-scrollbar {
        height: 0;
    }

    #tab-bar {
        scrollbar-width: none;
    }

    /* Compact progress strip — shown while tabs render */
    .progress-compact {
        flex: none !important;
        padding: 12px 20px !important;
        gap: 8px !important;
        justify-content: center !important;
        border-bottom: 1px solid var(--border);
        flex-direction: row !important;
        align-items: center !important;
    }

    .progress-compact #steps-list,
    .progress-compact #typing-preview,
    .progress-compact .cursor,
    .progress-compact #progress-label {
        display: none !important;
    }

    .progress-compact>div:first-child {
        margin: 0;
    }

    .progress-compact #progress-pct {
        font-size: 11px;
        min-width: 36px;
    }

    .progress-compact>div:nth-child(2) {
        flex: 1;
        gap: 4px !important;
    }
    </style>
</head>

<body>

    <div style="max-width:900px;margin:0 auto;padding:32px 20px 60px;">

        <!-- Header -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:36px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="background:var(--accent);padding:8px;border-radius:10px;display:flex;">
                    <i data-lucide="terminal" style="width:18px;height:18px;color:#fff;"></i>
                </div>
                <span class="mono" style="font-size:18px;font-weight:700;">Parse<span
                        style="color:var(--accent)">AI</span></span>
            </div>
            <div style="display:flex;align-items:center;gap:12px;">
                <!-- Theme toggle -->
                <div class="theme-toggle" id="theme-toggle">
                    <button class="theme-btn active" data-theme="dark" title="Dark">🌙</button>
                    <button class="theme-btn" data-theme="light" title="Light">☀️</button>
                </div>
                <div
                    style="width:32px;height:32px;border-radius:50%;background:var(--surface2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="bell" style="width:14px;height:14px;color:var(--muted);"></i>
                </div>
                <div
                    style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;">
                    JD</div>
            </div>
        </div>

        <!-- Column layout: Upload + Mode + File info | Results -->
        <div style="display:flex;flex-direction:column;gap:20px;">

            <!-- TOP ROW: Upload controls -->
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;align-items:stretch;">

                <!-- Upload Drop Zone -->
                <div class="drop-zone panel" id="drop-zone"
                    style="padding:24px;text-align:center;cursor:pointer;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;">
                    <input type="file" id="file-input" class="hidden" accept=".pdf" style="display:none;">
                    <div
                        style="width:44px;height:44px;background:rgba(79,142,247,0.12);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <i data-lucide="upload-cloud" style="width:22px;height:22px;color:var(--accent);"></i>
                    </div>
                    <div>
                        <p style="font-weight:600;font-size:13px;margin:0 0 2px;">Drop PDF here</p>
                        <p style="font-size:11px;color:var(--muted);margin:0;">Max 25MB</p>
                    </div>
                </div>

                <!-- Mode Selector -->
                <div class="panel" style="padding:20px;">
                    <p class="section-label">Distribution Mode</p>
                    <div style="display:flex;flex-direction:column;gap:8px;" id="mode-group">
                        <button class="mode-pill active" data-mode="lokal">
                            <span>🏠</span> Lokal
                        </button>
                        <button class="mode-pill" data-mode="ekspor">
                            <span>🌍</span> Ekspor
                        </button>
                    </div>
                    <p style="font-size:10px;color:var(--muted);margin-top:12px;line-height:1.6;">Mode mempengaruhi
                        fokus analisis BMC yang dihasilkan AI.</p>
                </div>

                <!-- File Info + Execute -->
                <div class="panel" style="padding:20px;display:flex;flex-direction:column;gap:16px;">
                    <div id="no-file"
                        style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;opacity:0.4;">
                        <i data-lucide="file-x" style="width:28px;height:28px;color:var(--muted);"></i>
                        <p style="font-size:12px;color:var(--muted);margin:0;">No file selected</p>
                    </div>

                    <div id="file-info" style="display:none;flex:1;flex-direction:column;gap:12px;">
                        <div
                            style="display:flex;align-items:center;gap:10px;padding:10px;background:var(--surface2);border-radius:10px;border:1px solid var(--border);">
                            <div style="background:rgba(79,142,247,0.15);padding:8px;border-radius:8px;">
                                <i data-lucide="file-text" style="width:16px;height:16px;color:var(--accent);"></i>
                            </div>
                            <div style="overflow:hidden;flex:1;">
                                <p id="file-name" class="mono"
                                    style="font-size:10px;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                </p>
                                <p style="font-size:10px;color:var(--green);margin:0;">● Ready</p>
                            </div>
                        </div>
                        <p class="section-label">Active Mode: <span id="active-mode-label"
                                style="color:var(--accent);">LOKAL</span></p>
                    </div>

                    <button id="extract-btn" class="btn-primary" style="width:100%;justify-content:center;" disabled>
                        <i data-lucide="play" style="width:12px;height:12px;fill:currentColor;"></i>
                        EXECUTE ANALYSIS
                    </button>
                </div>
            </div>

            <!-- RESULTS AREA -->
            <div class="panel" id="results-area"
                style="min-height:420px;display:flex;flex-direction:column;overflow:hidden;">

                <!-- Empty state -->
                <div id="empty-state"
                    style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;opacity:0.3;padding:60px 0;">
                    <i data-lucide="command" style="width:40px;height:40px;color:var(--muted);"></i>
                    <p class="mono" style="font-size:12px;color:var(--muted);">Waiting for execution command...</p>
                </div>

                <!-- Loading state -->
                <div id="loading-state"
                    style="display:none;flex:1;flex-direction:column;padding:32px;gap:24px;justify-content:center;">

                    <div style="display:flex;align-items:center;gap:8px;">
                        <span class="mono" style="font-size:11px;color:var(--accent);">&gt;_</span>
                        <span id="loading-text" class="mono"
                            style="font-size:11px;color:var(--muted);">Initializing...</span>
                        <span class="cursor"></span>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span class="mono" style="font-size:10px;color:var(--muted);" id="progress-label">STEP 1 /
                                3</span>
                            <span class="mono" style="font-size:10px;color:var(--accent);" id="progress-pct">0%</span>
                        </div>
                        <div
                            style="width:100%;height:6px;background:var(--surface2);border-radius:99px;overflow:hidden;border:1px solid var(--border);">
                            <div id="progress-bar"
                                style="height:100%;width:0%;background:linear-gradient(90deg,var(--accent),var(--accent2));border-radius:99px;transition:width 0.5s cubic-bezier(.4,0,.2,1);">
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:10px;" id="steps-list">
                        <div class="step-item" data-step="0"
                            style="display:flex;align-items:center;gap:10px;opacity:0.4;">
                            <span class="step-icon mono" style="font-size:11px;width:18px;text-align:center;">○</span>
                            <span class="mono" style="font-size:10px;">Extract PDF text</span>
                        </div>
                        <div class="step-item" data-step="1"
                            style="display:flex;align-items:center;gap:10px;opacity:0.4;">
                            <span class="step-icon mono" style="font-size:11px;width:18px;text-align:center;">○</span>
                            <span class="mono" style="font-size:10px;">Send to AI engine</span>
                        </div>
                        <div class="step-item" data-step="2"
                            style="display:flex;align-items:center;gap:10px;opacity:0.4;">
                            <span class="step-icon mono" style="font-size:11px;width:18px;text-align:center;">○</span>
                            <span class="mono" style="font-size:10px;" id="step-analyze-label">Analyzing
                                blocks...</span>
                        </div>
                    </div>

                    <div
                        style="padding:12px 16px;background:var(--surface2);border-radius:10px;border:1px solid var(--border);min-height:48px;">
                        <p id="typing-preview" class="mono"
                            style="font-size:10px;color:var(--muted);white-space:pre-wrap;line-height:1.7;margin:0;">
                        </p>
                    </div>
                </div>

                <!-- Tabs + Content -->
                <div id="tabs-area" style="display:none;flex-direction:column;flex:1;overflow:hidden;">

                    <!-- Tab bar -->
                    <div style="padding:16px 20px 0;border-bottom:1px solid var(--border);position:relative;">
                        <!-- Scroll hint arrows -->
                        <button id="tab-scroll-left"
                            onclick="document.getElementById('tab-bar').scrollBy({left:-160,behavior:'smooth'})"
                            style="display:none;position:absolute;left:0;top:8px;bottom:12px;width:36px;background:linear-gradient(to right,var(--surface),transparent);border:none;cursor:pointer;z-index:2;align-items:center;justify-content:center;color:var(--muted);font-size:16px;padding:0;">‹</button>
                        <button id="tab-scroll-right"
                            onclick="document.getElementById('tab-bar').scrollBy({left:160,behavior:'smooth'})"
                            style="display:none;position:absolute;right:0;top:8px;bottom:12px;width:36px;background:linear-gradient(to left,var(--surface),transparent);border:none;cursor:pointer;z-index:2;align-items:center;justify-content:center;color:var(--muted);font-size:16px;padding:0;">›</button>

                        <div id="tab-bar"
                            style="display:flex;gap:6px;padding-bottom:10px;overflow-x:auto;scroll-behavior:smooth;-webkit-overflow-scrolling:touch;">
                            <!-- tabs injected here -->
                        </div>
                        <!-- Scroll indicator dots / track -->
                        <div id="tab-scrollbar-track"
                            style="height:3px;background:var(--surface2);border-radius:99px;margin-bottom:0;overflow:hidden;display:none;">
                            <div id="tab-scrollbar-thumb"
                                style="height:100%;background:var(--accent);border-radius:99px;width:30%;transition:left 0.1s,width 0.1s;position:relative;left:0%;">
                            </div>
                        </div>
                    </div>

                    <!-- Tab content -->
                    <div id="tab-content" style="flex:1;overflow-y:auto;padding:24px;">
                        <!-- content injected here -->
                    </div>
                </div>

            </div>

        </div>

        <!-- Footer -->
        <div style="margin-top:24px;display:flex;justify-content:space-between;align-items:center;">
            <span class="mono" style="font-size:9px;color:var(--muted);">PARSEAI v2.0 · ANALYSIS ENGINE</span>
            <span class="mono" style="font-size:9px;color:var(--muted);" id="footer-ts"></span>
        </div>
    </div>

    <script>
    lucide.createIcons();
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

    // Footer timestamp
    document.getElementById('footer-ts').textContent = new Date().toISOString().replace('T', '_').slice(0, 16);

    // --- State ---
    let selectedMode = 'lokal';
    let analysisData = []; // [{type:'kesimpulan',data:[...]}, {type:'blok',blok:'...',...}]
    let activeTab = null;

    // --- Mode pills ---
    document.querySelectorAll('.mode-pill').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.mode-pill').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            selectedMode = btn.dataset.mode;
            const labels = {
                lokal: 'LOKAL',
                ekspor: 'EKSPOR'
            };
            document.getElementById('active-mode-label').textContent = labels[selectedMode] ||
                selectedMode.toUpperCase();
        });
    });

    // --- File input ---
    const fileInput = document.getElementById('file-input');
    const dropZone = document.getElementById('drop-zone');

    dropZone.addEventListener('click', () => fileInput.click());
    dropZone.addEventListener('dragover', e => {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const f = e.dataTransfer.files[0];
        if (f && f.type === 'application/pdf') setFile(f);
    });

    fileInput.addEventListener('change', e => {
        const f = e.target.files[0];
        if (f) setFile(f);
    });

    function setFile(file) {
        document.getElementById('no-file').style.display = 'none';
        const fi = document.getElementById('file-info');
        fi.style.display = 'flex';
        document.getElementById('file-name').textContent = file.name;
        document.getElementById('extract-btn').disabled = false;
        fileInput._selectedFile = file;
    }

    // --- Extract PDF ---
    async function extractPDFText(file) {
        const buf = await file.arrayBuffer();
        const pdf = await pdfjsLib.getDocument(buf).promise;
        let text = '';
        for (let i = 1; i <= pdf.numPages; i++) {
            const page = await pdf.getPage(i);
            const content = await page.getTextContent();
            text += content.items.map(it => it.str).join(' ') + '\n';
        }
        return text;
    }

    // --- Tabs ---
    function buildTabs() {
        const bar = document.getElementById('tab-bar');
        bar.innerHTML = '';

        // Kesimpulan tab
        const kesimpulan = analysisData.find(d => d.type === 'kesimpulan');
        if (kesimpulan) {
            const btn = document.createElement('button');
            btn.className = 'tab-btn kesimpulan-tab';
            btn.textContent = '★ KESIMPULAN';
            btn.dataset.key = '__kesimpulan__';
            btn.addEventListener('click', () => activateTab('__kesimpulan__'));
            bar.appendChild(btn);
        }

        // Blok tabs
        analysisData.filter(d => d.type === 'blok').forEach(blok => {
            const btn = document.createElement('button');
            btn.className = 'tab-btn';
            btn.textContent = blok.blok.toUpperCase();
            btn.dataset.key = blok.blok;
            btn.addEventListener('click', () => activateTab(blok.blok));
            bar.appendChild(btn);
        });

        // Activate first
        const firstKey = kesimpulan ? '__kesimpulan__' : (analysisData.find(d => d.type === 'blok')?.blok);
        if (firstKey) activateTab(firstKey);
    }

    function activateTab(key) {
        activeTab = key;
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.key === key);
        });
        renderTabContent(key);
    }

    function renderTabContent(key) {
        const content = document.getElementById('tab-content');
        content.innerHTML = '';

        if (key === '__kesimpulan__') {
            const kesimpulan = analysisData.find(d => d.type === 'kesimpulan');
            if (!kesimpulan) return;

            content.innerHTML = `
                <div class="fade-up">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;">
                        <span style="font-size:18px;">★</span>
                        <h3 class="mono" style="font-size:13px;margin:0;color:var(--accent2);">KESIMPULAN & REKOMENDASI PRIORITAS</h3>
                    </div>
                    <ul style="list-style:none;margin:0;padding:0;" id="kesimpulan-list"></ul>
                </div>`;

            const ul = document.getElementById('kesimpulan-list');
            kesimpulan.data.forEach((item, i) => {
                const li = document.createElement('li');
                li.className = 'list-item fade-up';
                li.style.animationDelay = `${i*60}ms`;
                li.innerHTML =
                    `<span style="color:var(--accent2);margin-right:12px;font-weight:700;">→</span><span>${item}</span>`;
                ul.appendChild(li);
            });
            return;
        }

        const blok = analysisData.find(d => d.type === 'blok' && d.blok === key);
        if (!blok) return;

        const container = document.createElement('div');
        container.className = 'fade-up';
        container.style.cssText = 'display:flex;flex-direction:column;gap:24px;';

        // Title
        const title = document.createElement('div');
        title.innerHTML = `<h3 class="mono" style="font-size:16px;margin:0 0 4px;color:var(--accent);">[${blok.blok}]</h3>
            <p style="font-size:12px;color:var(--muted);margin:0;">Business Model Canvas Block Analysis</p>`;
        container.appendChild(title);

        // Divider
        const divider = document.createElement('hr');
        divider.style.cssText = 'border:none;border-top:1px solid var(--border);';
        container.appendChild(divider);

        // Sections
        function makeSection(label, color, icon, items) {
            const sec = document.createElement('div');
            sec.innerHTML =
                `<p style="display:flex;align-items:center;gap:6px;font-size:11px;font-family:'Space Mono',monospace;color:${color};margin:0 0 10px;letter-spacing:.08em;">${icon} ${label}</p>`;
            const ul = document.createElement('ul');
            ul.style.cssText = 'list-style:none;margin:0;padding:0;';
            items.forEach((item, i) => {
                const li = document.createElement('li');
                li.className = 'list-item fade-up';
                li.style.animationDelay = `${i*50}ms`;
                li.innerHTML =
                    `<span style="color:${color};margin-right:12px;font-family:'Space Mono',monospace;font-size:11px;">>></span><span>${item}</span>`;
                ul.appendChild(li);
            });
            sec.appendChild(ul);
            return sec;
        }

        container.appendChild(makeSection('DATA', 'var(--text)', '◆', blok.data || []));
        container.appendChild(document.createElement('hr') && (() => {
            const h = document.createElement('hr');
            h.style.cssText = 'border:none;border-top:1px solid var(--border);';
            return h;
        })());
        container.appendChild(makeSection('ANALISIS BLOK', 'var(--rose)', '⚠', blok.analisis_blok || []));
        container.appendChild(document.createElement('hr') && (() => {
            const h = document.createElement('hr');
            h.style.cssText = 'border:none;border-top:1px solid var(--border);';
            return h;
        })());
        container.appendChild(makeSection('SARAN / TAMBAHAN', 'var(--green)', '✦', blok.saran || []));

        content.appendChild(container);
    }

    // --- Typing queue ---
    let typingQueue = [];
    let isTyping = false;
    const typingArea = document.getElementById('typing-preview');

    function enqueueText(text) {
        for (const ch of text) typingQueue.push(ch);
        if (!isTyping) processQueue();
    }

    function processQueue() {
        if (!typingQueue.length) {
            isTyping = false;
            return;
        }
        isTyping = true;
        typingArea.textContent += typingQueue.shift();
        setTimeout(processQueue, 8);
    }

    // --- Stream BMC ---
    async function streamBMC(rawText) {
        analysisData = [];
        let tabsShown = false;
        let firstTab = null;
        let blockCount = 0;
        let estimatedTotal = 9; // default estimate, updated from response header if available
        const API_ENDPOINT = "<?= base_url() ?>bmc/stream";

        const loadingEl = document.getElementById('loading-state');
        typingArea.textContent = '';
        typingQueue = [];
        isTyping = false;

        function showTabsArea() {
            if (!tabsShown) {
                // Shrink loading into compact progress strip at top, show tabs below
                loadingEl.classList.add('progress-compact');
                document.getElementById('tabs-area').style.display = 'flex';
                tabsShown = true;
            }
        }

        function addTab(obj) {
            const bar = document.getElementById('tab-bar');
            const isKesimpulan = obj.type === 'kesimpulan';
            const key = isKesimpulan ? '__kesimpulan__' : obj.blok;
            const label = isKesimpulan ? '★ KESIMPULAN' : obj.blok.toUpperCase();

            // Don't add duplicate
            if (bar.querySelector(`[data-key="${key}"]`)) return;

            const btn = document.createElement('button');
            btn.className = 'tab-btn' + (isKesimpulan ? ' kesimpulan-tab' : '');
            btn.textContent = label;
            btn.dataset.key = key;
            btn.addEventListener('click', () => activateTab(key));
            bar.appendChild(btn);

            // Auto-activate first tab that arrives
            if (!firstTab) {
                firstTab = key;
                activateTab(key);
            }
        }

        try {
            const response = await fetch(API_ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    text: rawText,
                    mode: selectedMode
                })
            });

            if (!response.ok) throw new Error('Server error: ' + response.status);

            // Check for estimated block count from server header
            const estHeader = response.headers.get('X-Estimated-Blocks');
            if (estHeader) estimatedTotal = parseInt(estHeader) || estimatedTotal;

            setProgress(55, 'AI processing stream...', 2);

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
                lineBuffer = lines.pop();

                for (const line of lines) {
                    if (line.startsWith('RAW:')) {
                        const raw = line.slice(4);
                        if (!raw.includes('###')) enqueueText(raw);
                    } else if (line.startsWith('JSON:')) {
                        try {
                            const obj = JSON.parse(line.slice(5));
                            if (obj.type !== 'done') {
                                typingQueue = [];
                                isTyping = false;
                                typingArea.textContent = '';

                                analysisData.push(obj);
                                blockCount++;

                                // Update progress: step 3 (analyzing), 55%-95%
                                setProgress(55, 'Analyzing blocks...', 2);
                                updateBlockProgress(blockCount, estimatedTotal);

                                showTabsArea();
                                addTab(obj);
                            }
                        } catch (e) {}
                    } else if (line === 'DONE') {
                        typingQueue = [];
                        isTyping = false;
                        typingArea.textContent = '';
                        // Complete progress to 100%
                        setProgress(100, 'Analysis complete!', 2);
                        document.querySelectorAll('.step-item').forEach(el => {
                            el.style.opacity = '1';
                            const icon = el.querySelector('.step-icon');
                            icon.textContent = '✓';
                            icon.style.color = 'var(--green)';
                        });
                        // Ensure tabs shown, then fade out progress strip after a moment
                        showTabsArea();
                        setTimeout(() => {
                            loadingEl.style.opacity = '0';
                            loadingEl.style.transition = 'opacity 0.4s';
                            setTimeout(() => {
                                loadingEl.style.display = 'none';
                                loadingEl.style.opacity = '';
                            }, 420);
                        }, 800);
                    }
                }
            }

        } catch (err) {
            loadingEl.style.display = 'none';
            document.getElementById('tabs-area').style.display = 'flex';
            document.getElementById('tab-bar').innerHTML = '';
            document.getElementById('tab-content').innerHTML =
                `<p class="mono" style="color:var(--rose);font-size:12px;">ERROR: STREAM_FAILED — ${err.message}</p>`;
        }
    }

    function finalize() {
        document.getElementById('loading-state').style.display = 'none';
        document.getElementById('tabs-area').style.display = 'flex';
        buildTabs();
    }

    // --- Progress helpers ---
    let totalBlocks = 0; // estimated, updated as blocks arrive
    let doneBlocks = 0;

    function setProgress(pct, stepLabel, stepIndex) {
        document.getElementById('progress-bar').style.width = pct + '%';
        document.getElementById('progress-pct').textContent = Math.round(pct) + '%';
        if (stepLabel) document.getElementById('loading-text').textContent = stepLabel;

        document.querySelectorAll('.step-item').forEach((el, i) => {
            const icon = el.querySelector('.step-icon');
            if (i < stepIndex) {
                el.style.opacity = '1';
                icon.style.color = 'var(--green)';
                icon.textContent = '✓';
            } else if (i === stepIndex) {
                el.style.opacity = '1';
                icon.style.color = 'var(--accent)';
                icon.textContent = '◉';
            } else {
                el.style.opacity = '0.35';
                icon.style.color = '';
                icon.textContent = '○';
            }
        });

        const labels = ['STEP 1 / 3', 'STEP 2 / 3', 'STEP 3 / 3'];
        document.getElementById('progress-label').textContent = labels[Math.min(stepIndex, 2)] || 'STEP 3 / 3';
    }

    function updateBlockProgress(received, total) {
        if (!total) return;
        // Block analysis is step 3, occupies 40%-95% of bar
        const blockPct = 55 + (received / total) * 40;
        document.getElementById('progress-bar').style.width = Math.min(blockPct, 95) + '%';
        document.getElementById('progress-pct').textContent = Math.round(Math.min(blockPct, 95)) + '%';
        document.getElementById('step-analyze-label').textContent = `Analyzing blocks... (${received}/${total})`;
    }

    // --- Orchestrator ---
    document.getElementById('extract-btn').addEventListener('click', async () => {
        const file = fileInput._selectedFile || fileInput.files[0];
        if (!file) return;

        document.getElementById('extract-btn').disabled = true;

        // Show loading, reset progress
        document.getElementById('empty-state').style.display = 'none';
        document.getElementById('tabs-area').style.display = 'none';
        document.getElementById('loading-state').style.display = 'flex';
        document.getElementById('tab-bar').innerHTML = '';
        document.getElementById('tab-content').innerHTML = '';
        document.getElementById('progress-bar').style.width = '0%';
        document.getElementById('progress-pct').textContent = '0%';
        document.querySelectorAll('.step-item').forEach(el => {
            el.style.opacity = '0.4';
            el.querySelector('.step-icon').textContent = '○';
        });

        try {
            setProgress(5, 'Extracting PDF text...', 0);
            const text = await extractPDFText(file);
            setProgress(30, 'Connecting to AI engine...', 1);
            await streamBMC(text);
        } catch (err) {
            document.getElementById('loading-state').style.display = 'none';
            alert('Error: ' + err.message);
        } finally {
            document.getElementById('extract-btn').disabled = false;
        }
    });

    // --- Theme toggle ---
    const html = document.documentElement;
    document.getElementById('theme-toggle').addEventListener('click', e => {
        const btn = e.target.closest('.theme-btn');
        if (!btn) return;
        const theme = btn.dataset.theme;
        // Set attribute
        if (theme === 'dark') {
            html.removeAttribute('data-theme');
        } else {
            html.setAttribute('data-theme', theme);
        }
        // Update active state
        document.querySelectorAll('.theme-btn').forEach(b => b.classList.toggle('active', b === btn));
    });

    // --- Tab bar custom scrollbar + arrows ---
    function initTabScroll() {
        const bar = document.getElementById('tab-bar');
        const track = document.getElementById('tab-scrollbar-track');
        const thumb = document.getElementById('tab-scrollbar-thumb');
        const btnLeft = document.getElementById('tab-scroll-left');
        const btnRight = document.getElementById('tab-scroll-right');

        function update() {
            const scrollable = bar.scrollWidth > bar.clientWidth + 4;
            track.style.display = scrollable ? 'block' : 'none';
            btnLeft.style.display = scrollable && bar.scrollLeft > 10 ? 'flex' : 'none';
            btnRight.style.display = scrollable && bar.scrollLeft < (bar.scrollWidth - bar.clientWidth - 10) ? 'flex' :
                'none';
            if (scrollable) {
                const ratio = bar.clientWidth / bar.scrollWidth;
                const maxScroll = bar.scrollWidth - bar.clientWidth;
                const pct = maxScroll > 0 ? (bar.scrollLeft / maxScroll) * (100 - ratio * 100) : 0;
                thumb.style.width = (ratio * 100) + '%';
                thumb.style.left = pct + '%';
            }
        }

        bar.addEventListener('scroll', update);
        const obs = new MutationObserver(() => setTimeout(update, 60));
        obs.observe(bar, {
            childList: true
        });
        update();
    }

    initTabScroll();
    </script>

</body>

</html>