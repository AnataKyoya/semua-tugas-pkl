<?php

namespace App\Libraries\Crawler;

class UrlQueue
{

    protected $queue = [];
    protected $visited = [];

    public function push($stage, $url, $parent = [])
    {
        if (!isset($this->visited[$url])) {
            $this->visited[$url] = true;
            $this->queue[] = compact('stage', 'url', 'parent');
        }
    }

    public function pop()
    {
        return array_shift($this->queue);
    }

    public function hasJobs()
    {
        return !empty($this->queue);
    }
}
