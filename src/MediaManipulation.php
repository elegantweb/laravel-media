<?php

namespace Elegant\Media;

use Elegant\Media\Concerns\InteractsWithImage;
use Illuminate\Http\File;

class MediaManipulation
{
    use InteractsWithImage;

    protected $name;
    protected $diskName;

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
        $this->diskName = $name;

        return $this;
    }

    public function getDiskName(): ?string
    {
        return $this->diskName;
    }
}
