<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Libraries/Crawler/PipelineParserNewNew.php';

use App\Libraries\Crawler\PipelineParserNewNew;
use Symfony\Component\DomCrawler\Crawler;

echo "\n\n" . str_repeat("=", 80) . "\n";
echo "TEST FOR YOUR DATA SPECIFICALLY\n";
echo str_repeat("=", 80) . "\n";

// YOUR ACTUAL DATA
$yourHtml = <<<HTML
<div class="container">
    <div class="t h22">Company Name</div>
    <div class="t h18">
        -, Kel.: Sadeng, Kec.: Leuwisadeng, Kab./Kota: Bogor, 16640<br>
        Telp.: -, Faks.: -, E-mail: -, Website: -, Instagram: -, Whatsapp: -<br>
        Youtube: -
    </div>
</div>
HTML;

$yourSelectors = [
    'parent' => 'div.container',
    'fields' => [
        "company" => "div.t.h22",
        "alamat" => "div.t.h18 :group :text(1)",
        "email" => "div.t.h18 :group :text(2) :contains('E-mail')",
        "all" => "div.t.h18 :group",
        // Tambahkan test untuk semua fields
        "telp" => "div.t.h18 :group :contains('Telp')",
        "faks" => "div.t.h18 :group :contains('Faks')",
        "website" => "div.t.h18 :group :contains('Website')",
        "instagram" => "div.t.h18 :group :contains('Instagram')",
        "whatsapp" => "div.t.h18 :group :contains('Whatsapp')",
        "youtube" => "div.t.h18 :group :contains('Youtube')",
    ]
];

$parser = new PipelineParserNewNew();
// $parser->setDebug(true); // AKTIFKAN DEBUG

$yourResult = $parser->parse($yourHtml, $yourSelectors);

echo "\n=== YOUR DATA RESULTS ===\n";
if (!empty($yourResult)) {
    print_r($yourResult[0]);
} else {
    echo "No results found!\n";
}

// ANALYZE THE PROBLEM
echo "\n=== DEBUG ANALYSIS ===\n";

$crawler = new Crawler($yourHtml);
$h18 = $crawler->filter('div.t.h18');

echo "1. Raw HTML:\n";
echo $h18->html() . "\n\n";

echo "2. Raw Text:\n";
echo $h18->text() . "\n\n";

echo "3. Split by <br>:\n";
$html = $h18->html();
$parts = explode('<br>', $html);
foreach ($parts as $i => $part) {
    $clean = trim(strip_tags($part));
    echo "[{$i}] '{$clean}'\n";
}

echo "\n4. Manual extraction test:\n";
// Coba extract manual
$line2 = trim(strip_tags($parts[1])); // "Telp.: -, Faks.: -, E-mail: -, Website: -, Instagram: -, Whatsapp: -"
echo "Line 2: '{$line2}'\n";

// Cari "E-mail"
if (preg_match('/E-mail\s*:\s*([^,]+)/i', $line2, $match)) {
    $emailValue = trim($match[1]);
    echo "Found E-mail: '{$emailValue}'\n";

    if ($emailValue === '-') {
        echo "WARNING: Email value is just a dash '-'\n";
        echo "Expected behavior: return empty string ''\n";
    }
} else {
    echo "ERROR: Could not find E-mail pattern\n";
}
