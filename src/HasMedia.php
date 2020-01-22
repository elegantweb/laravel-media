<?php

namespace Elegant\Media;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasMedia
{
    protected array $mediaGroups = [];
    protected array $mediaManipulations = [];

    public static function bootHasMedia(): void
    {
        static::deleted(function ($model) {
            if (!in_array(SoftDeletes::class, class_uses_recursive($entity))) {
                $model->media->each->delete();
            }
        });
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model');
    }

    public function addMedia($file): FileAdder
    {
        return new FileAdder($this, $file);
    }

    public function addMediaGroup(string $name): MediaGroup
    {
        return $this->mediaGroups[$name] = new MediaGroup($name);
    }

    public function getMediaGroup(string $name): MediaGroup
    {
        $this->registerMediaGroups();

        return $this->mediaGroups[$name] ?? new MediaGroup($name);
    }

    public function registerMediaGroups(): void
    {
    }

    public function addMediaManipulation(string $name): MediaManipulation
    {
        return $this->mediaManipulations[$name] = new MediaManipulation($name);
    }

    public function getMediaManipulation(string $name): ?MediaManipulation
    {
        $this->registerMediaManipulations();

        return $this->mediaManipulations[$name] ?? null;
    }

    public function registerMediaManipulations(): void
    {
    }

    public function getFallbackMediaUrl(string $group = 'default', string $manipulation = null): ?string
    {
        return $this->getMediaGroup($group)->getFallbackUrl($manipulation);
    }

    public function getFallbackMediaPath(string $group = 'default', string $manipulation = null): ?string
    {
        return $this->getMediaGroup($group)->getFallbackPath($manipulation);
    }

    public function getFirstMedia(string $group = 'default'): ?Media
    {
        return $this->media()->where('group', $group)->first();
    }

    public function getFirstMediaUrl(string $group = 'default', string $manipulation = null): ?string
    {
        $media = $this->getFirstMedia($group);

        if ($media) {
            return $media->getUrl($manipulation);
        } else {
            return $this->getFallbackMediaUrl($group, $manipulation);
        }
    }

    public function getFirstMediaPath(string $group = 'default', string $manipulation = null): ?string
    {
        $media = $this->getFirstMedia($group);

        if ($media) {
            return $media->getPath($manipulation);
        } else {
            return $this->getFallbackMediaPath($group, $manipulation);
        }
    }

    public function hasMedia(string $group = 'default'): bool
    {
        return $this->media()->where('group', $group)->exists();
    }

    public function getMedia(string $group = 'default'): Collection
    {
        return $this->media()->where('group', $group)->get();
    }
}
