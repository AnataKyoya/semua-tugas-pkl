<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Libraries/Crawler/PipelineParserNewNew.php';

use App\Libraries\Crawler\PipelineParserNewNew;
use Symfony\Component\DomCrawler\Crawler;

echo "\n\n" . str_repeat("=", 80) . "\n";
echo "DEBUG: :group vs Non-group Comparison\n";
echo str_repeat("=", 80) . "\n";

// SAME HTML for both tests
$html = <<<HTML
<div class="container">
    <div class="t h18">Alamat: Jl. Sudirman No. 123, Jakarta</div>
    <div class="t h18">-, Kel.: Kanoman, Kec.: Cibeber, Kab.\/Kota: Cianjur, 43262 Telp.: -, Faks.: -, E-mail: -, Website: -, Instagram: -, Whatsapp: -, Youtube: -</div>
    <div class="t h18">Instagram: @company, Youtube: CompanyChannel</div>
</div>
HTML;

$parser = new PipelineParserNewNew();
// $parser->setDebug(true);

echo "\n=== TEST 1: WITHOUT :group (Testing approach) ===\n";
$selectors1 = [
    'parent' => 'div.container',
    'fields' => [
        'telp' => 'div.t.h18 :contains("Telp")',  // NO :group!
    ]
];

$result1 = $parser->parse($html, $selectors1);
echo "Result: '" . ($result1[0]['telp'] ?? 'EMPTY') . "'\n";

echo "\n=== TEST 2: WITH :group (Your approach) ===\n";
$selectors2 = [
    'parent' => 'div.container',
    'fields' => [
        'telp' => 'div.t.h18 :group :contains("Telp")',  // WITH :group!
    ]
];

$result2 = $parser->parse($html, $selectors2);
echo "Result: '" . ($result2[0]['telp'] ?? 'EMPTY') . "'\n";

echo "\n=== ANALYSIS ===\n";

// Manual simulation
$crawler = new Crawler($html);
$elements = $crawler->filter('div.t.h18');

echo "Total div.t.h18 elements: " . $elements->count() . "\n\n";

$allTexts = [];
$elements->each(function ($el, $i) use (&$allTexts) {
    $text = trim($el->text());
    $allTexts[] = $text;
    echo "Element {$i}: '{$text}'\n";
});

echo "\nGrouped text (joined with space):\n";
$groupedText = implode(' ', $allTexts);
echo "'{$groupedText}'\n";

echo "\nSearching for 'Telp' in:\n";
echo "1. Individual elements:\n";
foreach ($allTexts as $i => $text) {
    $hasTelp = strpos($text, 'Telp') !== false ? 'YES' : 'NO';
    echo "  Element {$i}: {$hasTelp}\n";
}

echo "\n2. Grouped text:\n";
$hasTelpInGrouped = strpos($groupedText, 'Telp') !== false ? 'YES' : 'NO';
echo "  Grouped: {$hasTelpInGrouped} (at position: " . strpos($groupedText, 'Telp') . ")\n";

// Manual extractAfterLabel simulation
function testExtract($text, $label)
{
    echo "\nTesting extractAfterLabel on: '{$text}'\n";

    $lines = preg_split('/\r\n|\r|\n/', $text);

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        if (stripos($line, $label) === false) continue;

        echo "  Found in line: '{$line}'\n";

        $pattern = '/' . preg_quote($label, '/') . '\s*[:\-]?\s*([^,\n\r]+)/i';
        if (preg_match($pattern, $line, $matches)) {
            echo "  Pattern matched: '" . trim($matches[1]) . "'\n";
            return trim($matches[1]);
        }

        // Fallback
        $result = trim(preg_replace('/.*' . preg_quote($label, '/') . '\s*[:\-]?\s*/i', '', $line));
        echo "  Fallback result: '{$result}'\n";
        return $result;
    }

    return '';
}

echo "\n=== EXTRACT AFTER LABEL TEST ===\n";

// Test on element 1 (where Telp exists)
echo "\nOn Element 1 (contains Telp):\n";
testExtract($allTexts[1], 'Telp');

echo "\nOn Grouped text:\n";
testExtract($groupedText, 'Telp');
