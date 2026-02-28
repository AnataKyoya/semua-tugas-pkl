<?php

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../app/Libraries/Crawler/PipelineParserNewNew.php';

use App\Libraries\Crawler\PipelineParserNewNew;

echo "\n\n";
echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                  TESTING FIXED FEATURES                                    ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n";

// ========================================
// FIX 1: :group :contains() extraction
// ========================================

echo "\n" . str_repeat("=", 80) . "\n";
echo "FIX 1: :group :contains() - Extract value after label correctly\n";
echo str_repeat("=", 80) . "\n";

$html1 = <<<HTML
<div class="container">
    <div class="t h18">Alamat: Jl. Sudirman No. 123, Jakarta</div>
    <div class="t h18">-, Kel.: Kanoman, Kec.: Cibeber, Kab.\/Kota: Cianjur, 43262 Telp.: -, Faks.: -, E-mail: -, Website: -, Instagram: -, Whatsapp: -, Youtube: -</div>
    <div class="t h18">Instagram: @company, Youtube: CompanyChannel</div>
</div>
HTML;

$selectors1 = [
    'parent' => 'div.container',
    'fields' => [
        'telp' => 'div.t.h18 :group :contains("Telp")',
        'faks' => 'div.t.h18 :group :contains("Faks")',
        'email' => 'div.t.h18 :group :contains("E-mail")',
        'website' => 'div.t.h18 :group :contains("Website")',
        'instagram' => 'div.t.h18 :group :contains("Instagram")',
        'youtube' => 'div.t.h18 :group :contains("Youtube")',
    ]
];

$parser = new PipelineParserNewNew();
$result1 = $parser->parse($html1, $selectors1);

echo "\nExpected Results:\n";
echo "  telp: 021-12345\n";
echo "  faks: 021-67890\n";
echo "  email: info@company.com\n";
echo "  website: www.company.com\n";
echo "  instagram: @company\n";
echo "  youtube: CompanyChannel\n";

echo "\nActual Results:\n";
if (!empty($result1)) {
    foreach ($result1[0] as $key => $value) {
        echo "  $key: $value\n";
    }

    // Validation
    $passed = true;
    $expected = [
        'telp' => '021-12345',
        'faks' => '021-67890',
        'email' => 'info@company.com',
        'website' => 'www.company.com',
        'instagram' => '@company',
        'youtube' => 'CompanyChannel'
    ];

    foreach ($expected as $key => $value) {
        if (($result1[0][$key] ?? '') !== $value) {
            $passed = false;
            echo "\n❌ FAILED: $key expected '$value', got '" . ($result1[0][$key] ?? '') . "'\n";
        }
    }

    if ($passed) {
        echo "\n✅ FIX 1: PASSED - All extractions correct!\n";
    }
} else {
    echo "  ERROR: No results\n";
}

// ========================================
// FIX 2: Group Config - Numeric
// ========================================

echo "\n\n" . str_repeat("=", 80) . "\n";
echo "FIX 2: Group Config - Numeric (Group every 3 elements)\n";
echo str_repeat("=", 80) . "\n";

$html2 = <<<HTML
<div class="wrapper">
    <div class="item">Item 1</div>
    <div class="item">Item 2</div>
    <div class="item">Item 3</div>
    <div class="item">Item 4</div>
    <div class="item">Item 5</div>
    <div class="item">Item 6</div>
    <div class="item">Item 7</div>
</div>
HTML;

$selectors2 = [
    'parent' => 'div.wrapper div.item',
    'group' => 3, // Group every 3 elements
    'fields' => [
        'all_items' => 'div.item :group',
        'first_item' => 'div.item :group :text(1)',
    ]
];

$result2 = $parser->parse($html2, $selectors2);

echo "\nExpected: 3 groups (3+3+1 elements)\n";
echo "Group 1: Item 1 <g> Item 2 <g> Item 3\n";
echo "Group 2: Item 4 <g> Item 5 <g> Item 6\n";
echo "Group 3: Item 7\n";

echo "\nActual Results:\n";
foreach ($result2 as $idx => $group) {
    echo "Group " . ($idx + 1) . ":\n";
    echo "  all_items: " . $group['all_items'] . "\n";
    echo "  first_item: " . $group['first_item'] . "\n";
}

if (count($result2) === 3) {
    echo "\n✅ FIX 2 (Numeric): PASSED - Correct number of groups!\n";
} else {
    echo "\n❌ FIX 2 (Numeric): FAILED - Expected 3 groups, got " . count($result2) . "\n";
}

// ========================================
// FIX 2: Group Config - Selector Range
// ========================================

echo "\n\n" . str_repeat("=", 80) . "\n";
echo "FIX 2: Group Config - Selector Range (From start to end selector)\n";
echo str_repeat("=", 80) . "\n";

