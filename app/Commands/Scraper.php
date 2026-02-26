<?php

namespace App\Commands;

use App\Libraries\Crawler\CheckpointStore;
use App\Libraries\Crawler\ClaudeCrawl;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\Crawler\CrawlEngine;

class Scraper extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'scraper:run';
    protected $description = 'Run scraping process for specified site key.';
    protected $usage       = 'scraper:run siteKey';

    protected $arguments = [
        'siteKey' => 'The site key to scrape, e.g. kemenperin',
    ];

    protected $scraper;
    protected $scraperNew;
    protected $checkpoint;

    public function __construct()
    {
        $this->scraperNew = new ClaudeCrawl();
        $this->checkpoint = new CheckpointStore();
    }

    public function run(array $params)
    {
        $siteKey = $params[0] ?? null;

        if (!$siteKey) {
            CLI::error('Error: siteKey argument is required.');
            CLI::write('Usage: php spark scraper:run <siteKey>', 'yellow');
            return;
        }

        CLI::write("Starting scraper for siteKey: {$siteKey}");

        $lockFile = sys_get_temp_dir() . "/scraper_$siteKey.lock";
        $fp = fopen($lockFile, 'c');
        if (!flock($fp, LOCK_EX | LOCK_NB)) {
            // Ada proses lain yang sedang berjalan
            echo "Process already running, exiting.\n";
            return;
        }

        $checkpoint = $this->checkpoint->load($siteKey);

        $url_repeat_count = $checkpoint['url_repeat_count'] ?? 0;
        $html_repeat_count = $checkpoint['html_repeat_count'] ?? 0;
        $row_repeat_count = $checkpoint['row_repeat_count'] ?? 0;
        $paginate = $checkpoint['paginate'] ?? false;
        $pagination_finished = $checkpoint['pagination_finished'] ?? false;

        $shouldPause = $paginate
            ? ($pagination_finished && $html_repeat_count >= 3 && $url_repeat_count >= 3 && $row_repeat_count >= 3)
            : ($html_repeat_count >= 3 && $url_repeat_count >= 3 && $row_repeat_count >= 3);

        if ($shouldPause) {
            echo "Repeated content detected, skipping scraping.\n";
            flock($fp, LOCK_UN);
            fclose($fp);
            return;
        }

        try {
            $this->scraperNew->run($siteKey);
        } catch (\Exception $e) {
            echo "Error during scraping: " . $e->getMessage() . "\n";
        }

        // Lepas lock
        flock($fp, LOCK_UN);
        fclose($fp);
        unlink($lockFile);
    }
}
