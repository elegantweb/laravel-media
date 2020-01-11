<?php

namespace Elegant\Media;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasMedia
{
    protected array $mediaGroups = [];

    public static function bootHasMedia(): void
    {
        static::deleted(function ($model) {
            if (!in_array(SoftDeletes::class, class_uses_recursive($entity))) {
                $model->medias()->each->delete();
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
        $group = new MediaGroup($name);

        $this->mediaGroups[$name] = $group;

        return $group;
    }

    public function getMediaGroup(string $name): MediaGroup
    {
        $this->registerMediaGroups();

        return $this->mediaGroups[$name] ?? new MediaGroup($name);
    }

    public function registerMediaGroups(): void
    {
    }

    public function getFallbackMediaUrl(string $group = 'default', string $conversion = null): ?string
    {
        return $this->getMediaGroup($group)->getFallbackUrl($conversion);
    }

    public function getFallbackMediaPath(string $group = 'default', string $conversion = null): ?string
    {
        return $this->getMediaGroup($group)->getFallbackPath($conversion);
    }

    public function getFirstMedia(string $group = 'default'): ?Medium
    {
        return $this->medias()->where('group', $group)->first();
    }

    public function getFirstMediaUrl(string $group = 'default', string $conversion = null): ?string
    {
        $media = $this->getFirstMedia($group);

        if ($media) {
            return $media->getUrl($conversion);
        } else {
            return $this->getFallbackMediaUrl($group, $conversion);
        }
    }

    public function getFirstMediaPath(string $group = 'default', string $conversion = null): ?string
    {
        $media = $this->getFirstMedia($group);

        if ($media) {
            return $media->getPath($conversion);
        } else {
            return $this->getFallbackMediaPath($group, $conversion);
        }
    }

    public function hasMedia(string $group = 'default'): bool
    {
        return $this->getMedia()->isNotEmpty();
    }

    public function getMedia(string $group = 'default'): Collection
    {
        return $this->medias()->where('group', $group)->get();
    }
}
