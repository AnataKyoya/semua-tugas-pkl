<?php

namespace App\Libraries\Crawler;

use Config\WebsiteScraperBaru;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\ScrapedResult;

class ClaudeCrawl
{
    protected $fetcher;
    protected $parser;
    protected $queue;
    protected $checkpoint;
    protected $rate;
    protected $config;
    protected $scrapeModel;

    protected $maxPageFound = 0;
    protected $lastUrlProcessed;
    protected $isFirstFetch = true;

    public function __construct()
    {
        $this->fetcher = new Fetcher();
        $this->parser = new PipelineParser();
        $this->queue = new UrlQueue();
        $this->checkpoint = new CheckpointStore();
        $this->rate = new RateLimiter();
        $this->config = new WebsiteScraperBaru();
        $this->scrapeModel = new ScrapedResult();
    }

    public function run($siteKey)
    {
        $config = $this->config->sites[$siteKey];
        $stages = $config['stages'];
        $checkpoint = $this->checkpoint->load($siteKey);

        // Setup pagination
        $hasPagination = !empty($config['pagination']['selector']);
        $paginationUrls = $this->collectPaginationUrls($config, $checkpoint, $hasPagination);

        // Enqueue all pagination URLs to stage 0
        foreach ($paginationUrls as $url) {
            $this->queue->push(0, $url);
        }

        // Process queue
        $results = $this->processQueue(
            $stages,
            $checkpoint,
            $hasPagination,
            $siteKey
        );

        return $results;
    }

    /**
     * Collect all pagination URLs
     */
    protected function collectPaginationUrls($config, $checkpoint, $hasPagination)
    {
        $startUrl = $checkpoint['last_url'] ?? $config['start_url'];
        $urls = [$startUrl];

        if (!$hasPagination) {
            $this->lastUrlProcessed = $startUrl;
            return $urls;
        }

        // Initialize from checkpoint
        $this->maxPageFound = $checkpoint['last_max_page_seen'] ?? 0;
        $this->isFirstFetch = ($this->maxPageFound == 0);

        $currentUrl = $startUrl;
        $maxIterations = $config['pagination']['max_page'];
        $iteration = 0;

        while ($iteration < $maxIterations) {
            $html = $this->fetcher->get($currentUrl);
            $this->rateLimit();

            if (!$html) break;

            $pageData = $this->detectPaginationLinks(
                $html,
                $config['pagination']['selector']
            );

            // No new pages found, stop
            if (empty($pageData['links'])) {
                break;
            }

            // Add new URLs
            foreach ($pageData['links'] as $link) {
                if ($link !== null) {
                    $absoluteUrl = $this->makeAbsoluteUrl($link, $startUrl);
                    if (!in_array($absoluteUrl, $urls)) {
                        $urls[] = $absoluteUrl;
                    }
                }
            }

            // Update max page found
            $this->maxPageFound = max($pageData['maxPage'], $this->maxPageFound);

            // Get next URL to crawl
            $nextUrl = end($urls);
            if ($nextUrl === $currentUrl) break;

            $currentUrl = $nextUrl;
            $this->lastUrlProcessed = $nextUrl;
            $iteration++;
        }

        return $urls;
    }

    /**
     * Detect pagination links and max page number
     */
    protected function detectPaginationLinks($html, $selector)
    {
        $crawler = new Crawler($html);
        $result = [
            'maxPage' => $this->maxPageFound,
            'links' => []
        ];
        $shouldStop = false;

        $crawler->filter($selector)->each(function (Crawler $node) use (&$result, &$shouldStop) {
            if ($shouldStop) return;

            $text = trim($node->text());
            $href = $node->attr('href');

            // Skip anchor links
            if ($this->isAnchorLink($href)) {
                return;
            }

            // Extract page number
            $pageNumber = $this->extractPageNumber($text);

            // ✅ HANDLE ELLIPSIS - STOP SEGERA
            if ($this->isEllipsis($text)) {
                // Jika fetch pertama, langsung stop
                if ($this->isFirstFetch) {
                    $shouldStop = true;
                    return;
                }

                // ✅ Jika bukan fetch pertama, cek apakah sudah ada angka > maxPageFound
                // Jika sudah ada yang diambil, stop di ... pertama setelahnya
                if (!empty($result['links'])) {
                    $shouldStop = true;
                    return;
                }

                // Jika belum ada yang diambil, skip ... dan lanjut
                return;
            }

            // Hanya ambil yang LEBIH BESAR dari maxPageFound
            if ($pageNumber > $this->maxPageFound) {
                if ($pageNumber > $result['maxPage']) {
                    $result['maxPage'] = $pageNumber;
                }
                $result['links'][] = $href;
            }
        });

        return $result;
    }

