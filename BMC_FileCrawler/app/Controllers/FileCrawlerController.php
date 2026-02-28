<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\FileCrawler\FileCrawler;
use App\Libraries\Gemini;
use App\Libraries\HarmBlockThreshold;
use App\Libraries\HarmCategory;
use App\Libraries\SafetySetting;
use App\Models\ScrapeModel;
use CodeIgniter\HTTP\ResponseInterface;

use App\Libraries\OpenRouter;

// TCPDF for PDF
use TCPDF;

// PhpSpreadsheet for Excel
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// PhpWord for Word
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;

class FileCrawlerController extends BaseController
{
    protected $filecrawler;
    protected $ai;
    protected $db;
    protected $openrouter;

    public function __construct()
    {
        $this->filecrawler = new FileCrawler();
        $this->ai = new Gemini();
        $this->db = \Config\Database::connect();

        $safetySettings = [
            SafetySetting::create(HarmCategory::HARM_CATEGORY_HARASSMENT, HarmBlockThreshold::BLOCK_MEDIUM_AND_ABOVE),
            SafetySetting::create(HarmCategory::HARM_CATEGORY_HATE_SPEECH, HarmBlockThreshold::BLOCK_MEDIUM_AND_ABOVE),
            SafetySetting::create(HarmCategory::HARM_CATEGORY_SEXUALLY_EXPLICIT, HarmBlockThreshold::BLOCK_ONLY_HIGH),
            SafetySetting::create(HarmCategory::HARM_CATEGORY_DANGEROUS_CONTENT, HarmBlockThreshold::BLOCK_ONLY_HIGH),
        ];

        $this->ai->setSafetySettings($safetySettings);
        $this->ai->setGenerationConfig([
            'temperature' => 0.1,
            'topP' => 0.8,
        ]);

        $this->openrouter = new OpenRouter();
        $this->openrouter->setGenerationConfig([
            'temperature' => 0.0,
            'topP' => 0.1,
            'topK' => 1,
            'seed' => 42
        ]);
    }

    public function index()
    {
        return view('file_scraper');
    }

