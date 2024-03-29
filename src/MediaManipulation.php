<?php

namespace Elegant\Media;

use Elegant\Media\Concerns\InteractsWithImage;

class MediaManipulation
{
    use InteractsWithImage;

    protected string $name;
    protected ?string $diskName = null;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function useDisk(string $name): static
    {
        $this->diskName = $name;

        return $this;
    }

    public function getDiskName(): ?string
    {
        return $this->diskName;
    }
}
