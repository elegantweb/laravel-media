<?php

namespace Elegant\Media\Tests;

use Illuminate\Support\Facades\Storage;

trait RefreshStorage
{
    public function refreshStorage()
    {
        $this->beforeApplicationDestroyed(function () {
            $this->cleanup();
        });
    }

    protected function cleanup()
    {
        foreach (Storage::directories() as $dir) {
            Storage::deleteDirectory($dir);
        }

        foreach (Storage::files() as $file) {
            if (!in_array($file, ['.gitignore']))
                Storage::delete($file);
        }
    }
}
