<?php

namespace Elegant\Media;

use Elegant\Media\Contracts\PathGenerator as PathGeneratorContract;
use Illuminate\Http\File;

class PathGenerator implements PathGeneratorContract
{
    public function getName(Media $media, File $file): string
    {
        return $file->hashName();
    }

    public function getDirectory(Media $media, File $file): string
    {
        return $media->getKey();
    }

    public function getConversionName(Media $media, MediaConversion $conversion, File $file): string
    {
        return $conversion->getName();
    }

    public function getConversionDirectory(Media $media, MediaConversion $conversion, File $file): string
    {
        return join(DIRECTORY_SEPERATOR, $this->getDirectory($media, $file), 'conversions');
    }
}
