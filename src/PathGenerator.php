<?php

namespace Elegant\Media;

use Elegant\Media\Contracts\HasMedia;
use Elegant\Media\Contracts\PathGenerator as PathGeneratorContract;
use Illuminate\Http\File;
use Illuminate\Support\Str;

class PathGenerator implements PathGeneratorContract
{
    public function getName(HasMedia $model, File $file): string
    {
        return $file->hashName();
    }

    public function getDirectory(HasMedia $model, File $file): string
    {
        return join('/', ['media', date('Y'), date('m')]);
    }

    public function getConversionName(Media $media, MediaConversion $conversion, File $file): string
    {
        return "{$media->filename}-{$conversion->getName()}.{$file->extension()}";
    }

    public function getConversionDirectory(Media $media, MediaConversion $conversion, File $file): string
    {
        return $media->directory;
    }
}