$html3 = <<<HTML
<div class="page">
    <div class="t h22">PT Company A</div>
    <div class="info">Address A</div>
    <div class="info">Phone A</div>
    <div class="t h21">Email A</div>
    
    <div class="t h22">PT Company B</div>
    <div class="info">Address B</div>
    <div class="info">Phone B</div>
    <div class="t h21">Email B</div>
    
    <div class="t h22">PT Company C</div>
    <div class="info">Address C</div>
    <div class="info">Phone C</div>
    <div class="t h21">Email C</div>
</div>
HTML;

$selectors3 = [
    'parent' => 'div.page > div',
    'group' => ['div.t.h22', 'div.t.h21'], // From h22 to h21
    'fields' => [
        'company' => 'div.t.h22',
        'full_info' => 'div :group',
        'address' => 'div.info :group :text(1)',
        'phone' => 'div.info :group :text(2)',
        'email' => 'div.t.h21',
    ]
];

$result3 = $parser->parse($html3, $selectors3);

echo "\nExpected: 3 groups (A, B, C)\n";
echo "Each group: From div.t.h22 to div.t.h21\n";

echo "\nActual Results:\n";
foreach ($result3 as $idx => $item) {
    echo "\nGroup " . ($idx + 1) . ":\n";
    echo "  company: " . $item['company'] . "\n";
    echo "  address: " . $item['address'] . "\n";
    echo "  phone: " . $item['phone'] . "\n";
    echo "  email: " . $item['email'] . "\n";
}

if (count($result3) === 3) {
    echo "\n✅ FIX 2 (Selector Range): PASSED - Correct number of groups!\n";

    // Validate content
    if (
        strpos($result3[0]['company'], 'Company A') !== false &&
        strpos($result3[1]['company'], 'Company B') !== false &&
        strpos($result3[2]['company'], 'Company C') !== false
    ) {
        echo "✅ Content validation: PASSED!\n";
    }
} else {
    echo "\n❌ FIX 2 (Selector Range): FAILED - Expected 3 groups, got " . count($result3) . "\n";
}

// ========================================
// COMBINED TEST: Both Fixes Together
// ========================================

echo "\n\n" . str_repeat("=", 80) . "\n";
echo "COMBINED TEST: Group Config + :group :contains()\n";
echo str_repeat("=", 80) . "\n";

$html4 = <<<HTML
<div id="page-container">
    <div class="pc">
        <div class="t h22">PT Elecomp Indonesia</div>
        <div class="t h18">Jl. Gatot Subroto No. 123</div>
        <div class="t h18">Telp.: 021-5555, Faks.: 021-6666, E-mail: info@elecomp.com</div>
        <div class="t h21">Website: www.elecomp.com</div>
        
        <div class="t h22">PT Tech Solutions</div>
        <div class="t h18">Jl. Sudirman No. 456</div>
        <div class="t h18">Telp.: 021-7777, Faks.: 021-8888, E-mail: contact@techsol.com</div>
        <div class="t h21">Website: www.techsol.com</div>
    </div>
</div>
HTML;

$selectors4 = [
    'parent' => 'div#page-container div.pc > div',
    'group' => ['div.t.h22', 'div.t.h21'],
    'fields' => [
        'nama' => 'div.t.h22',
        'alamat' => 'div.t.h18 :group :text(1)',
        'telepon' => 'div.t.h18 :group :contains("Telp")',
        'fax' => 'div.t.h18 :group :contains("Faks")',
        'email' => 'div.t.h18 :group :contains("E-mail")',
        'website' => 'div.t.h21 :group :contains("Website")',
    ]
];

$result4 = $parser->parse($html4, $selectors4);

echo "\nExpected: 2 companies with clean extracted values\n";

echo "\nActual Results:\n";
foreach ($result4 as $idx => $item) {
    echo "\nCompany " . ($idx + 1) . ":\n";
    echo "  nama: " . $item['nama'] . "\n";
    echo "  alamat: " . $item['alamat'] . "\n";
    echo "  telepon: " . $item['telepon'] . "\n";
    echo "  fax: " . $item['fax'] . "\n";
    echo "  email: " . $item['email'] . "\n";
    echo "  website: " . $item['website'] . "\n";
}

// Final validation
$allPassed = true;
if (count($result4) !== 2) {
    echo "\n❌ FAILED: Expected 2 companies, got " . count($result4) . "\n";
    $allPassed = false;
}

if (!empty($result4)) {
    // Check that telepon is just the number, not full string
    if (
        strpos($result4[0]['telepon'], ',') === false &&
        strpos($result4[0]['telepon'], 'Faks') === false
    ) {
        echo "\n✅ Telepon extraction: PASSED (no commas or extra text)\n";
    } else {
        echo "\n❌ Telepon extraction: FAILED (contains extra text)\n";
        $allPassed = false;
    }
}

if ($allPassed) {
    echo "\n✅✅✅ ALL TESTS PASSED! ✅✅✅\n";
}

echo "\n\n";
echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                        TESTS COMPLETED                                     ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
