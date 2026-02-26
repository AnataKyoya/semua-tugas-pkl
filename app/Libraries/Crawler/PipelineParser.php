<?php

namespace App\Libraries\Crawler;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Polyfill\Intl\Normalizer\Normalizer;

class PipelineParser
{
    public function parse($html, $selectors)
    {
        $crawler = new Crawler($html);
        $results = [];
        $seenLinks = [];

        $itemSelector = $selectors['parent'];
        $groupConfig = $selectors['group'] ?? null;

        // Support multiple parent selectors (backward compatible)
        $parentSelectors = is_array($itemSelector) ? $itemSelector : [$itemSelector];

        // Loop setiap parent selector
        foreach ($parentSelectors as $currentParent) {
            // Jika ada group config, gunakan grouping logic
            if ($groupConfig !== null) {
                $groups = $this->groupElements($crawler, $currentParent, $groupConfig);

                foreach ($groups as $groupNodes) {
                    $item = [];

                    // Create temporary wrapper for group
                    $groupHtml = '';
                    foreach ($groupNodes as $node) {
                        $groupHtml .= $node->outerHtml();
                    }

                    $groupCrawler = new Crawler($groupHtml);

                    foreach ($selectors['fields'] as $key => $selector) {
                        if ($key === 'parent' || $key === 'group') continue;

                        try {
                            $item[$key] = $this->resolveField($groupCrawler, $key, $selector, $seenLinks);
                        } catch (\Throwable $e) {
                            $item[$key] = '';
                        }
                    }

                    if (!empty(array_filter($item))) {
                        $results[] = $item;
                    }
                }
            } else {
                // Original behavior tanpa grouping
                $crawler->filter($currentParent)->each(function (Crawler $node) use (&$results, &$seenLinks, $selectors) {
                    $item = [];

                    foreach ($selectors['fields'] as $key => $selector) {
                        if ($key === 'parent' || $key === 'group') continue;

                        try {
                            $item[$key] = $this->resolveField($node, $key, $selector, $seenLinks);
                        } catch (\Throwable $e) {
                            $item[$key] = '';
                        }
                    }

                    if (!empty(array_filter($item))) {
                        $results[] = $item;
                    }
                });
            }
        }

        return $results;
    }

    // =============================
    // CORE RESOLVER
    // =============================

    public function grouping(Crawler $crawler, string $itemSelector, $groupConfig)
    {

        [$startSelector, $endSelector] = $groupConfig;

        $parent = $crawler->filter($itemSelector);
        $children = $parent->children();

        $groups = [];
        $currentGroup = [];

        $children->each(function (Crawler $child, $i) use (&$groups, &$currentGroup, $endSelector, $startSelector) {

            // START → tutup grup sebelumnya, lalu mulai grup baru
            if ($child->matches($startSelector)) {
                // Simpan grup sebelumnya jika ada
                if (!empty($currentGroup)) {
                    $groups[] = $currentGroup;
                }
                // Mulai grup baru dengan start selector ini
                $currentGroup = [$child];
            }
            // Jika sedang dalam grup (sudah ada start), tambahkan child
            elseif (!empty($currentGroup)) {
                $currentGroup[] = $child;
            }

            // END → simpan grup
            // if ($child->matches($endSelector)) {
            //     if (!empty($currentGroup)) {
            //         $groups[] = $currentGroup;
            //         $currentGroup = [];
            //     }
            // }
        });

        // Tambahkan grup terakhir jika ada
        if (!empty($currentGroup)) {
            $groups[] = $currentGroup;
        }

        return $groups;
    }

    protected function groupElements(Crawler $crawler, string $itemSelector, $groupConfig): array
    {
        $elements = [];
        $crawler->filter($itemSelector)->each(function (Crawler $node) use (&$elements) {
            $elements[] = $node;
        });

        if (empty($elements)) {
            return [];
        }

        $groups = [];

        // Case 1: Numeric grouping - kelompokkan per N elemen
        if (is_numeric($groupConfig)) {
            $groupSize = (int) $groupConfig;
            $chunks = array_chunk($elements, $groupSize);

            return $chunks;
        }

        // Case 2: Selector range grouping (START → END = 1 RECORD)
        if (is_array($groupConfig) && count($groupConfig) === 2) {
            $res = $this->grouping($crawler, $itemSelector, $groupConfig);

            return $res;
        }


        // Fallback: treat each element as separate group
        return array_map(function ($el) {
            return [$el];
        }, $elements);
    }

    // =============================
    // CORE RESOLVER
    // =============================

