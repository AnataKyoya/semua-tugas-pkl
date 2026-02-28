<?php

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../app/Libraries/Crawler/PipelineParserNew.php';

use App\Libraries\Crawler\PipelineParserNew;


// ========================================
// TEST HTML SAMPLES
// ========================================

// Test Case 1: Contoh HTML dari user - flat divs dengan class yang sama
$html1 = <<<HTML
<div class="container">
    <div class="t m0 x26 h18 y290 ffd fs10 fc1 sc0 ls0 ws2">–, Kel.: Sadeng, Kec.: Leuwisadeng, Kab./K<span class="_ _0"></span>ota: Bogor<span class="_ _4"></span>, 16640</div>
    <div class="t m0 x26 h18 y291 ffd fs10 fc1 sc0 ls0 ws2">T<span class="_ _3"></span>elp.: –, Faks.: –, <span class="fff">E-mail</span>: –, <span class="fff">Website</span>: –, <span class="fff">Instagr<span class="_ _0"></span>am<span class="ffd">: –, </span>Whatsapp<span class="ffd">: </span></span></div>
    <div class="t m0 x26 h18 y292 ffd fs10 fc1 sc0 ls0 ws2">–, <span class="fff">Y<span class="_ _4"></span>outube<span class="ffd">: –</span></span></div>
</div>
HTML;

// Test Case 2: Contoh HTML untuk table (dari config user)
$html2 = <<<HTML
<table id="newspaper-a">
    <tbody>
        <tr valign="top">
            <td>1</td>
            <td>
                <div>PT Elecomp Indonesia</div>
                <div>Jl. Gatot Subroto No. 123</div>
                <div>Telp. 021-12345678</div>
                <div>e-Mail: info@elecomp.co.id</div>
                <div>Website: www.elecomp.co.id</div>
            </td>
            <td>Elektronik</td>
            <td>Perdagangan</td>
        </tr>
        <tr valign="top">
            <td>2</td>
            <td>
                <div>PT Tech Solutions</div>
                <div>Jl. Sudirman No. 456</div>
                <div>Telp. 021-87654321</div>
                <div>e-Mail: contact@techsol.com</div>
            </td>
            <td>IT Services</td>
            <td>Jasa</td>
        </tr>
    </tbody>
</table>
HTML;

// Test Case 3: Multiple divs with h22 and h21 classes
$html3 = <<<HTML
<div class="wrapper">
    <div class="t h22" style="background: #f8fafc;">Background Circle</div>
    <div style="background: #000000;">Black Line</div>
    <div style="background: #e2e8f0;">Dot 1</div>
    <div style="background: #e2e8f0;">Dot 2</div>
    <div style="background: #e2e8f0;">Dot 3</div>
    <div style="background: #6366f1;">Dot 4</div>
    <div class="t h21" style="font-weight: bold;">PT Elecomp Indonesia</div>
</div>
HTML;

// ========================================
// TEST FUNCTION
// ========================================

function test($html, $selector, $testName)
{
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "TEST: $testName\n";
    echo "SELECTOR: $selector\n";
    echo str_repeat("-", 80) . "\n";

    $parser = new PipelineParserNew();

    // Simulate parsing with a dummy parent
    $selectors = [
        'parent' => 'body > *',
        'fields' => [
            'result' => $selector
        ]
    ];

    try {
        $result = $parser->parse($html, $selectors);

        if (!empty($result)) {
            echo "RESULT: " . ($result[0]['result'] ?? 'EMPTY') . "\n";
        } else {
            echo "RESULT: NO MATCH\n";
        }
    } catch (\Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

// ========================================
// RUN TESTS
// ========================================

echo "\n\n";
echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                     TESTING :group FEATURE                                 ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n";

// Test 1: Basic :group - menggabungkan semua div dengan class h18
test($html1, 'div.h18 :group', 'Test 1: Basic :group');

// Test 2: :group + :text(1) - ambil bagian pertama setelah di-group
test($html1, 'div.h18 :group :text(1)', 'Test 2: :group + :text(1)');

// Test 3: :group + :text(2) - ambil bagian kedua setelah di-group
test($html1, 'div.h18 :group :text(2)', 'Test 3: :group + :text(2)');

// Test 4: :group + :contains("Telp") - cari yang mengandung "Telp" dari hasil group
test($html1, 'div.h18 :group :contains("Telp")', 'Test 4: :group + :contains("Telp")');

// Test 5: :group + :text(2) + :contains("Telp") - ambil text ke-2, lalu extract setelah "Telp"
test($html1, 'div.h18 :group :text(2) :contains("Telp")', 'Test 5: :group + :text(2) + :contains("Telp")');

// Test 6: Table example - group all divs in td
test($html2, 'table#newspaper-a tbody tr[valign="top"] td:nth-child(2) div :group', 'Test 6: Group table cell content');

// Test 7: Table with :text(1)
test($html2, 'table#newspaper-a tbody tr[valign="top"] td:nth-child(2) div :group :text(1)', 'Test 7: Table :group :text(1)');

// Test 8: Table with :contains("Telp")
test($html2, 'table#newspaper-a tbody tr[valign="top"] td:nth-child(2) div :group :contains("Telp")', 'Test 8: Table :group :contains("Telp")');

// Test 9: Multiple classes grouping
test($html3, 'div :group', 'Test 9: Group all divs');

// Test 10: Group from h22 to h21
test($html3, 'div.t :group', 'Test 10: Group divs with class "t"');

echo "\n\n";
echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                          TESTS COMPLETED                                   ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
