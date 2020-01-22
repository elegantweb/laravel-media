<?php

namespace Elegant\Media;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\FileHelpers;
use Symfony\Component\Mime\MimeTypes;

class RemoteFile
{
    use FileHelpers;

    protected $disk;
    protected $path;

    public function __construct($path, $disk)
    {
        $this->path = $path;
        $this->disk = $disk;
    }

    public function getDisk(): string
    {
        return $this->disk;
    }

    public function getUrl(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getTemporaryUrl(DateTimeInterface $expiration, array $options = []): string
    {
        return Storage::disk($this->disk)->temporaryUrl($this->path, $expiration, $options);
    }

    public function download()
    {
        return Storage::disk($this->disk)->download($this->path);
    }

    public function response()
    {
        return Storage::disk($this->disk)->response($this->path);
    }

    public function readStream()
    {
        return Storage::disk($this->disk)->readStream($this->path);
    }

    public function writeStream(resource $resource, array $options = []): bool
    {
        return Storage::disk($this->disk)->writeStream($this->path, $resource, $options);
    }

    public function getMimeType(): ?string
    {
        return Storage::disk($this->disk)->mimeType($this->path) ?: null;
    }

    public function exists(): bool
    {
        return Storage::disk($this->disk)->exists($this->path);
    }

    public function missing(): bool
    {
        return !$this->exists();
    }

    public function size(): int
    {
        return Storage::disk($this->disk)->size($this->path);
    }

    public function delete(): bool
    {
        return Storage::disk($this->disk)->delete($this->path);
    }

    public function getRealPath(): string
    {
        return $this->path;
    }

    public function guessExtension(): ?string
    {
        return MimeTypes::getDefault()->getExtensions($this->getMimeType())[0] ?? null;
    }
}
