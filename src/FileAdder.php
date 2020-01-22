<?php

namespace Elegant\Media;

use Elegant\Media\Contracts\PathGenerator;
use Elegant\Media\Contracts\HasMedia;
use Illuminate\Http\UploadedFile as File;

class FileAdder
{
    protected $model;
    protected $file;
    protected $preserveOriginal = false;
    protected $mediaName;
    protected $mediaDirectory;
    protected $mediaProperties = [];
    protected $mediaManipulations = [];

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

    public function useName(string $name): self
    {
        $this->mediaName = $name;

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

    public function useManipulations(array $manipulations): self
    {
        $this->mediaManipulations = $manipulations;

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

        $manipulations = array_merge($this->mediaManipulations, $group->getManipulations());

        foreach ($manipulations as $manipulation) {
            $this->toMediaConversion($manipulation, $media);
        }

        $collection = $this->model->getMedia($group->getName());

        if (null !== $size = $group->getSize() and $size < $count = $collection->count()) {
            $collection->take($count - $size)->each->delete();
        }
    }

    public function toMediaConversion(string $manipulationName, Media $originalMedia): void
    {
        $manipulation = $this->model->getMediaManipulation($manipulationName);

        $file = $manipulation->perform($this->file);

        $media = new Media();
        $media->disk = $manipulation->getDiskName() ?? $originalMedia->disk;
        $media->group = 'conversions';
        $media->manipulation = $manipulation->getName();
        $media->mime_type = $file->getMimeType();
        $media->size = $file->getSize();
        $media->name = $this->determineConversionName($originalMedia, $manipulation, $this->file);
        $media->directory = $this->determineConversionDirectory($originalMedia, $manipulation, $this->file);
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

    protected function determineConversionName(Media $media, MediaManipulation $manipulation, File $file): string
    {
        return $this->getPathGenerator()->getConversionName($media, $manipulation, $file);
    }

    protected function determineConversionDirectory(Media $media, MediaManipulation $manipulation, File $file): string
    {
        return $this->getPathGenerator()->getConversionDirectory($media, $manipulation, $file);
    }
}
