<?php

namespace Elegant\Media;

use Elegant\Media\Contracts\HasMedia;
use Elegant\Media\Contracts\PathGenerator as PathGeneratorContract;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

class PathGenerator implements PathGeneratorContract
{
    public function getName(HasMedia $model, File|UploadedFile|RemoteFile $file): string
    {
        return $file->hashName();
    }

    public function getDirectory(HasMedia $model, File|UploadedFile|RemoteFile $file): string
    {
        return join('/', [date('Y'), date('m')]);
    }

    public function getConversionName(Media $media, MediaManipulation $manipulation, File|UploadedFile|RemoteFile $file): string
    {
        return "{$media->filename}-{$manipulation->getName()}.{$file->extension()}";
    }

    public function getConversionDirectory(Media $media, MediaManipulation $manipulation, File|UploadedFile|RemoteFile $file): string
    {
        return $media->directory;
    }
}