    /**
     * Process the job queue
     */
    protected function processQueue($stages, $checkpoint, $hasPagination, $siteKey)
    {
        $results = [];
    
        // State tracking for stop detection (Sesuai kode aslimu)
        $state = [
            'urlRepeat' => $checkpoint['url_repeat_count'] ?? 0,
            'htmlRepeat' => $checkpoint['html_repeat_count'] ?? 0,
            'rowRepeat' => $checkpoint['row_repeat_count'] ?? 0,
            'lastUrl' => $checkpoint['last_url'] ?? null,
            'lastHtmlHash' => $checkpoint['last_html_hash'] ?? null,
            'lastRowHash' => $checkpoint['last_row_hash'] ?? null,
            'maxPageSeen' => $checkpoint['last_max_page_seen'] ?? 0
        ];
    
        while ($this->queue->hasJobs()) {
            $job = $this->queue->pop();
            $stageIndex = $job['stage'];
            $stage = $stages[$stageIndex];
    
            // Fetch HTML
            $html = $this->fetcher->get($job['url']);
            if (!$html) {
                $this->rate->slowDown();
                continue;
            }
    
            $htmlHash = md5($html);
    
            // Track repetitions
            if ($hasPagination) {
                $state = $this->trackRepetitions($state, $job['url'], $htmlHash);
            }
    
            // Parse rows
            $rows = $this->parser->parse($html, $stage);
    
            // Track last row hash
            $currentRowHash = $this->getLastRowHash($rows);
            if ($hasPagination && $currentRowHash) {
                $state['rowRepeat'] = ($state['lastRowHash'] === $currentRowHash)
                    ? $state['rowRepeat'] + 1
                    : 0;
            }
    
            // --- BAGIAN YANG DIPERBAIKI (TETAP MENGGUNAKAN DATABASE SEBAGAI FILTER) ---
            
            // 1. Ambil hasil HANYA untuk halaman/job ini (jangan digabung ke $results dulu)
            $currentJobResults = $this->processRows($rows, $stage, $stageIndex, $job, []);
    
            // 2. Ambil data terakhir dari Database
            $existing = $this->scrapeModel->where('website', $siteKey)->first();
            $oldResults = [];
            if ($existing && !empty($existing['data'])) {
                $oldResults = json_decode($existing['data'], true);
            }
    
            // 3. Deduplikasi: Bandingkan data halaman ini dengan data DB
            // Hasilnya adalah gabungan data lama + data baru yang benar-benar unik
            $finalResults = $this->deduplicateResults(
                $oldResults,
                $currentJobResults,
                $checkpoint['duplicate_count'] ?? []
            );
    
            // 4. Update Database secara real-time agar iterasi berikutnya punya referensi terbaru
            $jsonData = json_encode($finalResults);
            if ($existing) {
                $this->scrapeModel->update($existing['id'], ['data' => $jsonData]);
            } else {
                $this->scrapeModel->insert([
                    'website' => $siteKey, 
                    'data' => $jsonData, 
                    'status' => 'crawling'
                ]);
            }
            
            // Simpan ke variabel lokal untuk return fungsi
            $results = $finalResults;
    
            // --- END PERBAIKAN ---
    
            // Check stop conditions
            if ($hasPagination && $this->shouldStop($state)) {
                $checkpoint = $this->saveCheckpoint($checkpoint, $state, $currentRowHash, $htmlHash, $finalResults, $siteKey, $hasPagination, true);
                break;
            }
    
            // Save checkpoint
            $checkpoint = $this->saveCheckpoint($checkpoint, $state, $currentRowHash, $htmlHash, $finalResults, $siteKey, $hasPagination, false);
    
            // Update state for next iteration
            $state['lastUrl'] = $job['url'];
            $state['lastHtmlHash'] = $htmlHash;
            $state['lastRowHash'] = $currentRowHash;
            $state['maxPageSeen'] = max($state['maxPageSeen'], $this->maxPageFound);
    
            $this->rate->wait();
        }
    
        return $results;
    }

    /**
     * Track URL and HTML repetitions
     */
    protected function trackRepetitions($state, $currentUrl, $htmlHash)
    {
        // URL repeat
        $state['urlRepeat'] = ($state['lastUrl'] === $currentUrl)
            ? $state['urlRepeat'] + 1
            : 0;

        // HTML repeat
        $state['htmlRepeat'] = ($state['lastHtmlHash'] === $htmlHash)
            ? $state['htmlRepeat'] + 1
            : 0;

        return $state;
    }

    /**
     * Get hash of last row
     */
    protected function getLastRowHash($rows)
    {
        if (empty($rows)) return null;
        $lastRow = end($rows);
        return md5(json_encode($lastRow));
    }

    /**
     * Process parsed rows
     */
    protected function processRows($rows, $stage, $stageIndex, $job, $results)
    {
        foreach ($rows as $row) {
            if (!empty($stage['next'])) {
                $nextField = $stage['next'];
                if (!empty($row[$nextField])) {
                    $this->queue->push(
                        $stageIndex + 1,
                        $row[$nextField],
                        array_merge($job['parent'] ?? [], $row)
                    );
                }
            } else {
                $results[] = array_merge($job['parent'] ?? [], $row);
            }
        }
        return $results;
    }