    protected function resolveField(Crawler $node, string $key, string $selector, array &$seenLinks)
    {
        // :group has highest priority karena bisa dikombinasi dengan :text dan :contains
        if ($this->hasGroup($selector)) {
            return $this->resolveGroup($node, $selector);
        }

        if ($this->hasContains($selector)) {
            return $this->resolveContains($node, $selector);
        }

        if ($this->hasTextIndex($selector)) {
            return $this->resolveTextIndex($node, $selector);
        }

        return $this->resolveStandard($node, $key, $selector, $seenLinks);
    }

    // =============================
    // STANDARD SELECTOR HANDLER
    // =============================

    protected function resolveStandard(Crawler $node, string $key, string $selector, array &$seenLinks)
    {
        $element = $node->filter($selector);

        if (!$element->count()) return '';

        if ($key === 'detail_url') {
            $url = trim($element->attr('href') ?? '');
            if ($url === '' || isset($seenLinks[$url])) return '';
            $seenLinks[$url] = true;
            return $url;
        }

        if (strpos($key, 'image') !== false) {
            return $element->attr('src') ?? '';
        }

        return trim($element->text());
    }

    // =============================
    // :text(n) HANDLER
    // =============================

    protected function resolveTextIndex(Crawler $node, string $selector)
    {
        preg_match('/^(.+?)\s+:text\((\d+)\)$/i', $selector, $m);

        $baseSelector = trim($m[1] ?? '');
        $index = ((int)($m[2] ?? 1)) - 1;

        $element = $node->filter($baseSelector);
        if (!$element->count()) return '';

        $parts = $this->extractTextParts($element);
        return $parts[$index] ?? '';
    }

    // =============================
    // :group HANDLER
    // =============================

    protected function resolveGroup(Crawler $node, string $selector)
    {
        // Parse: "base :group" atau "base :group :text(n)" atau "base :group :contains("label")" 
        // atau "base :group :text(n) :contains("label")"

        preg_match('/^(.+?)\s+:group(.*)$/i', $selector, $matches);

        $baseSelector = trim($matches[1] ?? '');
        $modifiers = trim($matches[2] ?? '');

        if ($baseSelector === '') return '';

        // Ambil semua elemen yang match dengan base selector
        $elements = $node->filter($baseSelector);
        if (!$elements->count()) return '';

        // Gabungkan semua text dengan separator <g>
        $grouped = [];
        $elements->each(function ($el) use (&$grouped) {
            $text = $this->normalizeText($el->text());
            if ($text !== '') {
                $grouped[] = $text;
            }
        });

        $raw = implode(' <g> ', $grouped);

        $raw = $this->cleanGroupSeparatorsToSatu_alt($raw);
        $display = preg_replace('/\s*<SATU>\s*/', ' ', $raw);
        $cleaning = $this->cleanGroupSeparators($display);

        // echo $cleaning;

        // Split by <g> untuk processing selanjutnya
        $parts = array_map('trim', explode('<g>', $cleaning));

        if (preg_match('/:text\((\d+)\)/i', $modifiers, $textMatch)) {
            $index = ((int)$textMatch[1]) - 1;
            $text = $parts[$index] ?? '';

            if (preg_match("/:contains\('(.+)'\)/i", $modifiers, $containsMatch)) {
                $label = $containsMatch[1];

                return $this->extractAfterLabel($text, $label); // ✅ ganti ini
            }

            return trim(str_replace('<g>', '', $text));
        }

        if (preg_match("/:contains\('(.+)'\)/i", $modifiers, $containsMatch)) {
            $label = $containsMatch[1];

            foreach ($parts as $part) {
                $result = $this->extractAfterLabel($part, $label); // ✅ ganti ini
                if ($result !== '') return $result; // ⚠️ tambah ini juga
            }

            return '';
        }

        return $cleaning;
    }

    // =============================
    // :contains() HANDLER (UNIFIED)
    // =============================

    protected function resolveContains(Crawler $node, string $selector)
    {
        preg_match('/^(.+?):contains\("(.+)"\)(.*)$/', $selector, $m);

        $base = trim($m[1] ?? '');
        $label = $m[2] ?? '';
        $tail = trim($m[3] ?? '');

        if ($base === '' || $label === '') return '';

        // CASE A — base has :text(n)
        if ($this->hasTextIndex($base)) {
            return $this->resolveContainsFromText($node, $base, $label);
        }

        // CASE B — normal selector contains + optional tail
        return $this->resolveContainsFromElements($node, $base, $label, $tail);
    }

    protected function resolveContainsFromText(Crawler $node, string $baseSelector, string $label)
    {
        preg_match('/^(.+?)\s+:text\((\d+)\)$/i', $baseSelector, $m);

        $real = trim($m[1] ?? '');
        $index = ((int)($m[2] ?? 1)) - 1;

        $element = $node->filter($real);
        if (!$element->count()) return '';

        $parts = $this->extractTextParts($element);
        $text = $parts[$index] ?? '';

        return $this->extractAfterLabel($text, $label);
    }

