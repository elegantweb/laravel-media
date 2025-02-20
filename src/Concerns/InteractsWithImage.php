<?php

namespace Elegant\Media\Concerns;

use Elegant\Media\RemoteFile;
use Elegant\Media\TemporaryFile;
use Elegant\Media\Image\SepiaModifier;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Interfaces\ModifierInterface;

trait InteractsWithImage
{
    protected array $actions = [];

    public function width(int $width): static
    {
        $this->actions[] = fn (ImageInterface $img) => $img->resize(width: $width);

        return $this;
    }

    public function height(int $height): static
    {
        $this->actions[] = fn (ImageInterface $img) => $img->resize(height: $height);

        return $this;
    }

    public function cover(int $width, int $height, string $position = 'center'): static
    {
        $this->actions[] = fn (ImageInterface $img) => $img->cover($width, $height, $position);

        return $this;
    }

    public function crop(int $width, int $height, int $x = 0, int $y = 0): static
    {
        $this->actions[] = fn (ImageInterface $img) => $img->crop($width, $height, $x, $y);

        return $this;
    }

    public function brightness(int $level): static
    {
        $this->actions[] = fn (ImageInterface $img) => $img->brightness($level);

        return $this;
    }

    public function contrast(int $level): static
    {
        $this->actions[] = fn (ImageInterface $img) => $img->contrast($level);

        return $this;
    }

    public function gamma(float $gamma): static
    {
        $this->actions[] = fn (ImageInterface $img) => $img->gamma($gamma);

        return $this;
    }

    public function rotate(float $angle, mixed $background = 'ffffff'): static
    {
        $this->actions[] = fn (ImageInterface $img) => $img->rotate($angle, $background);

        return $this;
    }

    public function flip(): static
    {
        $this->actions[] = fn (ImageInterface $img) => $img->flip();

        return $this;
    }

    public function blur(int $amount = 5): static
    {
        $this->actions[] = fn (ImageInterface $img) => $img->blur($amount);

        return $this;
    }

    public function pixelate(int $size): static
    {
        $this->actions[] = fn (ImageInterface $img) => $img->pixelate($size);

        return $this;
    }

    public function greyscale(): static
    {
        $this->actions[] = fn (ImageInterface $img) => $img->greyscale();

        return $this;
    }

    public function sepia(): static
    {
        return $this->modify(new SepiaModifier());
    }

    public function sharpen(int $amount = 10): static
    {
        $this->actions[] = fn (ImageInterface $img) => $img->sharpen($amount);

        return $this;
    }

    public function insert(mixed $element, string $position = 'top-left', int $x = 0, int $y = 0): static
    {
        $this->actions[] = fn (ImageInterface $img) => $img->place($element, $position, $x, $y);

        return $this;
    }

    public function modify(ModifierInterface $modifier): static
    {
        $this->actions[] = fn (ImageInterface $img) => $img->modify($modifier);

        return $this;
    }

    public function toJpeg(int $quality = 75, bool $progressive = false, bool $strip = true)
    {
        $this->actions[] = fn (ImageInterface $img) => $img->toJpeg($quality, $progressive, $strip);

        return $this;
    }

    public function toWebp(int $quality = 75, bool $strip = true)
    {
        $this->actions[] = fn (ImageInterface $img) => $img->toWebp($quality, $strip);

        return $this;
    }

    public function toPng(bool $interlaced = false, bool $indexed = false)
    {
        $this->actions[] = fn (ImageInterface $img) => $img->toPng($interlaced, $indexed);

        return $this;
    }

    public function perform($file): TemporaryFile
    {
        $manager = ImageManager::gd();
        $image = $manager->read($this->localizeFile($file));

        foreach ($this->actions as $action) $action($image);

        // we create another tmp file to save conversion on it
        $tmpfile = new TemporaryFile();

        $image->save($tmpfile);

        // if we don't run this, file will report false stat (like invalid size, etc)
        clearstatcache(true, $tmpfile->path());

        return $tmpfile;
    }

    protected function localizeFile(File|UploadedFile|RemoteFile $file): File|UploadedFile|TemporaryFile
    {
        // if file is not remote, we do nothing
        if (!$file instanceof RemoteFile) return $file;

        // otherwise we create a local tmp file to work on it
        $tmpfile = new TemporaryFile();

        $handle = fopen($tmpfile->path(), 'w');
        stream_copy_to_stream($file->readStream(), $handle);
        fclose($handle);

        return $tmpfile;
    }
}
