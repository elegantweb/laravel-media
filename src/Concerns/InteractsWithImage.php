<?php

namespace Elegant\Media\Concerns;

use Elegant\Media\RemoteFile;
use Elegant\Media\TemporaryFile;
use Elegant\Media\Image\SepiaFilter;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManagerStatic as Image;
use Intervention\Image\Filters\FilterInterface;

trait InteractsWithImage
{
    protected array $actions = [];

    public function width(int $width): static
    {
        $this->actions[] = fn ($img) => $img->widen($width);

        return $this;
    }

    public function height(int $height): static
    {
        $this->actions[] = fn ($img) => $img->heighten($height);

        return $this;
    }

    public function fit(int $width, int $height = null, string $position = null): static
    {
        $this->actions[] = fn ($img) => $img->fit($width, $height, $position);

        return $this;
    }

    public function crop(int $width, int $height, int $x = null, int $y = null): static
    {
        $this->actions[] = fn ($img) => $img->crop($width, $height, $x, $y);

        return $this;
    }

    public function brightness(int $level): static
    {
        $this->actions[] = fn ($img) => $img->brightness($level);

        return $this;
    }

    public function contrast(int $level): static
    {
        $this->actions[] = fn ($img) => $img->contrast($level);

        return $this;
    }

    public function gamma(float $correction): static
    {
        $this->actions[] = fn ($img) => $img->gamma($correction);

        return $this;
    }

    public function rotate(float $angle, string $bgcolor = null): static
    {
        $this->actions[] = fn ($img) => $img->rotate($angle, $bgcolor);

        return $this;
    }

    public function flip($mode): static
    {
        $this->actions[] = fn ($img) => $img->flip($mode);

        return $this;
    }

    public function blur(int $amount = null): static
    {
        $this->actions[] = fn ($img) => $img->blur($amount);

        return $this;
    }

    public function pixelate(int $size): static
    {
        $this->actions[] = fn ($img) => $img->pixelate($size);

        return $this;
    }

    public function greyscale(): static
    {
        $this->actions[] = fn ($img) => $img->greyscale();

        return $this;
    }

    public function sepia(): static
    {
        return $this->filter(new SepiaFilter());
    }

    public function sharpen(int $amount = null): static
    {
        $this->actions[] = fn ($img) => $img->sharpen($amount);

        return $this;
    }

    public function insert($source, string $position = null, int $x = null, int $y = null): static
    {
        $this->actions[] = fn ($img) => $img->insert($source, $position, $x, $y);

        return $this;
    }

    public function filter(FilterInterface $filter): static
    {
        $this->actions[] = fn ($img) => $img->filter($filter);

        return $this;
    }

    public function perform($file): TemporaryFile
    {
        $image = Image::make($this->localizeFile($file));

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
