<?php

namespace Elegant\Media;

use DateTimeInterface;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile as File;

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

        static::deleted(function ($medium) {
            $medium->deleteConversions();
            $medium->deleteFile();
        });
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function conversions(): MorphMany
    {
        return $this->morphMany(Media::class, 'model');
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

    public function deleteConversions(): void
    {
        $this->conversions->each->delete();
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
        return Storage::disk($this->disk)->url($this->getPath($manipulation));
    }

    public function getTemporaryUrl(DateTimeInterface $expiration, string $manipulation = null, array $options): ?string
    {
        return Storage::disk($this->disk)->temporaryUrl($this->getPath($manipulation), $expiration, $options);
    }

    public function download(string $manipulation = null)
    {
        return Storage::disk($this->disk)->download($this->getPath($manipulation));
    }

    public function response(string $manipulation = null)
    {
        return Storage::disk($this->disk)->response($this->getPath($manipulation));
    }

    public function toResponse($request)
    {
        return $this->response();
    }

    public function stream(string $manipulation = null)
    {
        return Storage::disk($this->disk)->readStream($this->getPath($manipulation));
    }

    public function storeFile(File $file, bool $preserveOriginal = false): void
    {
        if (!$preserveOriginal) $this->deleteFile();

        Storage::disk($this->disk)->putFileAs($this->directory, $file, $this->name);
    }

    public function fileExists(): bool
    {
        return Storage::disk($this->disk)->exists($this->path);
    }

    public function fileMissing(): bool
    {
        return !$this->fileExists();
    }

    public function deleteFile(): void
    {
        Storage::disk($this->disk)->delete($this->path);
    }
}
