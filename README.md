# 🕷️ ClaudeCrawl — Web Scraper berbasis CodeIgniter 4

Aplikasi web scraper berbasis **CodeIgniter 4** yang mendukung scraping URL otomatis maupun file HTML lokal. Dilengkapi dengan **PipelineParser** yang fleksibel untuk mengekstrak data dari berbagai struktur HTML menggunakan CSS selector yang diperkaya dengan pseudo-selector kustom.

---

## 📋 Requirements

- PHP **^8.2**
- Composer
- MySQL / MariaDB
- Web server (Apache/Nginx) atau PHP built-in server

---

## 🚀 Instalasi

### 1. Install Composer

Jika belum terinstall, unduh dan install Composer terlebih dahulu:

```bash
# Linux / macOS
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Atau kunjungi: https://getcomposer.org/download/
```

Verifikasi instalasi:

```bash
composer --version
```

---

### 2. Clone Repository

```bash
git clone https://github.com/username/nama-repo.git
cd nama-repo
```

---

### 3. Install Dependencies

```bash
composer install
```

---

### 4. Konfigurasi Environment

Salin file `.env` dari template:

```bash
cp env .env
```

Edit file `.env` sesuai kebutuhan:

```env
CI_ENVIRONMENT = development

# Database
database.default.hostname = localhost
database.default.database = nama_database
database.default.username = root
database.default.password = 
database.default.DBDriver = MySQLi
database.default.port = 3306
```

---

### 5. Jalankan Aplikasi

```bash
php spark serve
```

Buka browser: `http://localhost:8080`

---

## 🖥️ Cara Penggunaan

### A. Scraping via Web UI

Akses halaman scraper melalui browser:

| Halaman            | URL                                  |
| ------------------ | ------------------------------------ |
| Form Scraper (URL) | `http://localhost:8080/scraper`      |
| Scraper File HTML  | `http://localhost:8080/scraper/file` |

---

### B. Scraping via CLI (Spark Command)

Jalankan scraper untuk siteKey tertentu yang sudah dikonfigurasi:

```bash
php spark scraper:run <siteKey>
```

Contoh:

```bash
php spark scraper:run kemenperin
php spark scraper:run inaexport
```

---

### C. Konfigurasi Site Scraper

Tambahkan konfigurasi situs di `app/Config/WebsiteScraperBaru.php`:

```php
'nama_situs' => [
    'start_url' => 'https://example.com/list?page=1',

    'pagination' => [
        'selector' => 'ul.pagination a[href]',
        'max_page' => 5
    ],

    'rate_limit' => [
        'base_delay_ms' => 800,
        'max_delay_ms'  => 5000,
        'retry'         => 3
    ],

    'stages' => [
        [
            'name'   => 'list',
            'parent' => 'table tbody tr',
            'fields' => [
                'nama'    => 'td:nth-child(1)',
                'alamat'  => 'td:nth-child(2)',
                'detail_url' => 'a[href]',
            ],
            'next' => 'detail_url' // lanjut ke stage berikutnya via URL ini
        ],

        [
            'name'   => 'detail',
            'parent' => 'div.content',
            'fields' => [
                'email'    => 'span.email',
                'telepon'  => 'span.phone',
            ]
        ]
    ]
]
```

---

## 🔍 Panduan Parser (CSS Selector)

PipelineParser mendukung CSS selector standar yang diperkaya dengan **pseudo-selector kustom**:

### Selector Standar

| Selector              | Deskripsi                | Contoh                  |
| --------------------- | ------------------------ | ----------------------- |
| `tag`                 | Elemen HTML biasa        | `h3`                    |
| `.class`              | Berdasarkan class        | `.company-name`         |
| `#id`                 | Berdasarkan ID           | `#result`               |
| `tag[attr]`           | Berdasarkan atribut      | `a[href]`               |
| `tag[attr*="val"]`    | Atribut mengandung nilai | `div[style*="color"]`   |
| `parent > child`      | Child langsung           | `ul > li`               |
| `el:nth-child(n)`     | Elemen ke-n              | `td:nth-child(2)`       |
| `el:contains("teks")` | Elemen mengandung teks   | `div:contains("Email")` |

---

### Pseudo-Selector Kustom

#### `:text(n)` — Ambil teks ke-n dari dalam elemen

Berguna ketika satu elemen mengandung banyak baris teks yang dipisahkan oleh `<br>`, newline, atau child element.

```php
// Ambil baris teks ke-2 dari dalam <td>
'alamat' => 'td:nth-child(2) :text(2)',

// Ambil baris teks ke-3 yang mengandung "Telp."
'telp' => 'td:nth-child(2) :text(3):contains("Telp.")',
```

---

#### `:group` — Grouping elemen bersaudara

Digunakan untuk mengelompokkan elemen-elemen sibling yang tidak dibungkus dalam parent yang sama.

```php
'stages' => [
    [
        'parent' => 'div.container',
        'group'  => ['h3.title', 'div.separator'], // [start_selector, end_selector]
        'fields' => [
            'judul'   => 'h3.title',
            'konten'  => 'p',
        ]
    ]
]
```

---

#### Ambil Atribut `href` dan `src`

Parser secara otomatis mendeteksi dan mengembalikan atribut `href` (pada `<a>`) dan `src` (pada `<img>`):

```php
'fields' => [
    'link_detail' => 'a.btn[href]',   // → mengambil nilai href
    'foto'        => 'img[src]',       // → mengambil nilai src
]
```

---

### Contoh Konfigurasi Lengkap

```php
[
    'name'   => 'list',
    'parent' => 'table#data tbody tr[valign="top"]',
    'fields' => [
        'perusahaan'   => 'td:nth-child(2) :text(1)',
        'alamat'       => 'td:nth-child(2) :text(2)',
        'telp'         => 'td:nth-child(2) :text(3):contains("Telp.")',
        'email'        => 'td:nth-child(2) :text(4):contains("e-Mail")',
        'komoditi'     => 'td:nth-child(3)',
        'bidang_usaha' => 'td:nth-child(4)',
    ]
]
```

---

## 📦 Dependencies Utama

| Package                    | Kegunaan                         |
| -------------------------- | -------------------------------- |
| `codeigniter4/framework`   | Framework utama                  |
| `symfony/dom-crawler`      | Parsing dan traversal HTML       |
| `symfony/css-selector`     | Konversi CSS selector ke XPath   |
| `guzzlehttp/guzzle`        | HTTP client untuk fetch URL      |
| `fabpot/goutte`            | Browser scraping (Goutte)        |
| `phpoffice/phpspreadsheet` | Export ke Excel (.xlsx)          |
| `smalot/pdfparser`         | Parsing file PDF                 |
| `phpoffice/phpword`        | Parsing/export file Word (.docx) |
| `tecnickcom/tcpdf`         | Generate PDF                     |
| `league/commonmark`        | Parsing Markdown                 |

---

## 📁 Struktur Penting

```
├── app/
│   ├── Commands/Scraper.php          # CLI command: php spark scraper:run
│   ├── Controllers/Scraper.php       # Web controller
│   ├── Config/WebsiteScraperBaru.php # ⭐ Konfigurasi semua situs scraper
│   └── Libraries/Crawler/
│       ├── ClaudeCrawl.php           # Engine scraping utama
│       ├── PipelineParser.php        # ⭐ HTML parser dengan pseudo-selector
│       ├── Fetcher.php               # HTTP fetcher
│       └── CheckpointStore.php       # Resume/checkpoint scraping
├── .env                              # Konfigurasi environment
└── composer.json                     # Dependencies
```

---

## 📄 Lisensi

MIT License
