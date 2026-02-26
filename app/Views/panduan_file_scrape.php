<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deep Dive Documentation | Scraper Engine</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        scroll-behavior: smooth;
    }

    .glass-header {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
    }

    .step-number {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    .instruction-card {
        border-left: 4px solid #3b82f6;
        transition: all 0.3s ease;
    }

    .instruction-card:hover {
        transform: translateX(8px);
        background: #f1f5f9;
    }

    pre {
        font-family: 'Fira Code', monospace;
    }
    </style>
</head>

<body class="bg-[#f8fafc] text-slate-900">

    <header class="fixed top-0 w-full z-50 glass-header border-b border-slate-200">
        <div class="max-w-5xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div
                    class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">
                    S</div>
                <h1 class="font-bold text-slate-800 tracking-tight text-xl">Scraper<span
                        class="text-blue-600">Pro</span></h1>
            </div>
            <a href="#analisa"
                class="bg-blue-600 text-white px-5 py-2 rounded-full text-sm font-bold hover:bg-blue-700 transition">Mulai
                Analisa</a>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-6 pt-32 pb-24">

        <div class="mb-16">
            <h2 class="text-4xl font-extrabold text-slate-900 mb-4 tracking-tight">Panduan Analisa Lapangan</h2>
            <p class="text-lg text-slate-500">Jangan sekadar menebak selector. Ikuti metodologi standar ini untuk
                menghasilkan data yang bersih dan akurat dari file HTML apapun.</p>
        </div>

        <section id="analisa" class="relative">
            <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-slate-200 hidden md:block"></div>

            <div class="space-y-16">

                <div class="relative pl-0 md:pl-16">
                    <div
                        class="absolute left-0 top-0 w-12 h-12 step-number rounded-2xl hidden md:flex items-center justify-center text-white font-bold text-xl shadow-lg">
                        1</div>
                    <div class="instruction-card bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                        <span class="text-blue-600 font-bold text-xs uppercase tracking-widest">Initial Discovery</span>
                        <h3 class="text-2xl font-bold mt-2 mb-4">Menentukan "The Parent" (Loop Anchor)</h3>
                        <p class="text-slate-600 mb-6">Parent adalah kunci utama. Jika salah menentukan parent, seluruh
                            data di bawahnya akan berantakan atau terlewat.</p>

                        <div class="bg-slate-50 p-5 rounded-xl border border-slate-200 space-y-3">
                            <h4 class="font-bold text-sm">Cara Eksekusi:</h4>
                            <ul class="text-sm text-slate-600 space-y-2 list-disc pl-5">
                                <li>Klik kanan pada teks data pertama, pilih <strong>Inspect</strong>.</li>
                                <li>Gerakkan kursor ke atas di dalam panel HTML (DOM) sampai Anda menemukan tag yang
                                    <strong>menyoroti seluruh area item pertama saja</strong>.
                                </li>
                                <li>Cek Item kedua. Apakah item kedua menggunakan tag dan class yang identik? Jika ya,
                                    itu adalah Parent Anda.</li>
                            </ul>
                            <div class="mt-4 p-3 bg-blue-900 text-blue-100 rounded text-xs font-mono">
                                // Contoh Ideal:<br>
                                table#data-list tbody tr<br>
                                div.container div.product-card
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative pl-0 md:pl-16">
                    <div
                        class="absolute left-0 top-0 w-12 h-12 step-number rounded-2xl hidden md:flex items-center justify-center text-white font-bold text-xl shadow-lg">
                        2</div>
                    <div class="instruction-card bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                        <span class="text-blue-600 font-bold text-xs uppercase tracking-widest">Data Mapping</span>
                        <h3 class="text-2xl font-bold mt-2 mb-4">Ekstraksi Field Relatif</h3>
                        <p class="text-slate-600 mb-6">Sekarang, ambil data di dalam parent. Ingat: Selector field
                            <strong>tidak boleh</strong> mengulang selector parent agar tetap efisien.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 border border-slate-100 rounded-xl bg-slate-50">
                                <h5 class="font-bold text-sm mb-2 text-blue-700">Situasi A: Data Teratur</h5>
                                <p class="text-xs text-slate-500">Jika data ada di kolom tabel yang pasti.</p>
                                <code
                                    class="block mt-2 text-[10px] bg-white p-2 border border-slate-200 rounded">td:nth-child(2)</code>
                            </div>
                            <div class="p-4 border border-slate-100 rounded-xl bg-slate-50">
                                <h5 class="font-bold text-sm mb-2 text-blue-700">Situasi B: Data Berantakan</h5>
                                <p class="text-xs text-slate-500">Jika data ditumpuk dalam satu elemen.</p>
                                <code
                                    class="block mt-2 text-[10px] bg-white p-2 border border-slate-200 rounded">td :text(2)</code>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative pl-0 md:pl-16">
                    <div
                        class="absolute left-0 top-0 w-12 h-12 step-number rounded-2xl hidden md:flex items-center justify-center text-white font-bold text-xl shadow-lg">
                        3</div>
                    <div class="instruction-card bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                        <span class="text-blue-600 font-bold text-xs uppercase tracking-widest">Advanced Strategy</span>
                        <h3 class="text-2xl font-bold mt-2 mb-4">Menangani "Label Variable"</h3>
                        <p class="text-slate-600 mb-6">Banyak website startup menyembunyikan data di balik label. Posisi
                            barisnya bisa berubah-ubah antar halaman.</p>

                        <div class="bg-amber-50 border border-amber-200 p-6 rounded-2xl">
                            <h4 class="font-bold text-amber-800 text-sm mb-3 underline">Gunakan Senjata Rahasia:
                                :contains</h4>
                            <p class="text-sm text-amber-700 mb-4 leading-relaxed">Jangan gunakan index jika posisi
                                "Email" di perusahaan A ada di baris 3, tapi di perusahaan B ada di baris 5. Gunakan
                                selector berbasis teks:</p>
                            <div class="bg-amber-900 text-amber-100 p-4 rounded-lg font-mono text-xs">
                                // Format: parent_label:contains("Teks Label") sub_elemen<br><br>
                                div.row:contains("Business Type") .col-sm-9
                            </div>
                            <p class="mt-4 text-xs italic text-amber-600">*Sistem akan mencari elemen div.row yang punya
                                kata "Business Type", lalu masuk ke dalam .col-sm-9 untuk mengambil nilainya.</p>
                        </div>
                    </div>
                </div>

                <div class="relative pl-0 md:pl-16">
                    <div
                        class="absolute left-0 top-0 w-12 h-12 step-number rounded-2xl hidden md:flex items-center justify-center text-white font-bold text-xl shadow-lg">
                        4</div>
                    <div class="instruction-card bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                        <span class="text-blue-600 font-bold text-xs uppercase tracking-widest">Final Validation</span>
                        <h3 class="text-2xl font-bold mt-2 mb-4">Uji Coba di Browser Console</h3>
                        <p class="text-slate-600 mb-6">Sebelum memasukkan ke config, pastikan selector Anda benar-benar
                            bekerja di mata browser.</p>

                        <div class="bg-slate-900 rounded-xl p-6 text-slate-300">
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                <span class="ml-2 text-[10px] text-slate-500 font-mono">Browser Developer Tools</span>
                            </div>
                            <p class="text-xs mb-3 text-slate-400">// Ketik ini di Console (F12):</p>
                            <pre class="text-xs leading-relaxed">
<span class="text-blue-400">let</span> items = document.querySelectorAll(<span class="text-green-400">'PARENT_SELECTOR_ANDA'</span>);
console.log(<span class="text-green-400">'Ditemukan:'</span>, items.length);

<span class="text-slate-500">// Cek field di item pertama</span>
console.log(items[0].querySelector(<span class="text-green-400">'FIELD_SELECTOR_ANDA'</span>).innerText);</pre>
                        </div>
                    </div>
                </div>

                <div class="relative pl-0 md:pl-16">
                    <div
                        class="absolute left-0 top-0 w-12 h-12 step-number rounded-2xl hidden md:flex items-center justify-center text-white font-bold text-xl shadow-lg">
                        5</div>
                    <div class="instruction-card bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                        <span class="text-blue-600 font-bold text-xs uppercase tracking-widest">Workflow Logic</span>
                        <h3 class="text-2xl font-bold mt-2 mb-4">Mekanisme "Next Stage"</h3>
                        <p class="text-slate-600 mb-6">Gunakan ini jika Anda ingin sistem mengambil link dari Stage 1,
                            lalu otomatis membukanya untuk diproses di Stage 2.</p>

                        <div class="space-y-4">
                            <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-xl">
                                <h5 class="font-bold text-sm text-emerald-800 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z">
                                        </path>
                                    </svg>
                                    Aturan Main:
                                </h5>
                                <ul class="text-xs text-emerald-700 mt-2 space-y-2 list-disc pl-5">
                                    <li>Salah satu field di Stage saat ini <b>WAJIB</b> bernama <code
                                            class="bg-white px-1">detail_url</code> atau mengandung link.</li>
                                    <li>Pada dropdown <b>Next Stage</b>, pilih stage tujuan (misal: Stage 2).</li>
                                    <li>Sistem akan mengumpulkan semua link dari Stage 1, lalu menjalankan aturan Stage
                                        2 di dalam link-link tersebut.</li>
                                </ul>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-[11px]">
                                <div class="p-3 bg-slate-800 text-slate-300 rounded-lg font-mono">
                                    <span class="text-orange-400">// Stage 1 (List)</span><br>
                                    Field Name: <span class="text-yellow-400">detail_url</span><br>
                                    Selector: <span class="text-yellow-400">a.title-link</span><br>
                                    Next Stage: <span class="text-sky-400">Stage 2</span>
                                </div>
                                <div class="p-3 bg-slate-800 text-slate-300 rounded-lg font-mono">
                                    <span class="text-orange-400">// Stage 2 (Detail)</span><br>
                                    Field Name: <span class="text-yellow-400">phone_number</span><br>
                                    Selector: <span class="text-yellow-400">.contact-info b</span><br>
                                    Next Stage: <span class="text-sky-400">-- Finish --</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative pl-0 md:pl-16">
                    <div
                        class="absolute left-0 top-0 w-12 h-12 step-number rounded-2xl hidden md:flex items-center justify-center text-white font-bold text-xl shadow-lg">
                        6</div>
                    <div class="instruction-card bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                        <span class="text-red-600 font-bold text-xs uppercase tracking-widest">Troubleshooting</span>
                        <h3 class="text-2xl font-bold mt-2 mb-4">Kenapa Data Saya Kosong?</h3>
                        <p class="text-slate-600 mb-6">Jika hasil CSV menunjukkan kolom kosong, biasanya disebabkan oleh
                            salah satu dari 4 faktor berikut:</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 rounded-xl border border-red-100 bg-red-50/50">
                                <h5 class="font-bold text-sm text-red-800 flex items-center">
                                    <span
                                        class="w-5 h-5 bg-red-200 text-red-700 rounded-full flex items-center justify-center mr-2 text-[10px]">1</span>
                                    Selector Terlalu Spesifik
                                </h5>
                                <p class="text-xs text-slate-600 mt-2">
                                    Gunakan class yang umum. Hindari class acak seperti <code
                                        class="bg-white px-1">.css-1abc2-container</code> karena class ini sering
                                    berubah setiap kali halaman di-refresh.
                                </p>
                            </div>

                            <div class="p-4 rounded-xl border border-orange-100 bg-orange-50/50">
                                <h5 class="font-bold text-sm text-orange-800 flex items-center">
                                    <span
                                        class="w-5 h-5 bg-orange-200 text-orange-700 rounded-full flex items-center justify-center mr-2 text-[10px]">2</span>
                                    Typo / Salah Tulis Nama
                                </h5>
                                <p class="text-xs text-slate-600 mt-2">
                                    Ingat: <code class="bg-white px-1">:contains("Email")</code> berbeda dengan <code
                                        class="bg-white px-1">:contains("email")</code>. Besar kecil huruf sangat
                                    berpengaruh (Case Sensitive).
                                </p>
                            </div>

                            <div class="p-4 rounded-xl border border-amber-100 bg-amber-50/50">
                                <h5 class="font-bold text-sm text-amber-800 flex items-center">
                                    <span
                                        class="w-5 h-5 bg-amber-200 text-amber-700 rounded-full flex items-center justify-center mr-2 text-[10px]">3</span>
                                    Struktur Data Berbeda
                                </h5>
                                <p class="text-xs text-slate-600 mt-2">
                                    Kadang perusahaan A punya nomor telepon, tapi perusahaan B tidak. Jika selector
                                    tidak menemukan elemennya, sistem akan memberikan hasil kosong.
                                </p>
                            </div>

                            <div class="p-4 rounded-xl border border-blue-100 bg-blue-50/50">
                                <h5 class="font-bold text-sm text-blue-800 flex items-center">
                                    <span
                                        class="w-5 h-5 bg-blue-200 text-blue-700 rounded-full flex items-center justify-center mr-2 text-[10px]">4</span>
                                    Elemen di Luar Parent
                                </h5>
                                <p class="text-xs text-slate-600 mt-2">
                                    Field Selector harus berada <b>di dalam</b> Parent Selector. Jika Anda mencari data
                                    yang posisinya di luar kotak (parent) yang sudah ditentukan, data tidak akan
                                    terambil.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 p-4 bg-slate-900 rounded-xl">
                            <p class="text-[11px] text-slate-400 italic">
                                <span class="text-amber-400 font-bold underline">Tips:</span> Selalu gunakan <b>Inspect
                                    Element</b> untuk memastikan elemen tersebut memang ada di dalam file HTML yang Anda
                                upload.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="mt-24 p-10 bg-blue-600 rounded-[3rem] text-white">
            <h3 class="text-2xl font-bold mb-6">Common Patterns Cheatsheet</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <p class="text-blue-200 font-bold text-xs uppercase tracking-widest italic">Jika HTML Seperti Ini:
                    </p>
                    <p class="text-sm border-l-2 border-blue-400 pl-4">
                        <code>&lt;td&gt;Nama&lt;br&gt;Alamat&lt;/td&gt;</code>
                    </p>
                    <p class="text-blue-200 font-bold text-xs uppercase tracking-widest italic mt-4">Gunakan Selector:
                    </p>
                    <p class="text-sm bg-blue-700 p-2 rounded"><code>td :text(2)</code></p>
                </div>
                <div class="space-y-2">
                    <p class="text-blue-200 font-bold text-xs uppercase tracking-widest italic">Jika HTML Seperti Ini:
                    </p>
                    <p class="text-sm border-l-2 border-blue-400 pl-4">
                        <code>&lt;a href="link"&gt;Detail&lt;/a&gt;</code>
                    </p>
                    <p class="text-blue-200 font-bold text-xs uppercase tracking-widest italic mt-4">Gunakan Field Name:
                    </p>
                    <p class="text-sm bg-blue-700 p-2 rounded"><code>detail_url</code></p>
                </div>
            </div>
        </div>

        <section class="mt-16 border-t border-slate-200 pt-16">
            <h3 class="text-3xl font-extrabold text-slate-900 mb-8 tracking-tight">Selector Cheatsheet</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col h-full">
                    <div
                        class="w-10 h-10 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center mb-4 font-bold">
                        : ]</div>
                    <h4 class="font-bold text-slate-800 mb-2">:contains("Teks")</h4>
                    <p class="text-xs text-slate-500 mb-4">Mencari elemen yang mengandung teks tertentu.</p>

                    <div class="space-y-4 flex-1">
                        <div class="bg-slate-900 p-3 rounded-lg font-mono text-[9px] text-slate-300">
                            <p class="text-slate-500 mb-1">// HTML Source</p>
                            <p>&lt;div class="row"&gt;</p>
                            <p>&nbsp;&nbsp;&lt;span&gt;Email: info@web.com&lt;/span&gt;</p>
                            <p>&lt;/div&gt;</p>
                        </div>
                        <div
                            class="bg-blue-50 p-3 rounded-lg font-mono text-[10px] space-y-2 text-slate-700 border border-blue-100">
                            <p class="text-blue-600 font-bold">// Target: div yang ada teks "Email"</p>
                            <p class="bg-white p-1 rounded">div.row:contains("Email")</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col h-full">
                    <div
                        class="w-10 h-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mb-4 font-bold">
                        T[ ]</div>
                    <h4 class="font-bold text-slate-800 mb-2">:text(index)</h4>
                    <p class="text-xs text-slate-500 mb-4">Mengambil baris teks tertentu (berguna jika ada &lt;br&gt;).
                    </p>

                    <div class="space-y-4 flex-1">
                        <div class="bg-slate-900 p-3 rounded-lg font-mono text-[9px] text-slate-300">
                            <p class="text-slate-500 mb-1">// HTML Source</p>
                            <p>&lt;td&gt;</p>
                            <p>&nbsp;&nbsp;Budi Santoso &lt;br&gt;</p>
                            <p>&nbsp;&nbsp;Jl. Merdeka No. 1</p>
                            <p>&lt;/td&gt;</p>
                        </div>
                        <div
                            class="bg-blue-50 p-3 rounded-lg font-mono text-[10px] space-y-2 text-slate-700 border border-blue-100">
                            <p class="text-blue-600 font-bold">// Ambil Alamat saja (baris 2)</p>
                            <p class="bg-white p-1 rounded">td :text(2)</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col h-full">
                    <div
                        class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center mb-4 font-bold">
                        #[ ]</div>
                    <h4 class="font-bold text-slate-800 mb-2">:nth-child(n)</h4>
                    <p class="text-xs text-slate-500 mb-4">Mengambil elemen berdasarkan urutan posisinya.</p>

                    <div class="space-y-4 flex-1">
                        <div class="bg-slate-900 p-3 rounded-lg font-mono text-[9px] text-slate-300">
                            <p class="text-slate-500 mb-1">// HTML Source</p>
                            <p>&lt;ul&gt;</p>
                            <p>&nbsp;&nbsp;&lt;li&gt;Home&lt;/li&gt;</p>
                            <p>&nbsp;&nbsp;&lt;li&gt;About&lt;/li&gt; <span class="text-emerald-500">&lt;--
                                    Target</span></p>
                            <p>&lt;/ul&gt;</p>
                        </div>
                        <div
                            class="bg-blue-50 p-3 rounded-lg font-mono text-[10px] space-y-2 text-slate-700 border border-blue-100">
                            <p class="text-blue-600 font-bold">// Pilih elemen ke-2</p>
                            <p class="bg-white p-1 rounded">li:nth-child(2)</p>
                        </div>
                    </div>
                </div>

            </div>

            <div class="mt-8 bg-blue-50 border border-blue-100 p-6 rounded-2xl flex items-start space-x-4">
                <div class="text-blue-500 mt-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h5 class="font-bold text-blue-900 text-sm">Pro Tip: Attribute Selector</h5>
                    <p class="text-xs text-blue-700 leading-relaxed mt-1">
                        Gunakan <code class="bg-blue-100 px-1 rounded">a[href]</code> untuk mengambil link, atau <code
                            class="bg-blue-100 px-1 rounded">img[src]</code> untuk gambar. Anda bahkan bisa memfilter
                        style seperti <code class="bg-blue-100 px-1 rounded">h3[style*="color: blue"]</code> jika elemen
                        tidak memiliki class yang unik.
                    </p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col h-full mt-8">
                <div
                    class="w-10 h-10 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center mb-4 font-bold">
                    @ ]</div>
                <h4 class="font-bold text-slate-800 mb-2">Attribute [href/src]</h4>
                <p class="text-xs text-slate-500 mb-4">Mengambil nilai di dalam atribut, bukan teks yang terlihat di
                    layar.</p>

                <div class="space-y-4 flex-1">
                    <div class="bg-slate-900 p-3 rounded-lg font-mono text-[9px] text-slate-300">
                        <p class="text-slate-500 mb-1">// HTML Source</p>
                        <p>&lt;a href="mailto:admin@pt.com" class="btn"&gt;</p>
                        <p>&nbsp;&nbsp;Hubungi Kami</p>
                        <p>&lt;/a&gt;</p>
                    </div>
                    <div
                        class="bg-blue-50 p-3 rounded-lg font-mono text-[10px] space-y-2 text-slate-700 border border-blue-100">
                        <p class="text-blue-600 font-bold">// Target: Ambil alamat emailnya</p>
                        <p class="bg-white p-1 rounded">a.btn[href]</p>
                        <p class="text-[9px] text-slate-400 italic">*Hasil: mailto:admin@pt.com</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="text-center pb-12">
        <p class="text-slate-400 text-xs tracking-widest font-bold uppercase">End of Documentation - Scraper Engine v2.0
        </p>
    </footer>

</body>

</html>