<?php

namespace App\Libraries\Crawler;

class CheckpointStore
{

    protected $file = WRITEPATH . 'crawler_checkpoint_';

    public function save($data, $site)
    {
        file_put_contents($this->file . "$site.json", json_encode($data));
    }

    public function load($site)
    {
        return file_exists($this->file . "$site.json")
            ? json_decode(file_get_contents($this->file . "$site.json"), true)
            : [];
    }
}
