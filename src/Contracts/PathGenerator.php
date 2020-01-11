<?php

namespace Elegant\Media\Contracts;

use Elegant\Media\Media;
use Elegant\Media\MediaConversion;
use Illuminate\Http\File;

interface PathGenerator
{
    public function getName(Media $media, File $file): string;
    public function getDirectory(Media $media, File $file): string;

    public function getConversionName(Media $media, MediaConversion $conversion, File $file): string;
    public function getConversionDirectory(Media $media, MediaConversion $conversion, File $file): string;
}
