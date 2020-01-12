<?php

namespace Elegant\Media;

use Illuminate\Database\Eloquent\Model;

class MediaGroup
{
    protected $name;
    protected $size;
    protected $disk;
    protected $mediaProperties = [];
    protected $mediaConversions = [];
    protected $fallbackUrls = [];
    protected $fallbackPaths = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function onlyKeepLatest(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function singleFile(): self
    {
        $this->onlyKeepLatest(1);

        return $this;
    }

    public function useDisk(string $name): self
    {
        $this->disk = $name;

        return $this;
    }

    public function getDiskName(): string
    {
        return $this->disk ?? config('media.disk');
    }

    public function withProperties(array $properties): self
    {
        $this->mediaProperties = $properties;

        return $this;
    }

    public function getProperties(): array
    {
        return $this->mediaProperties;
    }

    public function useConversions(array $conversions): self
    {
        $this->mediaConversions = $conversions;

        return $this;
    }

    public function getConversions(): array
    {
        return $this->mediaConversions;
    }

    public function useFallbackUrl(string $url, string $conversion = null): self
    {
        $this->fallbackUrls[$conversion] = $url;

        return $this;
    }

    public function getFallbackUrl(string $conversion = null): ?string
    {
        return $this->fallbackUrls[$conversion] ?? $this->fallbackUrls[null] ?? null;
    }

    public function useFallbackPath(string $path, string $conversion = null): self
    {
        $this->fallbackPaths[$conversion] = $path;

        return $this;
    }

    public function getFallbackPath(string $conversion = null): ?string
    {
        return $this->fallbackPaths[$conversion] ?? $this->fallbackPaths[null] ?? null;
    }
}
