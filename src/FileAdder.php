<?php

namespace Elegant\Media;

use Elegant\Media\Contracts\PathGenerator;
use Elegant\Media\Contracts\HasMedia;
use Illuminate\Http\File;

class FileAdder
{
    protected $model;
    protected $file;
    protected $preserveOriginal = false;
    protected $mediaName;
    protected $mediaDirectory;
    protected $mediaProperties = [];
    protected $mediaConversions = [];

    public function __construct(HasMedia $model, File $file)
    {
        $this->model = $model;
        $this->file = $file;
    }

    public function preserveOriginal(): self
    {
        $this->preserveOriginal = true;

        return $this;
    }

    public function useFilename(string $name): self
    {
        $this->mediaFilename = $name;

        return $this;
    }

    public function useDirectory(string $directory): self
    {
        $this->mediaDirectory = $directory;

        return $this;
    }

    public function withProperties(array $properties): self
    {
        $this->mediaProperties = $properties;

        return $this;
    }

    public function useConversions(array $properties): self
    {
        $this->mediaConversions = $conversions;

        return $this;
    }

    public function toMediaGroup(string $name = 'default'): void
    {
        $group = $this->model->getMediaGroup($name);

        $media = new Media();
        $media->disk = $group->getDiskName();
        $media->group = $group->getName();
        $media->properties = array_merge($this->mediaProperties, $group->getProperties());
        $media->mime_type = $this->file->getMimeType();
        $media->size = $this->file->getSize();
        $media->name = $this->determineMediaName($this->model, $this->file);
        $media->directory = $this->determineMediaDirectory($this->model, $this->file);
        $this->model->media()->save($media);

        $media->storeFile($this->file, $this->preserveOriginal);

        $conversions = array_merge($this->mediaConversions, $group->getConversions());

        foreach ($conversions as $conversion) {
            $this->createConversion($conversion, $media);
        }

        $collection = $this->model->getMedia($group->getName());

        if (null !== $size = $group->getSize() and $size < $count = $collection->count()) {
            $collection->take($count - $size)->each->delete();
        }
    }

    protected function createConversion(MediaConversion $conversion, Media $originalMedia): void
    {
        $file = $conversion->perform($this->file);

        $media = new Media();
        $media->disk = $conversion->getDiskName() ?? $originalMedia->disk;
        $media->group = $conversion->getName();
        $media->mime_type = $file->getMimeType();
        $media->size = $file->getSize();
        $media->name = $this->determineConversionName($originalMedia, $conversion, $this->file);
        $media->directory = $this->determineConversionDirectory($originalMedia, $conversion, $this->file);
        $originalMedia->conversions()->save($media);

        $media->storeFile($file, false);
    }

    protected function getPathGenerator(): PathGenerator
    {
        return resolve(config('media.path_generator'));
    }

    protected function determineMediaName(HasMedia $model, File $file): string
    {
        return $this->mediaName ?? $this->getPathGenerator()->getName($model, $file);
    }

    protected function determineMediaDirectory(HasMedia $model, File $file): string
    {
        return $this->mediaDirectory ?? $this->getPathGenerator()->getDirectory($model, $file);
    }

    protected function determineConversionName(Media $media, MediaConversion $conversion, File $file): string
    {
        return $this->getPathGenerator()->getConversionName($media, $conversion, $file);
    }

    protected function determineConversionDirectory(Media $media, MediaConversion $conversion, File $file): string
    {
        return $this->getPathGenerator()->getConversionDirectory($media, $conversion, $file);
    }
}
