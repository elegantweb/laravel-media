<?php

namespace Elegant\Media;

use Illuminate\Http\UploadedFile as File;

class TemporaryFile extends File
{
    public function __construct(string $originalName)
    {
        $tmpfile = tempnam(sys_get_temp_dir(), 'laravel-media-');

        parent::__construct($tmpfile, $originalName);
    }

    public function __destruct()
    {
        @unlink($this->getPathname());
    }
}
