<?php

namespace Elegant\Media;

use DateTimeInterface;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Media extends Model implements Responsable
{
    protected $casts = [
        'properties' => 'array',
    ];

    protected $attributes = [
        'properties' => '[]',
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

    public function getConversion(string $manipulation): ?Media
    {
        return $this->conversions()->where('manipulation', $manipulation)->first();
    }

    public function deleteConversion(string $manipulation): bool
    {
        return $this->conversions()->where('manipulation', $manipulation)->delete();
    }

    public function getPathAttribute()
    {
        return "{$this->directory}/{$this->name}";
    }

    public function getExtensionAttribute()
    {
        return pathinfo($this->name, PATHINFO_EXTENSION);
    }

    public function getFilenameAttribute()
    {
        return pathinfo($this->name, PATHINFO_FILENAME);
    }

    public function scopePath($query, string $path)
    {
        $query->where('name', pathinfo($path, PATHINFO_BASENAME));
        $query->where('directory', pathinfo($path, PATHINFO_DIRNAME));
    }

    public function getPath(string $manipulation = null): ?string
    {
        if (null === $manipulation) return $this->path;

        return optional($this->getConversion($manipulation))->path;
    }

    public function getUrl(string $manipulation = null): ?string
    {
        if (null === $manipulation) return $this->file()->getUrl();

        return optional($this->getConversion($manipulation))->getUrl();
    }

    public function getTemporaryUrl(DateTimeInterface $expiration, string $manipulation = null, array $options = []): ?string
    {
        if (null === $manipulation) return $this->file()->getTemporaryUrl($expiration, $options);

        return optional($this->getConversion($manipulation))->getTemporaryUrl($expiration, null, $options);
    }

    public function download(string $manipulation = null)
    {
        if (null === $manipulation) return $this->file()->download();

        return optional($this->getConversion($manipulation))->download();
    }

    public function response(string $manipulation = null)
    {
        if (null === $manipulation) return $this->file()->response();

        return optional($this->getConversion($manipulation))->response();
    }

    public function toResponse($request)
    {
        return $this->response();
    }

    public function stream(string $manipulation = null)
    {
        if (null === $manipulation) return $this->file()->readStream();

        return optional($this->getConversion($manipulation))->readStream();
    }

    public function file(): RemoteFile
    {
        return new RemoteFile($this->path, $this->disk);
    }
}
