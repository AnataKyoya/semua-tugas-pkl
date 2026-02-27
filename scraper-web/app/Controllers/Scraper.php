<?php

namespace App\Controllers;

use App\Libraries\Crawler\CheckpointStore;
use App\Libraries\Crawler\ClaudeCrawl;
use App\Libraries\Crawler\CrawlEngine;
use App\Libraries\Crawler\CrawlEngineNew;
use App\Libraries\SimpleScraper;
use CodeIgniter\API\ResponseTrait;
use Kint\Value\FunctionValue;

class Scraper extends BaseController
{
    protected $scraper;
    protected $scraperNew;
    protected $checkpoint;

    public function __construct()
    {
        $this->scraper = new SimpleScraper();
        $this->scraperNew = new ClaudeCrawl();
        $this->checkpoint = new CheckpointStore();
    }

    use ResponseTrait;

    public function set()
    {
        try {
            $configRaw = $this->request->getPost('config_data');
            $configs = json_decode($configRaw, true);
            $files = $this->request->getFiles();

            if (empty($configs)) {
                return $this->fail('Data konfigurasi kosong.');
            }

            $results = [];

            if (isset($files['html_files'])) {
                foreach ($files['html_files'] as $index => $file) {
                    if ($file->isValid() && !$file->hasMoved()) {

                        // Buat folder jika belum ada
                        $path = WRITEPATH . 'uploads/html_stages/';
                        if (!is_dir($path)) mkdir($path, 0777, true);

                        $newName = $file->getRandomName();
                        $file->move($path, $newName);

                        // Gabungkan info file dengan config yang dikirim
                        $results[] = [
                            'filename' => $newName,
                            'client_name' => $file->getClientName(),
                            'config' => $configs[$index] ?? []
                        ];
                    }
                }
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'Data diproses',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            // Jika terjadi error, kirim pesan JSON, bukan HTML error page
            return $this->fail($e->getMessage());
        }
    }

    /**
     * Halaman utama
     * URL: http://localhost/ci4-scraper/public/scraper
     */
    public function index()
    {
        return view('scraper_form');
    }

    public function scrape24x7()
    {
        $website = $this->request->getPost('website');
        $checkpoint = $this->checkpoint->load($website);

        $url_repeat_count = $checkpoint['url_repeat_count'] ?? 0;
        $html_repeat_count = $checkpoint['html_repeat_count'] ?? 0;
        $row_repeat_count = $checkpoint['row_repeat_count'] ?? 0;
        $paginate = $checkpoint['paginate'] ?? false;
        $pagination_finished = $checkpoint['pagination_finished'] ?? false;

        $index = 0;
        while ($index < 1) {
            if ($paginate) {
                if (
                    $pagination_finished &&
                    $html_repeat_count >= 3 &&
                    $url_repeat_count >= 3 &&
                    $row_repeat_count >= 3
                ) {
                    sleep(1800);
                }
            } else {
                if (
                    $html_repeat_count >= 3 &&
                    $url_repeat_count >= 3 &&
                    $row_repeat_count >= 3
                ) {
                    sleep(1800);
                }
            }

            try {
                // Jalankan scraping
                $this->scraperNew->run($website);
            } catch (\Exception $e) {
                return view('scraper_error', [
                    'error' => $e->getMessage()
                ]);
            }

            $index++;
            sleep(3);
        }
    }

    /**
     * Proses scraping
     * URL: /scraper/file
     */
    public function indexFile()
    {
        return view('scraper_file');
    }

    /**
     * Proses scraping
     * URL: /scraper/file/set
     */
    public function file()
    {
        try {
            $configRaw = $this->request->getPost('config_data');
            $configs = json_decode($configRaw, true);
            $files = $this->request->getFiles();

            if (empty($configs)) {
                return $this->fail('Data konfigurasi kosong.');
            }

            $results = [];

            if (isset($files['html_files'])) {
                foreach ($files['html_files'] as $index => $file) {
                    if ($file->isValid() && !$file->hasMoved()) {

                        // Buat folder jika belum ada
                        // $path = WRITEPATH . 'uploads/html_stages/';
                        // if (!is_dir($path)) mkdir($path, 0777, true);

                        // $newName = $configs[$index]['customName'] ?? $file->getRandomName();

                        // $hasHtml = pathinfo($newName, PATHINFO_EXTENSION) === 'html';
                        // $fullName = $hasHtml ? $newName : $newName . ".html";

                        $html = file_get_contents($file->getTempName());

                        // $file->move($path, $fullName);

                        $results[] = $this->scraperNew->runFileOnce($html, $configs[$index]);
                    }
                }
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'Data diproses',
                'link' => $results
            ]);
        } catch (\Exception $e) {
            // Jika terjadi error, kirim pesan JSON, bukan HTML error page
            return $this->fail($e->getMessage());
        }
    }

    /**
     * Proses scraping
     * URL: /scraper/file/panduan
     */
    public function panduan()
    {
        return view("panduan_file_scrape.php");
    }

    /**
     * Export ke CSV
     * URL: /scraper/export/(:any)
     */
    public function exportFile($siteKey)
    {

        try {
            $data = []; // database

            if (empty($data)) {
                return redirect()->back()->with('error', 'Tidak ada data untuk di-export!');
            }

            $filename = $siteKey . '_' . date('Y-m-d_H-i-s') . '.csv';

            // Header HTTP CSV
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-store, no-cache');

            $output = fopen('php://output', 'w');

            // Tambahkan BOM UTF-8 supaya Excel tidak rusak encoding
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header kolom CSV
            $headers = array_keys($data[0]);
            fputcsv($output, $headers);

            // Isi data
            foreach ($data as $row) {
                $cleanRow = [];
                foreach ($headers as $key) {
                    $cleanRow[] = $row[$key] ?? '';
                }

                fputcsv($output, $cleanRow);
            }

            fclose($output);
            exit;
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function ui()
    {
        return view('file_scrape');
    }
}