    /**
     * Upload file endpoint
     */
    public function fileUpload()
    {
        try {
            $file = $this->request->getFile('file');

            // Validasi file
            if (!$file || !$file->isValid()) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'File tidak valid'
                ])->setStatusCode(400);
            }

            // Ukuran file detail
            $fileSize = $file->getSize();
            $fileSizeMB = round($fileSize / 1024 / 1024, 2);

            // Validasi ukuran (max 15MB)
            if ($fileSize > 15 * 1024 * 1024) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => "File terlalu besar ({$fileSizeMB}MB). Maksimal 15MB."
                ])->setStatusCode(400);
            }

            // Validasi tipe file
            $allowedTypes = ['txt', 'pdf', 'docx', 'csv', 'md'];
            $ext = strtolower($file->getExtension());

            if (!in_array($ext, $allowedTypes)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Tipe file tidak didukung. Hanya: ' . implode(', ', $allowedTypes)
                ])->setStatusCode(400);
            }

            // Upload file
            $uploadPath = WRITEPATH . 'uploads/';

            // Pastikan folder ada
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $originalName = $file->getName();
            $newName = $file->getRandomName();
            $file->move($uploadPath, $newName);

            log_message('info', 'File uploaded: ' . $newName . ' (' . $file->getSize() . ' bytes)');

            return $this->response->setJSON([
                'success' => true,
                'file_id' => $newName,
                'name' => $originalName,
                'size' => $file->getSize(),
                'extension' => $ext
            ])->setStatusCode(200);
        } catch (\Exception $e) {
            log_message('error', 'File upload error: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'error' => 'Gagal upload file: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Process files endpoint
     */
    public function process()
    {
        $uploadedFiles = []; // Track files untuk cleanup

        try {
            $json = $this->request->getJSON(true);

            // Validasi input
            if (!isset($json['prompt'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'file_ids dan prompt wajib diisi'
                ])->setStatusCode(400);
            }

            // $fileIds = $json['file_ids'];
            $prompt = $json['prompt'];
            $fullTextFile = $json['text'];

            // Validasi prompt
            if (empty($prompt) || trim($prompt) === '') {
                // Cleanup files jika prompt kosong
                // $this->cleanupFiles($fileIds);

                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Prompt tidak boleh kosong'
                ])->setStatusCode(400);
            }

            // Validasi file_ids
            // if (!is_array($fileIds) || empty($fileIds)) {
            //     return $this->response->setJSON([
            //         'success' => false,
            //         'error' => 'Tidak ada file yang dipilih'
            //     ])->setStatusCode(400);
            // }

            $fullFile = '';
            $uploadPath = WRITEPATH . 'uploads/';

            // Proses setiap file
            // foreach ($fileIds as $fileId) {
            //     $path = $uploadPath . $fileId;

            //     // Cek file exists
            //     if (!file_exists($path)) {
            //         log_message('warning', 'File not found: ' . $fileId);
            //         continue;
            //     }

            //     $uploadedFiles[] = $path; // Track untuk cleanup

            //     // Get extension
            //     $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            //     log_message('debug', 'Processing file: ' . $fileId . ' (ext: ' . $ext . ')');

            //     // Baca file
            //     $content = $this->filecrawler->readFile($path, $ext);

            //     // Pastikan content adalah UTF-8 valid
            //     $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');

            //     // Batasi panjang content per file
            //     $maxLength = 50000;
            //     if (strlen($content) > $maxLength) {
            //         $content = substr($content, 0, $maxLength)
            //             . "\n\n[... konten dipotong karena terlalu panjang ...]";
            //     }

            //     // Tambahkan ke full file
            //     $fullFile .= "=== FILE: " . $fileId . " ===\n\n"
            //         . $content . "\n\n";
            // }

            // Gabungkan dengan prompt
            // $fullPrompt = $fullFile . "=== INSTRUKSI ===\n" . $prompt . "\n\n";

            $fullPrompt = <<<PROMPT
$fullTextFile

[ATURAN]
1. Respon WAJIB tanpa markdown format
2. Respon tanpa pembuka dan penutup, HANYA HASIL YANG DIMINTA TANPA ADA HAL LAIN
3. Respon dalam format yang ditentukan, hanya sebuah format bukan patokan data
4. Tidak boleh ada object tanpa key.
5. JSON harus valid dan bisa diparse oleh json_decode().
6. Beri value "nama_file" dari kesimpulan isi data, singkat aja
7. Hasil data wajib lengkap
8. Jika ada field kosong tetap tulis "website": ""
9. Jangan pernah membuat object baru tanpa key.

FORMAT:
{"nama_file": "value", "data": [{ "nama_perusahaan": "value", "alamat": "value", ...}, { "nama_perusahaan": "value", "alamat": "value", ...}]}

CONTOH BENAR:
{"nama_file": "Data Perusahaan Jakarta", "data": [{"nama_perusahaan": "PT ABC", "alamat": "Jakarta"}]}

[INSTRUKSI]
$prompt

PROMPT;

            log_message('debug', 'Total prompt length: ' . strlen($fullPrompt));
            // log_message('debug', 'prompt: ' . $fullPrompt);

            // Kirim ke AI
            $aiPrompt = [
                ["role" => "user", "content" => $fullPrompt]
            ];

            // $response = $this->ai->complete($aiPrompt); // Gemini
            $response = $this->openrouter->complete($aiPrompt); // OpenRouter (StepFun)

            // $rawJson = $this->extractJson($response);

            // if (!$rawJson) {
            //     return $this->response->setJSON([
            //         'success' => false,
            //         'error' => 'Tidak ditemukan JSON dalam response AI'
            //     ])->setStatusCode(500);
            // }

            // $start = strpos($response, '[');
            // $end = strrpos($response, ']');

            // if ($start !== false && $end !== false) {
            //     $response = substr($response, $start, $end - $start + 1);
            // }

            // $response = trim($response);

            if (substr($response, -1) === ']') {
                $response .= '}';
            }

            $json_decode = json_decode($response, true);

            log_message('error', 'Invalid JSON response: ' . json_encode($json_decode));

            // if (!isset($json_decode[0], $json_decode[1])) {
            //     return $this->response->setJSON([
            //         'success' => false,
            //         'error' => 'Format response AI tidak sesuai'
            //     ])->setStatusCode(500);
            // }

            $data = json_encode($json_decode['data']);

            // $fileUploadCount = count($uploadedFiles);
            $clean_name_file = $json_decode['nama_file'];

            $id_file = sprintf(
                'file-%04x%04x-%04x',
                random_int(0, 0xffff),
                random_int(0, 0x0fff) | 0x4000,
                random_int(0, 0x3fff) | 0x8000,
            );

            // $modelScrape = new ScrapeModel();
            // $modelScrape->insert(['id_file' => $id_file, 'hasil' => $data]);
            // $modelScrape = new ScrapeModel();
            // $modelScrape->insert(['nama' => $clean_name_file, 'hasil' => $data]);

            // log_message('info', 'Files processed successfully. Total: ' . count($fileIds));

            // ✅ CLEANUP: Hapus semua file setelah proses
            // $this->cleanupFiles($fileIds);

            return $this->response->setJSON([
                'success' => true,
                'name' => $clean_name_file,
                'id_file' => $id_file,
                'type' => 'json',
            ])->setStatusCode(200);
        } catch (\Exception $e) {
            log_message('error', 'Process error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());

            // ✅ CLEANUP: Hapus file meskipun error
            // if (!empty($fileIds)) {
            //     $this->cleanupFiles($fileIds);
            // }

            return $this->response->setJSON([
                'success' => false,
                'error' => 'Gagal memproses file: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    protected function extractJson($text)
    {
        preg_match('/\[(.*)\]/s', $text, $matches);
        return $matches[0] ?? null;
    }

    /**
     * Get list uploaded files (optional - untuk debug)
     */
    public function listFiles()
    {
        try {
            $uploadPath = WRITEPATH . 'uploads/';

            if (!is_dir($uploadPath)) {
                return $this->response->setJSON([
                    'success' => true,
                    'files' => []
                ]);
            }

            $files = array_diff(scandir($uploadPath), ['.', '..']);

            $fileList = [];
            foreach ($files as $file) {
                $filePath = $uploadPath . $file;

                if (is_file($filePath)) {
                    $fileList[] = [
                        'file_id' => $file,
                        'size' => filesize($filePath),
                        'extension' => pathinfo($file, PATHINFO_EXTENSION),
                        'uploaded_at' => date('Y-m-d H:i:s', filemtime($filePath))
                    ];
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'files' => $fileList,
                'total' => count($fileList)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'List files error: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Delete specific file (optional)
     */
    public function deleteFile($fileId = null)
    {
        try {
            if (!$fileId) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'file_id diperlukan'
                ])->setStatusCode(400);
            }

            $filePath = WRITEPATH . 'uploads/' . $fileId;

            if (!file_exists($filePath)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'File tidak ditemukan'
                ])->setStatusCode(404);
            }

            if (unlink($filePath)) {
                log_message('info', 'File deleted manually: ' . $fileId);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'File berhasil dihapus'
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'error' => 'Gagal menghapus file'
            ])->setStatusCode(500);
        } catch (\Exception $e) {
            log_message('error', 'Delete file error: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Helper: Cleanup files
     */
    private function cleanupFiles(array $fileIds)
    {
        $uploadPath = WRITEPATH . 'uploads/';
        $deletedCount = 0;
        $fileCount = count($fileIds);

        foreach ($fileIds as $fileId) {
            $path = $uploadPath . $fileId;

            if (file_exists($path)) {
                if (unlink($path)) {
                    $deletedCount++;
                    log_message('info', 'File deleted: ' . $fileId);
                } else {
                    log_message('warning', 'Failed to delete file: ' . $fileId);
                }
            }
        }

        log_message('info', "Cleanup completed. Files deleted: {$deletedCount}/{$fileCount}");
    }

    /**
     * API: Cleanup files
     */
    public function cleanupFilesBackup()
    {
        $json = $this->request->getJSON(true);
        $fileIds = $json['file_ids'];

        $uploadPath = WRITEPATH . 'uploads/';

        foreach ($fileIds as $fileId) {
            $path = $uploadPath . $fileId;

            if (file_exists($path)) {
                if (unlink($path)) {
                    log_message('info', 'File deleted: ' . $fileId);
                } else {
                    log_message('warning', 'Failed to delete file: ' . $fileId);
                }
            }
        }

        log_message('info', "Cleanup completed.");
    }
}
