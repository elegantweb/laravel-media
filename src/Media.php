<?php

namespace Elegant\Media;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Request;
use Illuminate\Http\File;

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

    public function getConversion(string $name): ?Media
    {
        return $this->conversions()->where('group', $name)->first();
    }

    public function getPathAttribute()
    {
        return sprintf("%s/%s", $this->directory, $this->name);
    }

    public function getExtensionAttribute()
    {
        return pathinfo($this->name, PATHINFO_EXTENSION);
    }

    public function getFilenameAttribute()
    {
        return pathinfo($this->name, PATHINFO_FILENAME);
    }

    public function getPath(string $conversion = null): ?string
    {
        if (null === $conversion) return $this->path;

        return optional($this->getConversion($conversion))->path;
    }

    public function getUrl(string $conversion = null): ?string
    {
        return Storage::disk($this->disk)->url($this->getPath($conversion));
    }

    public function download(string $conversion = null)
    {
        return Storage::disk($this->disk)->download($this->getPath($conversion));
    }

    public function response(string $conversion = null)
    {
        return Storage::disk($this->disk)->response($this->getPath($conversion));
    }

    public function toResponse($request)
    {
        return $this->response();
    }

    public function storeFile(File $file, bool $preserveOriginal = false): void
    {
        if (!$preserveOriginal) $this->deleteFile();

        Storage::disk($this->disk)->putFileAs($this->directory, $file, $this->name);
    }

    public function deleteFile(): void
    {
        Storage::disk($this->disk)->delete($this->path);
    }

    public function deleteConversions(): void
    {
        $this->conversions->each->delete();
    }
}
