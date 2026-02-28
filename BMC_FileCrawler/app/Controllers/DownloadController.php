<?php

namespace App\Controllers;

use App\Models\ScrapeModel;
use CodeIgniter\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use TCPDF;

class DownloadController extends Controller
{
    /**
     * Download file dalam berbagai format
     */
    public function downloadAs($name, $ext)
    {
        // VALIDASI AWAL - SEBELUM MEMULAI DOWNLOAD
        try {
            $jsonData = $this->getJSONData($name);

            if (empty($jsonData)) {
                return redirect()->back()->with('error', 'Tidak ada data untuk didownload');
            }

            $data = json_decode($jsonData, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()->back()->with('error', 'Invalid JSON data: ' . json_last_error_msg());
            }

            if (!is_array($data)) {
                $data = [$data];
            }

            if (empty($data)) {
                return redirect()->back()->with('error', 'Data kosong');
            }

            // Sanitize filename
            $fileName = $this->sanitizeFileName($name);

            // Download berdasarkan extension
            // CATATAN: Method download akan exit, jadi tidak akan return ke sini
            switch (strtolower($ext)) {
                case 'pdf':
                    $this->downloadAsPDF($data, $fileName);
                    break;

                case 'xlsx':
                    $this->downloadAsExcel($data, $fileName);
                    break;

                case 'docx':
                    $this->downloadAsWord($data, $fileName);
                    break;

                case 'json':
                    $this->downloadAsJSON($data, $fileName);
                    break;

                default:
                    return redirect()->back()->with('error', 'Format tidak didukung');
            }
        } catch (\Exception $e) {
            log_message('error', 'Download error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());

            if (!headers_sent()) {
                return redirect()->back()->with('error', 'Gagal mendownload file: ' . $e->getMessage());
            } else {
                die('Download error: ' . $e->getMessage());
            }
        }
    }

    /**
     * Get JSON data from database
     */
    private function getJSONData($name): string
    {
        // SESUAIKAN DENGAN STRUKTUR DATABASE ANDA!
        $modelScrape = new ScrapeModel();
        $hasil = $modelScrape->where('nama', $name)->select('hasil')->first();
        return $hasil['hasil'];

        // Untuk testing (hardcoded)
        // return '[
        //     {
        //         "nama_perusahaan": "KELOMPOK TANI BINA WARGA BINA TANI",
        //         "alamat": "Kel.: Sadeng, Kec.: Leuwisadeng, Kab./Kota: Bogor, 16640",
        //         "telepon": "-",
        //         "email": "-",
        //         "website": "-",
        //         "instagram": "-",
        //         "whatsapp": "-",
        //         "youtube": "-",
        //         "jenis_komoditas": "Padi, Palawija"
        //     },
        //     {
        //         "nama_perusahaan": "PT MAJU BERSAMA SUKSES",
        //         "alamat": "Jl. Sudirman No. 123, Jakarta Selatan, DKI Jakarta, 12190",
        //         "telepon": "021-12345678",
        //         "email": "info@majubersama.com",
        //         "website": "www.majubersama.com",
        //         "instagram": "@majubersama",
        //         "whatsapp": "081234567890",
        //         "youtube": "Maju Bersama Official",
        //         "jenis_komoditas": "Perdagangan Umum"
        //     },
        //     {
        //         "nama_perusahaan": "CV BERKAH SENTOSA JAYA",
        //         "alamat": "Jl. Ahmad Yani No. 45, Bandung, Jawa Barat, 40132",
        //         "telepon": "022-87654321",
        //         "email": "contact@berkahsentosa.co.id",
        //         "website": "www.berkahsentosa.co.id",
        //         "instagram": "@berkahsentosa",
        //         "whatsapp": "082345678901",
        //         "youtube": "Berkah Sentosa Channel",
        //         "jenis_komoditas": "Tekstil dan Garmen"
        //     }
        // ]';
    }

    /**
     * Get headers dari JSON keys
     * Mengubah snake_case menjadi Title Case
     */
    private function getHeaders(array $data): array
    {
        if (empty($data)) {
            return [];
        }

        // Ambil keys dari item pertama
        $keys = array_keys($data[0]);

        // Convert ke Title Case
        $headers = [];
        foreach ($keys as $key) {
            $headers[$key] = $this->keyToLabel($key);
        }

        return $headers;
    }

    /**
     * Convert key (snake_case) ke label (Title Case)
     */
    private function keyToLabel(string $key): string
    {
        // Replace underscore dengan space
        $label = str_replace('_', ' ', $key);

        // Uppercase first letter of each word
        $label = ucwords($label);

        return $label;
    }

    /**
     * Download sebagai PDF dengan TCPDF
     */
    private function downloadAsPDF(array $data, string $fileName)
    {
        // Get headers dari JSON keys
        $headers = $this->getHeaders($data);

        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('Your Application');
        $pdf->SetAuthor('Your Company');
        $pdf->SetTitle('Data Export');
        $pdf->SetSubject('Data List');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);

        // Add page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 10);

        // Title
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 12, strtoupper($fileName), 0, 1, 'C');
        $pdf->Ln(3);

