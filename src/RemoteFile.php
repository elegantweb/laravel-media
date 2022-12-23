<?php

namespace Elegant\Media;

use InvalidArgumentException;
use DateTimeInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\FileHelpers;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Mime\MimeTypes;

class RemoteFile
{
    use FileHelpers;

    protected string $diskName;
    protected string $path;

    public function __construct(string $path, string $diskName)
    {
        $this->path = $path;
        $this->diskName = $diskName;
    }

    public function getDiskName(): string
    {
        return $this->diskName;
    }

    public function getUrl(): string
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter */
        $disk = Storage::disk($this->diskName);

        return $disk->url($this->path);
    }

    public function getTemporaryUrl(DateTimeInterface $expiration, array $options = []): string
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter */
        $disk = Storage::disk($this->diskName);

        return $disk->temporaryUrl($this->path, $expiration, $options);
    }

    public function download(): StreamedResponse
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter */
        $disk = Storage::disk($this->diskName);

        return $disk->download($this->path);
    }

    public function response(): StreamedResponse
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter */
        $disk = Storage::disk($this->diskName);

        return $disk->response($this->path);
    }

    /**
     * @return resource|null
     */
    public function readStream()
    {
        return Storage::disk($this->diskName)->readStream($this->path);
    }

    /**
     * @param resource $resource
     */
    public function writeStream($resource, array $options = []): bool
    {
        if (!is_resource($resource))
            throw new InvalidArgumentException(sprintf('Argument resource must be a valid resource type. %s given.', gettype($resource)));

        return Storage::disk($this->disk)->writeStream($this->path, $resource, $options);
    }

    public function getMimeType(): ?string
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter */
        $disk = Storage::disk($this->diskName);

        return $disk->mimeType($this->path) ?: null;
    }

    public function getSize(): int
    {
        return Storage::disk($this->diskName)->size($this->path);
    }

    public function exists(): bool
    {
        return Storage::disk($this->diskName)->exists($this->path);
    }

    public function missing(): bool
    {
        return !$this->exists();
    }

    public function delete(): bool
    {
        return Storage::disk($this->diskName)->delete($this->path);
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
