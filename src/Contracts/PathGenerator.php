<?php

namespace Elegant\Media\Contracts;

use Elegant\Media\Media;
use Elegant\Media\MediaManipulation;
use Illuminate\Http\UploadedFile as File;

interface PathGenerator
{
    public function getName(HasMedia $model, File $file): string;
    public function getDirectory(HasMedia $model, File $file): string;

    public function getConversionName(Media $media, MediaManipulation $manipulation, File $file): string;
    public function getConversionDirectory(Media $media, MediaManipulation $manipulation, File $file): string;
}
