<?php

namespace Elegant\Media\Contracts;

use Elegant\Media\Media;
use Elegant\Media\MediaManipulation;

interface PathGenerator
{
    public function getName(HasMedia $model, $file): string;
    public function getDirectory(HasMedia $model, $file): string;

    public function getConversionName(Media $media, MediaManipulation $manipulation, $file): string;
    public function getConversionDirectory(Media $media, MediaManipulation $manipulation, $file): string;
}
