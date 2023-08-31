<?php

namespace Elegant\Media;

use Elegant\Media\Contracts\PathGenerator;
use Elegant\Media\Contracts\HasMedia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Traits\Macroable;

class FileAdder
{
    use Macroable;

    protected $model;
    /**
     * @var \Illuminate\Http\File|\Illuminate\Http\UploadedFile|RemoteFile
     */
    protected $file;
    protected $preserveOriginal = false;
    protected $mediaName;
    protected $mediaDirectory;
    protected $mediaProperties = [];

    public function __construct(HasMedia $model, $file)
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

    public function toMediaGroup(string $name = 'default'): void
    {
        $group = $this->model->getMediaGroup($name);

        if (null === $group) {
            $group = new MediaGroup($name);
        }

        $mediaClass = config('media.model');
        $media = new $mediaClass();
        $media->disk = $group->getDiskName();
        $media->group = $group->getName();
        $media->properties = array_merge($this->mediaProperties, $group->getProperties());
        $media->mime_type = $this->file->getMimeType();
        $media->size = $this->file->getSize();
        $media->name = $this->determineMediaName($this->model, $this->file);
        $media->directory = $this->determineMediaDirectory($this->model, $this->file);
        $this->model->media()->save($media);

        if ($this->file instanceof RemoteFile) {
            Storage::disk($media->disk)->put($media->path, $this->file->readStream());
        } else {
            Storage::disk($media->disk)->putFileAs($media->directory, $this->file, $media->name);
        }

        $manipulations = $group->getManipulations();

        foreach ($manipulations as $manipulation) {
            $this->toMediaConversion($manipulation, $media);
        }

        if (!$this->preserveOriginal) {
            $this->deleteFile();
        }

        $collection = $this->model->media()->where('group', $group->getName())->get();

        if (null !== $size = $group->getSize() and $size < $count = $collection->count()) {
            $collection->take($count - $size)->each->delete();
        }
    }

    public function toMediaConversion(string $manipulationName, Media $originalMedia): void
    {
        $manipulation = $this->model->getMediaManipulation($manipulationName);

        if (null === $manipulation) {
            throw new \Exception("Manipulation {$manipulationName} is not registered.");
        }

        $file = $manipulation->perform($this->file);

        $mediaClass = config('media.model');
        $media = new $mediaClass();
        $media->disk = $manipulation->getDiskName() ?? $originalMedia->disk;
        $media->group = 'conversions';
        $media->manipulation = $manipulation->getName();
        $media->mime_type = $file->getMimeType();
        $media->size = $file->getSize();
        $media->name = $this->determineConversionName($originalMedia, $manipulation, $this->file);
        $media->directory = $this->determineConversionDirectory($originalMedia, $manipulation, $this->file);
        $originalMedia->conversions()->save($media);

        Storage::disk($media->disk)->putFileAs($media->directory, $file, $media->name);
    }

    protected function deleteFile()
    {
        if ($this->file instanceof RemoteFile) {
            $this->file->delete();
        } else {
            unlink($this->file->path());
        }
    }

    protected function getPathGenerator(): PathGenerator
    {
        return resolve(config('media.path_generator'));
    }

    protected function determineMediaName(HasMedia $model, $file): string
    {
        return $this->mediaName ?? $this->getPathGenerator()->getName($model, $file);
    }

    protected function determineMediaDirectory(HasMedia $model, $file): string
    {
        return $this->mediaDirectory ?? $this->getPathGenerator()->getDirectory($model, $file);
    }

    protected function determineConversionName(Media $media, MediaManipulation $manipulation, $file): string
    {
        return $this->getPathGenerator()->getConversionName($media, $manipulation, $file);
    }

    protected function determineConversionDirectory(Media $media, MediaManipulation $manipulation, $file): string
    {
        return $this->getPathGenerator()->getConversionDirectory($media, $manipulation, $file);
    }
}
