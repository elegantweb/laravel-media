<?php

namespace Elegant\Media;

class FileRemover
{
    protected $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function toNullity(): void
    {
        if ($this->file instanceof RemoteFile) {
            $this->file->delete();
        } else {
            unlink($this->file->path());
        }
    }
}