        // Horizontal line
        $pdf->SetDrawColor(50, 50, 50);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(8);

        // List data
        $pdf->SetFont('helvetica', '', 10);

        foreach ($data as $index => $item) {
            // Check if need new page
            if ($pdf->GetY() > 250) {
                $pdf->AddPage();
            }

            // Item number (jika ada field pertama yang bisa dijadikan title)
            $firstKey = array_key_first($item);
            $firstValue = $item[$firstKey] ?? '';

            $pdf->SetFont('helvetica', 'B', 13);
            $pdf->SetTextColor(30, 30, 30);
            $pdf->Cell(0, 8, ($index + 1) . '. ' . $firstValue, 0, 1, 'L');

            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(60, 60, 60);

            // Details
            $x = 20; // Indentation
            $skipFirst = false;

            foreach ($item as $key => $value) {
                // Skip first item (sudah dipakai sebagai title)
                if (!$skipFirst) {
                    $skipFirst = true;
                    continue;
                }

                if (!empty($value) && $value !== '-') {
                    $pdf->SetX($x);

                    // Label (bold)
                    $pdf->SetFont('helvetica', 'B', 10);
                    $pdf->Cell(45, 6, $headers[$key], 0, 0, 'L');

                    // Colon
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->Cell(5, 6, ':', 0, 0, 'C');

                    // Value (multiline if needed)
                    $pdf->MultiCell(0, 6, $value, 0, 'L');
                }
            }

            // Spacing between items
            $pdf->Ln(6);

            // Light separator line
            $pdf->SetDrawColor(200, 200, 200);
            $pdf->SetLineWidth(0.2);
            $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
            $pdf->Ln(6);
        }

