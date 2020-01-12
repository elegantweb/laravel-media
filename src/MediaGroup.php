<?php

namespace Elegant\Media;

use Illuminate\Database\Eloquent\Model;

class MediaGroup
{
    protected $name;
    protected $size;
    protected $diskName;
    protected $mediaProperties = [];
    protected $mediaManipulations = [];
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
        $this->diskName = $name;

        return $this;
    }

    public function getDiskName(): string
    {
        return $this->diskName ?? config('media.disk');
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

    public function useManipulations(array $manipulations): self
    {
        $this->mediaManipulations = $manipulations;

        return $this;
    }

    public function getManipulations(): array
    {
        return $this->mediaManipulations;
    }

    public function useFallbackUrl(string $url, string $manipulation = null): self
    {
        $this->fallbackUrls[$manipulation] = $url;

        return $this;
    }

    public function getFallbackUrl(string $manipulation = null): ?string
    {
        return $this->fallbackUrls[$manipulation] ?? $this->fallbackUrls[null] ?? null;
    }

    public function useFallbackPath(string $path, string $manipulation = null): self
    {
        $this->fallbackPaths[$manipulation] = $path;

        return $this;
    }

    public function getFallbackPath(string $manipulation = null): ?string
    {
        return $this->fallbackPaths[$manipulation] ?? $this->fallbackPaths[null] ?? null;
    }
}