    protected function resolveContainsFromElements(Crawler $node, string $base, string $label, string $tail)
    {
        $value = '';

        $node->filter($base)->each(function ($el) use ($label, $tail, &$value) {
            if (stripos($el->text(), $label) === false) return;

            if ($tail) {
                $target = $el->filter($tail);
                if ($target->count()) {
                    $value = trim($target->text());
                }
            } else {
                $value = $this->extractAfterLabel($el->text(), $label);
            }
        });

        return $value;
    }

    // =============================
    // TEXT EXTRACTION UTILITIES
    // =============================

    protected function extractAfterLabel(string $text, string $label): string
    {
        foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
            if (stripos($line, $label) === false) continue;

            if (preg_match('/(?<!\w)' . preg_quote($label, '/') . '\s*[:.]\s*(.+?)(?=\s*,?\s*\w[\w\s]*\s*[:.]\s*|$)/i', $line, $m)) {
                return trim($m[1], " ,");
            }

            // Fallback
            return trim(preg_replace(
                '/.*' . preg_quote($label, '/') . '\s*[:\-]?\s*/i',
                '',
                $line
            ));
        }

        return '';
    }

    protected function extractTextParts(Crawler $crawler): array
    {
        if (!$crawler->count()) return [];

        $node = $crawler->getNode(0);
        $texts = [];
        $html = $crawler->html();

        // Strategy 1 — <br> / <hr>
        if (preg_match('/<(br|hr)\b/i', $html)) {
            $parts = preg_split('/<(?:br|hr)\b[^>]*>/i', $html);
            $clean = array_values(array_filter(array_map(fn($p) => trim(strip_tags($p)), $parts)));
            if ($clean) return $clean;
        }

        // Strategy 2 — child blocks
        $children = $crawler->children();
        if ($children->count() > 1) {
            $children->each(function ($child) use (&$texts) {
                $t = trim($child->text());
                if ($t !== '') $texts[] = $t;
            });
            if (count($texts) > 1) return $texts;
        }

        // Strategy 3 — DOM text nodes
        $texts = [];
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE || $child->nodeType === XML_ELEMENT_NODE) {
                // $t = trim($child->textContent);
                $t = $this->normalizeText($child->textContent);
                if ($t !== '') $texts[] = $t;
            }
        }
        if (count($texts) > 1) return $texts;

        // Strategy 4 — newline split
        // $lines = preg_split('/\r\n|\r|\n/', $crawler->text());
        $lines = preg_split('/\r\n|\r|\n/', $this->normalizeText($crawler->text()));
        $lines = array_values(array_filter(array_map('trim', $lines)));
        if (count($lines) > 1) return $lines;

        // Strategy 5 — fallback
        $full = $this->normalizeText($crawler->text());
        return $full !== '' ? [$full] : [];
    }

    // =============================
    // HELPERS
    // =============================

    protected function hasContains(string $selector): bool
    {
        return strpos($selector, ':contains') !== false;
    }

    protected function hasTextIndex(string $selector): bool
    {
        return (bool) preg_match('/\:text\(\d+\)/i', $selector);
    }

    protected function hasGroup(string $selector): bool
    {
        return strpos($selector, ':group') !== false;
    }

    protected function normalizeText(string $text): string
    {
        if ($text === '') return '';

        // Decode HTML entities first
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Unicode normalize (if intl exists)
        if (class_exists('Normalizer')) {
            $text = Normalizer::normalize($text, Normalizer::FORM_C);
        }

        // Replace known Unicode variants → ASCII
        $map = [

            // DASHES / MINUS
            "\u{2010}" => "-",
            "\u{2011}" => "-",
            "\u{2012}" => "-",
            "\u{2013}" => "-",
            "\u{2014}" => "-",
            "\u{2212}" => "-",

            // QUOTES
            "\u{2018}" => "'",
            "\u{2019}" => "'",
            "\u{201A}" => "'",
            "\u{201B}" => "'",
            "\u{201C}" => '"',
            "\u{201D}" => '"',
            "\u{201E}" => '"',

            // ELLIPSIS
            "\u{2026}" => "...",

            // BULLETS
            "\u{2022}" => "-",
            "\u{25CF}" => "-",

            // SPACES
            "\u{00A0}" => " ",
            "\u{2000}" => " ",
            "\u{2001}" => " ",
            "\u{2002}" => " ",
            "\u{2003}" => " ",
            "\u{2004}" => " ",
            "\u{2005}" => " ",
            "\u{2006}" => " ",
            "\u{2007}" => " ",
            "\u{2008}" => " ",
            "\u{2009}" => " ",
            "\u{200A}" => " ",
            "\u{202F}" => " ",
            "\u{205F}" => " ",
            "\u{3000}" => " ",

            // ZERO WIDTH / INVISIBLE
            "\u{200B}" => "",
            "\u{200C}" => "",
            "\u{200D}" => "",
            "\u{FEFF}" => "",
            "\u{2060}" => "",

            // CONTROL CHARACTERS
            "\x00" => "",
            "\x1F" => "",
        ];

        $text = strtr($text, $map);

        // Remove invisible/control unicode blocks
        $text = preg_replace('/[\p{Cf}\p{Cc}]/u', '', $text);

        // OCR NOISE CLEANUP (common garbage)
        $text = preg_replace('/[�]+/u', '', $text);

        // Normalize line breaks → single space
        $text = preg_replace("/\r\n|\r|\n/u", " ", $text);

        // Collapse whitespace
        $text = preg_replace('/\s+/u', ' ', $text);

        return trim($text);
    }

    /**
     * Membersihkan separator <g> yang berlebihan dan merge label yang terpisah
     */
    protected function cleanGroupSeparators(string $text): string
    {
        $text = preg_replace([
            '/([,:])\s*<g>\s*/i',   // Hapus <g> setelah koma/titik dua
            '/<g>\s*,/i',           // Hapus <g> sebelum koma ✨ (bonus!)
            '/(\s*<g>\s*)+/i'       // ← PERBAIKAN: tambah +
        ], [
            '$1 ',
            ', ',
            ' <g> '
        ], $text);

        return trim(preg_replace('/\s+/', ' ', $text));
    }

    protected function cleanGroupSeparatorsToSatu_alt(string $text): string
    {
        if ($text === '') return '';

        // RULE 1: Proteksi entitas lengkap "Label: Value"
        $protected = [];
        $text = preg_replace_callback(
            '/([\p{L}\p{N}\.\/\\\]+[\s]*[:：])\s*([^,<>\n\r]+?)(?=\s*[,<]|\s*$)/u',
            function ($m) use (&$protected) {
                $id = '###PROTECTED_' . count($protected) . '###';
                $protected[$id] = trim($m[0]);
                return $id;
            },
            $text
        );

        // RULE 2: Antar entitas lengkap → <SATU>
        $text = preg_replace(
            '/(###PROTECTED_\d+###)\s*,?\s*<g>\s*(###PROTECTED_\d+###)/',
            '$1, <SATU> $2',
            $text
        );

        // RULE 3: Slash + <g> → <SATU>
        $text = preg_replace(
            '/(\\\?\/)\s*<g>\s*/u',
            '$1 <SATU> ',
            $text
        );

        // RULE 4: Abreviasi 2-5 huruf + <g> (LANGSUNG) → <SATU>
        $text = preg_replace(
            '/(\b[\p{L}]{2,5}\.?)\s*<g>\s*/ui',
            '$1 <SATU> ',
            $text
        );

        // RULE 5: Numeric fragment → <SATU>
        $text = preg_replace(
            '/(\d{2,6})\s*<g>\s*(\d{2,6})/u',
            '$1<SATU>$2',
            $text
        );
        $text = preg_replace(
            '/(\d{2,4})\s*<g>\s*(\d{3,4})\s*<g>\s*(\d{3,4})/',
            '$1<SATU>$2<SATU>$3',
            $text
        );

        // RULE 6: Symbol + <g> → <SATU>
        $text = preg_replace(
            '/([@#\$€£¥%&])\s*<g>\s*/u',
            '$1<SATU> ',
            $text
        );

        // RULE 7: Sisa <g> → structural separator (tetap <g>)
        $text = str_replace('<SATU>', '###SATU###', $text);
        $text = preg_replace('/(\s*<g>\s*)+/', ' <g> ', $text);
        $text = str_replace('###SATU###', '<SATU>', $text);

        // RULE 8: Restore protected entities
        foreach ($protected as $id => $entity) {
            $text = str_replace($id, $entity, $text);
        }

        /**
         * =================================================================
         * RULE KHUSUS ALAMAT: Label wilayah di kanan → <SATU>
         * =================================================================
         */
        $text = preg_replace_callback(
            '/\s*(.+?)\s*<g>\s*(.+?)(?=\s*(?:Kab\.?\/?\s*Kota|Kec\.?|Kel\.?|Prov\.?|Desa|Dusun|Kp\.?|RW|RT)\b)/ui',
            function ($m) {
                return trim($m[1]) . ' <SATU> ' . $m[2];
            },
            $text
        );

        // RULE 9: Final cleanup
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/,\s*,/', ',', $text);
        $text = preg_replace('/<g>\s*,/', ',', $text);
        $text = preg_replace('/<SATU>\s*,/', ',', $text);
        $text = preg_replace('/\s+([,])/', '$1', $text);

        return trim($text);
    }
}
