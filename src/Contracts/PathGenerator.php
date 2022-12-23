<?php

namespace Elegant\Media\Contracts;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Elegant\Media\Media;
use Elegant\Media\MediaManipulation;
use Elegant\Media\RemoteFile;

interface PathGenerator
{
    public function getName(HasMedia $model, File|UploadedFile|RemoteFile $file): string;
    public function getDirectory(HasMedia $model, File|UploadedFile|RemoteFile $file): string;

    public function getConversionName(Media $media, MediaManipulation $manipulation, File|UploadedFile|RemoteFile $file): string;
    public function getConversionDirectory(Media $media, MediaManipulation $manipulation, File|UploadedFile|RemoteFile $file): string;
}
