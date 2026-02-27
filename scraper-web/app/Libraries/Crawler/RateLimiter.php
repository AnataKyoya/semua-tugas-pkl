<?php

namespace App\Libraries\Crawler;

class RateLimiter
{

    protected $delay;

    public function __construct($base = 600)
    {
        $this->delay = $base;
    }

    public function wait()
    {
        usleep(($this->delay + rand(50, 300)) * 1000);
    }

    public function slowDown()
    {
        $this->delay = min($this->delay * 1.5, 5000);
    }
}
