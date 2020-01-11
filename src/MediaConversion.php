<?php

namespace Elegant\Media;

use Elegant\Media\Concerns\InteractsWithImage;
use Illuminate\Http\File;

class MediaConversion
{
    use InteractsWithImage;

    protected $name;
    protected $disk;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function useDisk(string $name): self
    {
        $this->disk = $name;

        return $this;
    }

    public function getDiskName(): ?string
    {
        return $this->disk;
    }
}