        // Bersihkan output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Output PDF
        $pdf->Output($fileName . '.pdf', 'D');
        exit;
    }

    /**
     * Download sebagai Excel dengan PhpSpreadsheet
     */
    private function downloadAsExcel(array $data, string $fileName)
    {
        // Get headers dari JSON keys
        $headers = $this->getHeaders($data);
        $keys = array_keys($headers);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set sheet title
        $sheet->setTitle('Data Export');

        // Write headers
        $col = 1;
        foreach ($headers as $key => $label) {
            $cellAddress = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '1';
            $sheet->setCellValue($cellAddress, $label);
            $col++;
        }

        // Style headers
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];

        $sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray($headerStyle);

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Write data
        $row = 2;
        foreach ($data as $item) {
            $col = 1;
            foreach ($keys as $key) {
                $cellAddress = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
                $sheet->setCellValue($cellAddress, $item[$key] ?? '');
                $col++;
            }

            // Style data rows
            $dataStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP
                ]
            ];

            $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray($dataStyle);

            // Alternate row colors
            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F2F2F2');
            }

            $row++;
        }

        // Auto-size columns
        for ($i = 1; $i <= count($headers); $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Freeze header row
        $sheet->freezePane('A2');

        // Create Excel file
        $writer = new Xlsx($spreadsheet);

        // Bersihkan semua buffer
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Set headers
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        // Save dan exit
        $writer->save('php://output');
        exit;
    }

    /**
     * Download sebagai Word dengan PhpWord
     */
    private function downloadAsWord(array $data, string $fileName)
    {
        // Get headers dari JSON keys
        $headers = $this->getHeaders($data);

        $phpWord = new PhpWord();

        // Set document properties
        $properties = $phpWord->getDocInfo();
        $properties->setCreator('Your Application');
        $properties->setCompany('Your Company');
        $properties->setTitle(ucwords(str_replace('_', ' ', $fileName)));

        // Add section
        $section = $phpWord->addSection([
            'marginTop' => 1000,
            'marginBottom' => 1000,
            'marginLeft' => 1000,
            'marginRight' => 1000
        ]);

        // Title
        $section->addText(
            strtoupper($fileName),
            [
                'name' => 'Arial',
                'size' => 18,
                'bold' => true,
                'color' => '1F497D'
            ],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 300]
        );

        // Horizontal line
        $section->addLine([
            'weight' => 1,
            'width' => 450,
            'height' => 0,
            'color' => '1F497D'
        ]);

        $section->addTextBreak(1);

        // Style definitions
        $titleStyle = [
            'name' => 'Arial',
            'size' => 13,
            'bold' => true,
            'color' => '1F497D'
        ];

        $labelStyle = [
            'name' => 'Arial',
            'size' => 10,
            'bold' => true
        ];

        $valueStyle = [
            'name' => 'Arial',
            'size' => 10
        ];

        $paragraphStyle = ['spaceAfter' => 100];

        // List data
        foreach ($data as $index => $item) {
            // Title (first field)
            $firstKey = array_key_first($item);
            $firstValue = $item[$firstKey] ?? '';

            $section->addText(
                ($index + 1) . '. ' . $firstValue,
                $titleStyle,
                ['spaceAfter' => 200]
            );

            // Details
            $skipFirst = false;
            foreach ($item as $key => $value) {
                // Skip first item (sudah dipakai sebagai title)
                if (!$skipFirst) {
                    $skipFirst = true;
                    continue;
                }

                if (!empty($value) && $value !== '-') {
                    $textRun = $section->addTextRun($paragraphStyle);
                    $textRun->addText($headers[$key] . ': ', $labelStyle);
                    $textRun->addText($value, $valueStyle);
                }
            }

            // Spacing between items
            $section->addTextBreak(1);

            // Light separator
            if ($index < count($data) - 1) {
                $section->addLine([
                    'weight' => 0.5,
                    'width' => 450,
                    'height' => 0,
                    'color' => 'CCCCCC'
                ]);
                $section->addTextBreak(1);
            }
        }

        // Save to output
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');

        // Bersihkan output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Set headers
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $fileName . '.docx"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        // Save dan exit
        $objWriter->save('php://output');
        exit;
    }

    /**
     * Download sebagai JSON
     */
    private function downloadAsJSON(array $data, string $fileName)
    {
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Bersihkan output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Set headers
        header('Content-Type: application/json; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '.json"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public');

        // Output dan exit
        echo $jsonContent;
        exit;
    }

    /**
     * Sanitize filename
     */
    private function sanitizeFileName(string $fileName): string
    {
        $fileName = preg_replace('/\.[^.]+$/', '', $fileName);
        $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $fileName);
        $fileName = preg_replace('/_+/', '_', $fileName);
        $fileName = trim($fileName, '_');

        return $fileName ?: 'download';
    }

    /**
     * Test Excel - untuk memastikan library berfungsi
     */
    public function testExcel()
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->setCellValue('A1', 'TEST XLSX');

        $writer = new Xlsx($spreadsheet);

        // Bersihkan semua buffer
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="test.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