    /**
     * Deduplicate results
     */
    protected function deduplicateResults($oldResults, $newResults, $duplicateCount)
    {
        $hashIndex = [];
        $finalResults = $oldResults;
    
        // Indexing data lama (Data dari DB)
        foreach ($oldResults as $row) {
            // Normalisasi: Urutkan key dan bersihkan whitespace agar Hash akurat
            ksort($row);
            $normalizedRow = array_map(function($v) {
                return is_string($v) ? trim($v) : $v;
            }, $row);
            
            $hashIndex[md5(json_encode($normalizedRow))] = true;
        }
    
        // Cek data baru
        foreach ($newResults as $row) {
            // Normalisasi data baru sebelum dibandingkan
            ksort($row);
            $normalizedNewRow = array_map(function($v) {
                return is_string($v) ? trim($v) : $v;
            }, $row);
            
            $hash = md5(json_encode($normalizedNewRow));
            
            if (!isset($hashIndex[$hash])) {
                $finalResults[] = $row;
                $hashIndex[$hash] = true;
                $duplicateCount[$hash] = 0;
            } else {
                $duplicateCount[$hash] = ($duplicateCount[$hash] ?? 0) + 1;
            }
        }
    
        return $finalResults;
    }

    /**
     * Check if should stop pagination
     */
    protected function shouldStop($state)
    {
        $pageStagnant = ($this->maxPageFound <= $state['maxPageSeen']);

        return (
            $state['urlRepeat'] >= 3 &&
            $state['htmlRepeat'] >= 3 &&
            $state['rowRepeat'] >= 3 &&
            $pageStagnant
        );
    }

    /**
     * Save checkpoint
     */
    protected function saveCheckpoint($checkpoint, $state, $rowHash, $htmlHash, $finalResults, $siteKey, $hasPagination, $isFinished)
    {
        $checkpoint = [
            'last_url' => $this->lastUrlProcessed ?? $checkpoint['last_url'],
            'url_repeat_count' => $state['urlRepeat'],
            'html_repeat_count' => $state['htmlRepeat'],
            'row_repeat_count' => $state['rowRepeat'],
            'last_html_hash' => $htmlHash,
            'last_row_hash' => $rowHash,
            'last_max_page_seen' => max($state['maxPageSeen'], $this->maxPageFound),
            'pagination_finished' => $isFinished,
            'paginate' => $hasPagination,
        ];

        $this->checkpoint->save($checkpoint, $siteKey);
        return $checkpoint;
    }

    /**
     * Check if link is anchor
     */
    protected function isAnchorLink($href)
    {
        return (strpos($href, '#') === 0 || substr($href, -1) === '#');
    }

    /**
     * Extract page number from text
     */
    protected function extractPageNumber($text)
    {
        if (preg_match('/^([1-9][0-9]*)(?:\D{2,3})?$/', $text, $matches)) {
            return (int)$matches[1];
        }
        return 0;
    }

    /**
     * Check if text is ellipsis
     */
    protected function isEllipsis($text)
    {
        return (preg_match('/^(?:\.{3,})$/', $text) || str_contains($text, '...'));
    }

    /**
     * Convert relative URL to absolute
     */
    protected function makeAbsoluteUrl($url, $baseUrl)
    {
        // Already absolute
        if (preg_match('/^https?:\/\//', $url)) {
            return str_replace('http://', 'https://', $url);
        }

        $parts = explode('/', $baseUrl);
        $domain = $parts[0] . '//' . $parts[2];

        // Absolute path
        if (strpos($url, '/') === 0) {
            return $domain . $url;
        }

        // Relative path
        return $domain . '/' . $url;
    }

    /**
     * Rate limiting
     */
    protected function rateLimit()
    {
        usleep(rand(200000, 600000)); // 0.2-0.6 seconds
    }
    
    /**
     * Parse single HTML file once
     */
    public function runFileOnce($html, $config)
    {
        // Validasi konfigurasi
        $configs = $config;
        $nameFile = str_replace(" ", "_", $configs['customName']);
        $hasHtml = pathinfo($nameFile, PATHINFO_EXTENSION) === 'html';
        $fullName = $hasHtml ? $nameFile : $nameFile . ".html";

        // Baca HTML dari file
        if (!$html) {
            throw new \Exception("Failed to read HTML from file");
        }

        $allParse = [];

        // Parse dengan stage 0
        $stages = $configs['stages'];
        foreach ($stages as $value) {
            $parser = $this->parser->parse($html, $value);
            $allParse = array_merge($allParse, $parser);
        }

        $save = [
            'results' => $allParse
        ];
        
        $jsonData = json_encode($allParse);
        
        $justName = str_replace('.html', '', $fullName);

        $isExist = $this->scrapeModel->where('website', $justName)->first();
        $isExist ? $this->scrapeModel->update($isExist['id'], ['data' => $jsonData]) : $this->scrapeModel->insert(['data' => $jsonData, 'website' => $justName, 'status' => 'done']);
        
        // $this->checkpoint->save($save, $justName);

        return base_url() . "scraper/export/" . $justName;
    }
}
