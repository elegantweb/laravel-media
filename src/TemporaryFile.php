<?php

namespace Elegant\Media;

use Illuminate\Http\File;

class TemporaryFile extends File
{
    public function __construct()
    {
        $tmpfile = tempnam(sys_get_temp_dir(), 'laravel-media-');

        parent::__construct($tmpfile);
    }

    public function __destruct()
    {
        @unlink($this->getPathname());
    }
}
