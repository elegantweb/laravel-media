<?php

namespace Elegant\Media\Concerns;

use Elegant\Media\RemoteFile;
use Elegant\Media\TemporaryFile;
use Elegant\Media\Image\SepiaFilter;
use Intervention\Image\ImageManagerStatic as Image;
use Intervention\Image\Filters\FilterInterface;

trait InteractsWithImage
{
    protected $actions = [];

    public function width(int $width)
    {
        $this->actions[] = fn($img) => $img->widen($width);

        return $this;
    }

    public function height(int $height)
    {
        $this->actions[] = fn($img) => $img->heighten($height);

        return $this;
    }

    public function fit(int $width, int $height = null, string $position = null)
    {
        $this->actions[] = fn($img) => $img->fit($width, $height, $position);

        return $this;
    }

    public function crop(int $width, int $height, int $x = null, int $y = null)
    {
        $this->actions[] = fn($img) => $img->crop($width, $height, $x, $y);

        return $this;
    }

    public function brightness(int $level)
    {
        $this->actions[] = fn($img) => $img->brightness($level);

        return $this;
    }

    public function contrast(int $level)
    {
        $this->actions[] = fn($img) => $img->contrast($level);

        return $this;
    }

    public function gamma(float $correction)
    {
        $this->actions[] = fn($img) => $img->gamma($correction);

        return $this;
    }

    public function rotate(float $angle, string $bgcolor = null)
    {
        $this->actions[] = fn($img) => $img->rotate($angle, $bgcolor);

        return $this;
    }

    public function flip($mode)
    {
        $this->actions[] = fn($img) => $img->flip($mode);

        return $this;
    }

    public function blur(int $amount = null)
    {
        $this->actions[] = fn($img) => $img->blur($amount);

        return $this;
    }

    public function pixelate(int $size)
    {
        $this->actions[] = fn($img) => $img->pixelate($size);

        return $this;
    }

    public function greyscale()
    {
        $this->actions[] = fn($img) => $img->greyscale();

        return $this;
    }

    public function sepia()
    {
        return $this->filter(new SepiaFilter());
    }

    public function sharpen(int $amount = null)
    {
        $this->actions[] = fn($img) => $img->sharpen($amount);

        return $this;
    }

    public function insert($source, string $position = null, int $x = null, int $y = null)
    {
        $this->actions[] = fn($img) => $img->insert($opacity, $position, $x, $y);

        return $this;
    }

    public function filter(FilterInterface $filter)
    {
        $this->actions[] = fn($img) => $img->filter($filter);

        return $this;
    }

    public function perform($file)
    {
        $image = Image::make($this->localizeFile($file));

        foreach ($this->actions as $action) $action($image);

        // we create another tmp file to save conversion on it
        $tmpfile = new TemporaryFile();

        $image->save($tmpfile);

        // if we don't run this, file will report false stat like size, etc
        clearstatcache(true, $tmpfile->path());

        return $tmpfile;
    }

    protected function localizeFile($file)
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
