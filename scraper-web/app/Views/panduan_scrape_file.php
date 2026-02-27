<?php

/**
 * FILE: Panduan_Lengkap_Scraper.php
 * Dokumentasi Konfigurasi Stage, Field, dan Teknik Analisa HTML
 * Berdasarkan: PipelineParser.php & WebsiteScraperBaru.php
 */

$panduan = [
    'HEADER' => [
        'title' => 'Panduan Konfigurasi Scraper & Analisa HTML',
        'version' => '2.0 (Februari 2026)',
        'description' => 'Gunakan file ini sebagai acuan saat mengisi form di HTML Stage Manager.'
    ],

    'BAGIAN_1_LOGIKA_KONFIGURASI' => [
        'CORE_FIELD' => [
            'parent' => 'Selector CSS pembungkus satu blok data (Looping).',
            'fields' => 'Daftar data yang ingin diambil secara relatif dari elemen parent.',
            'next'   => 'Indeks stage tujuan jika ingin lanjut ke halaman lain (Opsional).'
        ],
        'TIPE_DATA_KHUSUS' => [
            'detail_url' => 'Nama field wajib jika ingin mengambil atribut href (link).',
            'image'      => 'Field yang mengandung kata "image" otomatis mengambil atribut src (gambar).'
        ]
    ],

    'BAGIAN_2_TEKNIK_SELECTOR_CANGGIH' => [
        'TEXT_BREAKOUT' => [
            'syntax' => 'selector :text(n)',
            'fungsi' => 'Mengambil teks urutan ke-n jika dipisahkan <br> atau baris baru.',
            'contoh' => 'td:nth-child(2) :text(1).'
        ],
        'LABEL_LOOKUP' => [
            'syntax' => 'selector:contains("Label")',
            'fungsi' => 'Mencari label teks dan otomatis mengambil isi setelah tanda ":" atau "-".',
            'contoh' => 'div.row:contains("Email").'
        ],
        'LABEL_NESTED' => [
            'syntax' => 'selector:contains("Label") sub_selector',
            'fungsi' => 'Cari elemen berlabel, lalu ambil data dari sub-elemen spesifik di dalamnya.',
            'contoh' => 'div.row:contains("PIC") .col-sm-9.'
        ]
    ],

    'BAGIAN_3_CARA_ANALISA_HTML' => [
        'STEP_BY_STEP' => [
            '1. Menentukan Parent' => [
                'Action' => 'Klik kanan -> Inspect pada item pertama di browser.',
                'Tips'   => 'Cari elemen berulang seperti <tr> atau <div> dengan class yang sama (misal .post-item).'
            ],
            '2. Menentukan Field' => [
                'Action' => 'Lihat struktur di dalam Parent.',
                'Tips'   => 'Gunakan selector yang paling unik. Jika data acak, gunakan metode :contains("Nama Label").'
            ],
            '3. Validasi Link' => [
                'Action' => 'Pastikan elemen <a> memiliki atribut href yang valid.',
                'Field'  => 'Gunakan field "detail_url" untuk menangkap link tersebut.'
            ]
        ],
        'CONTOH_REAL_CASE' => [
            'inaexport'  => 'Menggunakan :contains untuk data yang labelnya tetap tapi barisnya bisa bergeser.',
            'kemenperin' => 'Menggunakan :text(n) untuk data tabel yang menumpuk di satu kolom.',
            'pertanian'  => 'Menggunakan ID halaman (div#pf1) sebagai parent untuk file HTML statis.'
        ]
    ]
];

// Tampilkan panduan dengan format yang rapi
header('Content-Type: text/plain');
echo "============================================================\n";
echo "   " . strtoupper($panduan['HEADER']['title']) . "   \n";
echo "============================================================\n\n";

foreach ($panduan as $section => $content) {
    if ($section === 'HEADER') continue;
    echo "## " . str_replace('_', ' ', $section) . "\n";
    print_r($content);
    echo "\n";
}

echo "\nCreated by Gemini AI - 2026";
