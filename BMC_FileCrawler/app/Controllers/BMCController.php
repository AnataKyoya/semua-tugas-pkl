<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\Gemini;
use App\Libraries\HarmBlockThreshold;
use App\Libraries\HarmCategory;
use App\Libraries\OpenRouter;
use App\Libraries\SafetySetting;
use App\Models\BMCHasilModel;
use App\Models\BMCKnowladgeModel;
use App\Models\BMCUserModel;
use CodeIgniter\HTTP\ResponseInterface;

class BMCController extends BaseController
{
    protected $ai;
    protected $openrouter;
    protected $usermodelbmc;
    protected $hasilmodelbmc;
    protected $bmcknowladge;

    public function __construct()
    {
        $this->ai = new Gemini();

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

        $this->usermodelbmc = new BMCUserModel();
        $this->hasilmodelbmc = new BMCHasilModel();
        $this->bmcknowladge = new BMCKnowladgeModel();
    }

    public function index()
    {
        return view('bmc_index');
    }

    public function process()
    {
        try {
            $json = $this->request->getJSON(true);
            $fullFile = $json['text'];
            $mode = $json['mode'] ?? 'lokal';

            $instuksi_khusus = $mode === 'lokal' ? 'Berikan perhatian khusus pada aspek lokal Indonesia di setiap blok.' : 'Berikan perhatian khusus pada aspek ekspor di setiap blok.';

            $fullPrompt = <<<PROMPT
            $fullFile

            [ATURAN]
            1. Respon WAJIB berbahasa Indonesia (WAJIB)
            2. Respon WAJIB tanpa markdown format
            3. Respon tanpa pembuka dan penutup, HANYA HASIL YANG DIMINTA TANPA ADA HAL LAIN
            4. Respon dalam format JSON
            5. Singkat, Padat, Jelas

            Format:
            {"business_model_canvas_analysis":[{"blok":"value_block_name","data":["...","..."],"analisis_nyata":["...","..."],"saran_konkret":["...","..."]}, {...}],"kesimpulan_dan_rekomendasi_prioritas":["...","..."]}

            [INSTRUKSI]
            Anda adalah seorang konsultan bisnis yang ahli dalam model bisnis, khususnya dalam industri F&B dan produk konsumen. Tugas Anda adalah menganalisis Business Model Canvas (BMC) yang diberikan secara mendalam, dengan pendekatan praktis dan realistis.

            $instuksi_khusus
            1. Baca dan pahami BMC yang diberikan. BMC akan berisi 9 blok: Customer Segments, Value Propositions, Channels, Customer Relationships, Revenue Streams, Key Activities, Key Resources, Key Partnerships, Cost Structure. (Jika ada blok yang tidak disebut, tetap analisis berdasarkan data yang ada.)
            2. Analisis setiap blok dengan format (JSON):
                - Nama Blok: (misal: Channels)
                - Data di BMC: Ringkas poin-poin yang ada.
                - Analisis Nyata: Berikan komentar kritis tentang data tersebut. Identifikasi potensi masalah, ketidakcocokan antar blok, atau peluang yang terlewat. Gunakan sudut pandang eksekusi di lapangan, misalnya: biaya tersembunyi, perilaku konsumen, kendala logistik, dll.
                - Saran Konkret: Berikan rekomendasi praktis yang bisa langsung diterapkan. Sertakan contoh nyata, angka perkiraan, atau langkah-langkah spesifik.
            3. Perhatikan keterkaitan antar blok. Misalnya, apakah Revenue Streams mendukung Customer Segments? Apakah Key Activities cukup untuk menjalankan Value Propositions? Jika ada konflik atau ketidakharmonisan, soroti dan beri solusi.
            4. Akhiri dengan kesimpulan yang merangkum 3-5 prioritas rekomendasi utama yang paling berdampak.
            5. Gaya penulisan: Gunakan bahasa Indonesia yang lugas, profesional, namun mudah dipahami. Hindari jargon yang tidak perlu. Sertakan contoh konkret dan analogi jika membantu.

            [BMC YANG DIANALISIS]
            Customer Segments: ...
            Value Propositions: ...
            Channels: ...
            ... dst
            PROMPT;

            log_message('debug', 'Total prompt length: ' . strlen($fullPrompt));

            // Kirim ke AI
            $aiPrompt = [
                ["role" => "user", "content" => $fullPrompt]
            ];

            $response = $this->openrouter->complete($aiPrompt); // masukkin ke DB
            $json_decode = json_decode($response, true);

            log_message('debug', 'hasil: ' . json_encode($json_decode));

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->setJSON([
                    'error' => 'Invalid JSON format',
                    'raw' => $response
                ]);
            }

            return response()->setJSON([
                'res' => $json_decode
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Process error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
        }
    }

    public function stream_1()
    {
        try {
            $json = $this->request->getJSON(true);
            $fullFile = $json['text'];
            $mode = $json['mode'] ?? 'lokal';

            $instuksi_khusus = $mode === 'lokal' ? 'Berikan perhatian khusus pada aspek lokal Indonesia di setiap blok.' : 'Berikan perhatian khusus pada aspek ekspor di setiap blok.';

            $fullPrompt = <<<PROMPT
            $fullFile

            [ATURAN KETAT OUTPUT STREAMING]
            1. Respon WAJIB berbahasa Indonesia.
            2. Respon WAJIB tanpa markdown.
            3. Respon TANPA pembuka, TANPA penutup, TANPA penjelasan.
            5. Setiap output HARUS berupa SATU objek JSON utuh per kiriman.
            6. DILARANG menggabungkan beberapa blok dalam satu objek.
            7. DILARANG memecah satu objek JSON menjadi beberapa bagian.
            8. Setelah seluruh blok selesai, kirim {"type":"done"}.
            9. Singkat, padat, jelas.

            [FORMAT STREAMING PER UNIT LOGIS]
            Kirim pertama:
            ###JSON_START###
            {"type":"kesimpulan","data":["...","..."]}
            ###JSON_END###

            Kemudian untuk SETIAP blok Business Model Canvas kirim satu per satu:
            ###JSON_START###
            {"type":"blok","blok":"value_block_name","data":["...","..."],"analisis_nyata":["...","..."],"saran_konkret":["...","..."]}
            ###JSON_END###

            Setelah semua blok selesai:
            ###JSON_START###
            {"type":"done"}
            ###JSON_END###

            [INSTRUKSI]
            Anda adalah seorang konsultan bisnis yang ahli dalam model bisnis, khususnya dalam industri F&B dan produk konsumen. Tugas Anda adalah menganalisis Business Model Canvas (BMC) yang diberikan secara mendalam, dengan pendekatan praktis dan realistis.

            $instuksi_khusus
            1. Baca dan pahami BMC yang diberikan. BMC akan berisi 9 blok: Customer Segments, Value Propositions, Channels, Customer Relationships, Revenue Streams, Key Activities, Key Resources, Key Partnerships, Cost Structure. (Jika ada blok yang tidak disebut, tetap analisis berdasarkan data yang ada.)
            2. Analisis setiap blok dengan format (JSON):
                - Nama Blok: (misal: Channels)
                - Data di BMC: Ringkas poin-poin yang ada.
                - Analisis Nyata: Berikan komentar kritis tentang data tersebut. Identifikasi potensi masalah, ketidakcocokan antar blok, atau peluang yang terlewat. Gunakan sudut pandang eksekusi di lapangan, misalnya: biaya tersembunyi, perilaku konsumen, kendala logistik, dll.
                - Saran Konkret: Berikan rekomendasi praktis yang bisa langsung diterapkan. Sertakan contoh nyata, angka perkiraan, atau langkah-langkah spesifik.
            3. Perhatikan keterkaitan antar blok. Misalnya, apakah Revenue Streams mendukung Customer Segments? Apakah Key Activities cukup untuk menjalankan Value Propositions? Jika ada konflik atau ketidakharmonisan, soroti dan beri solusi.
            4. Akhiri dengan kesimpulan yang merangkum 3-5 prioritas rekomendasi utama yang paling berdampak.
            5. Gaya penulisan: Gunakan bahasa Indonesia yang lugas, profesional, namun mudah dipahami. Hindari jargon yang tidak perlu. Sertakan contoh konkret dan analogi jika membantu.

            [BMC YANG DIANALISIS]
            Customer Segments: ...
            Value Propositions: ...
            Channels: ...
            ... dst
            PROMPT;

            log_message('debug', 'Total prompt length: ' . strlen($fullPrompt));

            header('Content-Type: text/plain');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no');

            while (ob_get_level() > 0) {
                ob_end_flush();
            }
            ob_implicit_flush(true);

            $aiPrompt = [
                ["role" => "user", "content" => $fullPrompt]
            ];

            $buffer = '';

            $this->openrouter->stream($aiPrompt, function ($chunk, $done) use (&$buffer) {

                if (!empty($chunk)) {

                    // 1️⃣ RAW typing effect
                    echo "RAW:" . $chunk . "\n";
                    flush();

                    // 2️⃣ Tambahkan ke buffer
                    $buffer .= $chunk;

                    // 3️⃣ Proses semua JSON yang sudah lengkap
                    while (true) {

                        $startPos = strpos($buffer, '###JSON_START###');
                        $endPos   = strpos($buffer, '###JSON_END###');

                        if ($startPos === false || $endPos === false) {
                            break;
                        }

                        if ($endPos < $startPos) {
                            // Cleanup jika format rusak
                            $buffer = substr($buffer, $endPos + strlen('###JSON_END###'));
                            continue;
                        }

                        $jsonBlock = substr(
                            $buffer,
                            $startPos + strlen('###JSON_START###'),
                            $endPos - ($startPos + strlen('###JSON_START###'))
                        );

                        $jsonBlock = trim($jsonBlock);
                        $decoded = json_decode($jsonBlock, true);

                        if (json_last_error() === JSON_ERROR_NONE) {
                            echo "JSON:" . $jsonBlock . "\n";
                            flush();
                        }

                        // Hapus hanya bagian yang sudah diproses
                        $buffer = substr($buffer, $endPos + strlen('###JSON_END###'));
                    }
                }

                if ($done) {
                    echo "DONE\n";
                    flush();
                }
            });
        } catch (\Exception $e) {
            log_message('error', 'Process error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
        }
    }

    public function stream_2()
    {
        try {
            $json = $this->request->getJSON(true);
            $fullFile = $json['text'] ?? '';
            $mode = in_array($json['mode'] ?? '', ['lokal', 'ekspor']) ? $json['mode'] : 'lokal';

            if (empty($fullFile)) {
                http_response_code(400);
                echo "ERROR:Input teks tidak boleh kosong\n";
                return;
            }

            // Batasi ukuran input
            if (strlen($fullFile) > 200000) {
                $fullFile = substr($fullFile, 0, 200000);
            }

            $instuksi_khusus = $mode === 'lokal'
                ? 'perhatian khusus pada aspek lokal Indonesia di setiap blok.'
                : 'perhatian khusus pada aspek ekspor di setiap blok.';

            $fullPrompt = <<<PROMPT
[SYSTEM INSTRUCTION]
Anda adalah seorang Strategic Business Auditor senior. Tugas Anda bukan sekadar merangkum isi Business Model Canvas (BMC), melainkan melakukan audit mendalam untuk menemukan keretakan logis dan potensi kegagalan sistemik dalam model bisnis tersebut.

[ATURAN KETAT OUTPUT STREAMING]
1. Respon WAJIB berbahasa Indonesia profesional.
2. Respon WAJIB tanpa markdown (Dilarang menggunakan **, #, atau ` di luar struktur JSON).
3. Respon TANPA kata pembuka, TANPA kata penutup.
4. Setiap output HARUS berupa SATU objek JSON utuh per unit diapit oleh ###JSON_START### dan ###JSON_END###.
5. Urutan analisis blok WAJIB: (1) Value Proposition, (2) Customer Segments, (3) Channels, (4) Customer Relationships, (5) Revenue Streams, (6) Key Resources, (7) Key Activities, (8) Key Partnerships, (9) Cost Structure.

[CORE KNOWLEDGE BASE: AUDIT REALITAS & SINKRONISASI]
Gunakan parameter ini untuk membongkar model bisnis:
1. Logical Missing Link: Jika Blok X menjanjikan A, namun Blok Y tidak menyediakan alat/biaya/aktivitas untuk A, tandai sebagai "Ketimpangan".
2. Operational Paradox: Strategi yang terlihat bagus di kertas tapi mustahil dilakukan bersamaan (Contoh: Kualitas mewah tapi biaya operasional dipangkas habis).
3. Cannibalization & Friction: Apakah satu blok justru merusak blok lain? (Contoh: Channel pihak ketiga yang komisinya memakan seluruh Revenue Stream).
4. Scale Fragility: Apakah model ini akan hancur jika permintaan naik 10x lipat?

[INSTRUKSI KHUSUS ANALISIS]
1. Dilarang Memberi Pujian Basa-Basi: Hanya berikan pujian jika ada harmoni yang sangat kuat dan masuk akal secara ekonomi lapangan. Jika standar saja, tidak perlu dipuji.
2. Audit Ketimpangan (Conflict Finder): Gunakan formula: "Di blok [Nama Blok] terdapat [Isi], namun di blok [Nama Blok Lain] tidak ditemukan [Pendukung/Kesesuaian], sehingga terjadi ketimpangan berupa..."
3. Analisis Lapangan: Gunakan database pengetahuan nyata tentang kegagalan bisnis yang sering terjadi.
4. Utamakan {$instuksi_khusus}

[FORMAT OUTPUT JSON]
Tahap 1: Kesimpulan Strategis
###JSON_START###
{"type":"kesimpulan","data":["Poin kritis kegagalan sistemik","Titik terlemah dalam model bisnis","Potensi keberhasilan jika ada"]}
###JSON_END###

Tahap 2: Analisis Per Blok (9 Blok Berurutan)
Gunakan format array untuk data, analisis_blok, dan saran guna kebutuhan rendering poin-per-poin.
###JSON_START###
{"type":"blok","blok":"nama_blok","data":["Daftar poin asli"],"analisis_blok":["Identifikasi ketimpangan antar blok (Contoh: Di blok A ada X tapi di blok B tidak ada Y)","Kritik tajam realitas lapangan","Pujian (Hanya jika benar-benar kuat/solid)"],"saran":["Langkah konkret menutup ketimpangan","Perbaikan struktur agar logis secara bisnis"]}
###JSON_END###

Tahap 3: Selesai
###JSON_START###
{"type":"done"}
###JSON_END###

[DATA BMC UNTUK DIANALISIS]
{$fullFile}
PROMPT;

            // Set headers untuk streaming
            header('Content-Type: text/plain; charset=utf-8');
            header('Cache-Control: no-cache');
            header('X-Accel-Buffering: no'); // Matikan buffering Nginx

            while (ob_get_level() > 0) {
                ob_end_clean(); // clean, bukan flush — hindari output prematur
            }

            set_time_limit(0);

            $aiPrompt = [
                ["role" => "user", "content" => $fullPrompt]
            ];

            $buffer = '';
            $MAX_BUFFER = 500000;

            $this->openrouter->stream($aiPrompt, function ($chunk, $done) use (&$buffer, $MAX_BUFFER) {

                if (!empty($chunk)) {
                    // Kirim RAW untuk efek typing
                    echo "RAW:" . str_replace("\n", " ", $chunk) . "\n";
                    flush();

                    $buffer .= $chunk;

                    // Cegah buffer overflow
                    if (strlen($buffer) > $MAX_BUFFER) {
                        $lastEnd = strrpos($buffer, '###JSON_END###');
                        if ($lastEnd !== false) {
                            $buffer = substr($buffer, $lastEnd + strlen('###JSON_END###'));
                        } else {
                            $buffer = substr($buffer, -$MAX_BUFFER);
                        }
                    }

                    // Proses semua JSON unit yang sudah lengkap
                    $this->processJsonUnits($buffer);
                }

                if ($done) {
                    // Proses sisa buffer jika masih ada
                    $this->processJsonUnits($buffer, true);
                    echo "DONE\n";
                    flush();
                }
            });
        } catch (\Exception $e) {
            log_message('error', 'Stream error: ' . $e->getMessage());
            echo "ERROR:" . $e->getMessage() . "\n";
            flush();
        }
    }

    public function stream()
    {
        try {
            $json     = $this->request->getJSON(true);
            $fullFile = $json['text'] ?? '';
            $user_id = $json['xyz_abc'] ?? '';
            $file_name = $json['file_name'] ?? '';
            $mode     = in_array($json['mode'] ?? '', ['lokal', 'ekspor']) ? $json['mode'] : 'lokal';

            if (empty($fullFile)) {
                http_response_code(400);
                echo "ERROR:Input teks tidak boleh kosong\n";
                return;
            }

            if (strlen($fullFile) > 200000) {
                $fullFile = substr($fullFile, 0, 200000);
            }

            $instuksi_khusus = $mode === 'lokal'
                ? 'perhatian khusus pada aspek lokal Indonesia di setiap blok.'
                : 'perhatian khusus pada aspek ekspor di setiap blok.';

            $knowladge = $this->bmcknowladge->select('pengetahuan')->first();

            $fullPrompt = <<<PROMPT
[SYSTEM INSTRUCTION]
Anda adalah seorang Strategic Business Auditor senior. Tugas Anda bukan sekadar merangkum isi Business Model Canvas (BMC), melainkan melakukan audit mendalam untuk menemukan keretakan logis dan potensi kegagalan sistemik dalam model bisnis tersebut.

[ATURAN KETAT OUTPUT STREAMING]
1. Respon WAJIB berbahasa Indonesia profesional.
2. Respon WAJIB tanpa markdown (Dilarang menggunakan **, #, atau ` di luar struktur JSON).
3. Respon TANPA kata pembuka, TANPA kata penutup.
4. Setiap output HARUS berupa SATU objek JSON utuh per unit diapit oleh ###JSON_START### dan ###JSON_END###.
5. Urutan analisis blok WAJIB: (1) Value Proposition, (2) Customer Segments, (3) Channels, (4) Customer Relationships, (5) Revenue Streams, (6) Key Resources, (7) Key Activities, (8) Key Partnerships, (9) Cost Structure.

[CORE KNOWLEDGE BASE: AUDIT REALITAS & SINKRONISASI]
Gunakan parameter ini untuk membongkar model bisnis:
{$knowladge['pengetahuan']}

[INSTRUKSI KHUSUS ANALISIS]
1. Dilarang Memberi Pujian Basa-Basi: Hanya berikan pujian jika ada harmoni yang sangat kuat.
2. Audit Ketimpangan: "Di blok [X] terdapat [Isi], namun di blok [Y] tidak ditemukan [Pendukung]..."
3. Analisis Lapangan: Gunakan database pengetahuan nyata tentang kegagalan bisnis.
4. Utamakan {$instuksi_khusus}

[FORMAT OUTPUT JSON]
Tahap 1: Kesimpulan Strategis
###JSON_START###
{"type":"kesimpulan","data":["Poin kritis","Titik terlemah","Potensi keberhasilan"]}
###JSON_END###

Tahap 2: Analisis Per Blok (9 Blok Berurutan)
Gunakan format array untuk data, analisis_blok, dan saran guna kebutuhan rendering poin-per-poin.
###JSON_START###
{"type":"blok","blok":"nama_blok","data":["Daftar poin asli"],"analisis_blok":["Analisis 1", "Analisis 2", ...],"saran":["Saran 1", "Saran 2", ...]}
###JSON_END###

Tahap 3: Selesai
###JSON_START###
{"type":"done"}
###JSON_END###

[DATA BMC UNTUK DIANALISIS]
{$fullFile}
PROMPT;

            header('Content-Type: text/plain; charset=utf-8');
            header('Cache-Control: no-cache');
            header('X-Accel-Buffering: no');

            while (ob_get_level() > 0) ob_end_clean();
            set_time_limit(0);

            $aiPrompt = [["role" => "user", "content" => $fullPrompt]];

            $buffer     = '';
            $MAX_BUFFER = 500000;

            // Akumulator — persis struktur analysisData di frontend
            $collectedObjects = []; // array of decoded objects

            $this->openrouter->stream(
                $aiPrompt,
                function ($chunk, $done) use (&$buffer, &$collectedObjects, $MAX_BUFFER, $mode, $user_id, $file_name) {

                    if (!empty($chunk)) {
                        echo "RAW:" . str_replace("\n", " ", $chunk) . "\n";
                        flush();

                        $buffer .= $chunk;

                        if (strlen($buffer) > $MAX_BUFFER) {
                            $lastEnd = strrpos($buffer, '###JSON_END###');
                            $buffer  = $lastEnd !== false
                                ? substr($buffer, $lastEnd + strlen('###JSON_END###'))
                                : substr($buffer, -$MAX_BUFFER);
                        }

                        $this->processJsonUnits($buffer, false, $collectedObjects);
                    }

                    if ($done) {
                        $this->processJsonUnits($buffer, true, $collectedObjects);

                        // ── Simpan ke DB ──────────────────────────────────────
                        try {
                            $exist = $this->hasilmodelbmc->where('user_id', $user_id)->first();

                            if ($exist) {
                                $this->hasilmodelbmc->update($exist['id'], [
                                    'mode' => $mode,
                                    'hasil' => json_encode($collectedObjects, JSON_UNESCAPED_UNICODE),
                                ]);
                            } else {
                                $this->hasilmodelbmc->insert([
                                    'user_id' => $user_id,
                                    'mode' => $mode,
                                    'hasil' => json_encode($collectedObjects, JSON_UNESCAPED_UNICODE),
                                ]);
                            }

                            log_message('info', "BMC saved.");

                            echo "SAVED\n";
                            flush();
                        } catch (\Exception $e) {
                            log_message('error', 'DB save error: ' . $e->getMessage());
                            // Stream tetap lanjut, jangan abort
                        }
                        // ─────────────────────────────────────────────────────

                        echo "DONE\n";
                        flush();
                    }
                }
            );
        } catch (\Exception $e) {
            log_message('error', 'Stream error: ' . $e->getMessage());
            echo "ERROR:" . $e->getMessage() . "\n";
            flush();
        }
    }

    public function getResult(string $user_id)
    {
        try {
            $row = $this->hasilmodelbmc->where('user_id', $user_id)->first();

            if (!$row) {
                return $this->response->setJSON([
                    'status'  => 'not-found',
                    'message' => 'Ayo mulai analisa business model canvas kamu!',
                ]);
            }

            $decoded = json_decode($row['hasil'], true);

            return $this->response->setJSON([
                'status'     => 'ok',
                'id'         => (int) $row['id'],
                'mode'       => $row['mode'],
                'created_at' => $row['created_at'],
                // Langsung bisa dipakai: analysisData = response.data
                'data'       => $decoded,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'getResult error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function processJsonUnits_2(string &$buffer, bool $isFinal = false, array &$collected = []): void
    {
        $startDelimiter = '###JSON_START###';
        $endDelimiter   = '###JSON_END###';

        while (true) {
            $startPos = strpos($buffer, $startDelimiter);
            $endPos   = strpos($buffer, $endDelimiter);

            if ($startPos === false || $endPos === false) break;

            if ($endPos < $startPos) {
                $buffer = substr($buffer, $endPos + strlen($endDelimiter));
                continue;
            }

            $jsonStart = $startPos + strlen($startDelimiter);
            $jsonStr   = trim(substr($buffer, $jsonStart, $endPos - $jsonStart));
            $buffer    = substr($buffer, $endPos + strlen($endDelimiter));

            $obj = json_decode($jsonStr, true);
            if (json_last_error() !== JSON_ERROR_NONE || empty($obj['type'])) continue;

            // Kirim ke frontend
            echo "JSON:" . json_encode($obj, JSON_UNESCAPED_UNICODE) . "\n";
            flush();

            // Kumpulkan (skip type:done, tidak perlu disimpan)
            if ($obj['type'] !== 'done') {
                $collected[] = $obj;
            }
        }
    }

    /**
     * Ekstrak dan kirim semua JSON unit lengkap dari buffer.
     * Buffer di-mutate by reference — bagian yang sudah diproses dihapus.
     */
    private function processJsonUnits(string &$buffer, bool $force = false, array &$collected = []): void
    {
        $startDelimiter = '###JSON_START###';
        $endDelimiter   = '###JSON_END###';

        while (true) {
            $startPos = strpos($buffer, $startDelimiter);
            $endPos   = strpos($buffer, $endDelimiter);

            // Belum ada unit lengkap
            if ($startPos === false || $endPos === false) {
                break;
            }

            // Format rusak: end sebelum start
            if ($endPos < $startPos) {
                $buffer = substr($buffer, $endPos + strlen($endDelimiter));
                continue;
            }

            $jsonStart  = $startPos + strlen($startDelimiter);
            $jsonBlock  = trim(substr($buffer, $jsonStart, $endPos - $jsonStart));
            $decoded    = json_decode($jsonBlock, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                // Kirim JSON yang sudah valid dan lengkap
                echo "JSON:" . json_encode($decoded) . "\n";
                flush();
            } else {
                log_message('warning', 'Invalid JSON block skipped: ' . $jsonBlock);
            }

            // Hapus unit yang sudah diproses dari buffer
            $buffer = substr($buffer, $endPos + strlen($endDelimiter));

            // Kumpulkan (skip type:done, tidak perlu disimpan)
            if ($decoded['type'] !== 'done') {
                $collected[] = $decoded;
            }
        }
    }
}
