<?php

namespace Elegant\Media;

use DateTimeInterface;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Media extends Model implements Responsable
{
    protected $table = 'media';

    protected $hidden = [
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    protected $attributes = [
        'properties' => '{}',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($media) {
            $media->conversions->each->delete();
        });

        static::deleted(function ($media) {
            $media->file()->delete();
        });
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function conversions(): MorphMany
    {
        return $this->morphMany(config('media.model'), 'model');
    }

    public function hasConversion(string $manipulation): bool
    {
        return $this->conversions()->where('manipulation', $manipulation)->exists();
    }

    /**
     * Retrieves conversation of the media using the provided manipulation name.
     * NOTE: uses relationship data and it won't execute a new query each time called.
     */
    public function getConversion(string $manipulation): ?Media
    {
        return $this->conversions->where('manipulation', $manipulation)->first();
    }

    public function deleteConversion(string $manipulation): bool
    {
        return $this->conversions()->where('manipulation', $manipulation)->delete();
    }

    public function getPathAttribute(): string
    {
        return "{$this->directory}/{$this->name}";
    }

    public function getExtensionAttribute(): string
    {
        return pathinfo($this->name, PATHINFO_EXTENSION);
    }

    public function getFilenameAttribute(): string
    {
        return pathinfo($this->name, PATHINFO_FILENAME);
    }

    public function scopePath($query, string $path): void
    {
        $query->where('name', pathinfo($path, PATHINFO_BASENAME));
        $query->where('directory', pathinfo($path, PATHINFO_DIRNAME));
    }

    public function getPath(string $manipulation = null): ?string
    {
        if (null === $manipulation) return $this->path;

        return $this->getConversion($manipulation)?->path;
    }

    public function getUrl(string $manipulation = null): ?string
    {
        if (null === $manipulation) return $this->file()->getUrl();

        return $this->getConversion($manipulation)?->getUrl();
    }

    public function getTemporaryUrl(DateTimeInterface $expiration, string $manipulation = null, array $options = []): ?string
    {
        if (null === $manipulation) return $this->file()->getTemporaryUrl($expiration, $options);

        return $this->getConversion($manipulation)?->getTemporaryUrl($expiration, null, $options);
    }

    public function download(string $manipulation = null): ?StreamedResponse
    {
        if (null === $manipulation) return $this->file()->download();

        return $this->getConversion($manipulation)?->download();
    }

    public function response(string $manipulation = null): ?StreamedResponse
    {
        if (null === $manipulation) return $this->file()->response();

        return $this->getConversion($manipulation)?->response();
    }

    public function toResponse($request): ?StreamedResponse
    {
        return $this->response();
    }

    /**
     * @return resource|null
     */
    public function stream(string $manipulation = null)
    {
        if (null === $manipulation) return $this->file()->readStream();

        return $this->getConversion($manipulation)?->readStream();
    }

    public function file(): RemoteFile
    {
        return new RemoteFile($this->path, $this->disk);
    }
}
