<?php

namespace Elegant\Media;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Media extends Model
{
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
        return $this->conversions()->where('name', $name)->first();
    }

    public function getUrl(string $conversion = null): ?string
    {
        return Storage::disk($this->disk)->url($this->getPath($conversion));
    }

    public function getPath(string $conversion = null): ?string
    {
        if (null === $conversion) {
            return sprintf("%s/%s", $this->directory, $this->filename);
        } else {
            return optional($this->getConversion($conversion))->getPath();
        }
    }

    public function storeFile(File $file, bool $preserveOriginal = false): void
    {
        if (!$preserveOriginal) $this->deleteFile();

        Storage::disk($this->disk)->putFileAs($this->directory, $file, $this->filename);
    }

    public function deleteFile(): void
    {
        Storage::disk($this->disk)->delete($this->getPath());
    }

    public function deleteConversions(): void
    {
        $this->conversions()->each->delete();
    }
}
