<?php

namespace Elegant\Media\Contracts;

use Elegant\Media\Media;
use Elegant\Media\MediaConversion;
use Illuminate\Http\File;

interface PathGenerator
{
    public function getName(HasMedia $model, File $file): string;
    public function getDirectory(HasMedia $model, File $file): string;

    public function getConversionName(Media $media, MediaConversion $conversion, File $file): string;
    public function getConversionDirectory(Media $media, MediaConversion $conversion, File $file): string;
}
