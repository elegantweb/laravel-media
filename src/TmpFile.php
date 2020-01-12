<?php

namespace Elegant\Media;

use Illuminate\Http\File;

class TmpFile extends File
{
    public function __destruct()
    {
        @unlink($this->getPathname());
    }
}
