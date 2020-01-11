<?php

namespace Elegant\Media;

use Intervention\Image\ImageManagerStatic as Image;

trait InteractsWithImage
{
    protected $actions = [];

    public function width(int $width)
    {
        $this->actions[] = ($img) => $img->widen($width);

        return $this;
    }

    public function height(int $height)
    {
        $this->actions[] = ($img) => $img->heighten($height);

        return $this;
    }

    public function fit(int $width, int $height = null, string $position = null)
    {
        $this->actions[] = ($img) => $img->fit($width, $height, $position);

        return $this;
    }

    public function crop(int $width, int $height, int $x = null, int $y = null)
    {
        $this->actions[] = ($img) => $img->crop($width, $height, $x, $y);

        return $this;
    }

    public function brightness(int $level)
    {
        $this->actions[] = ($img) => $img->brightness($level);

        return $this;
    }

    public function contrast(int $level)
    {
        $this->actions[] = ($img) => $img->contrast($level);

        return $this;
    }

    public function gamma(float $correction)
    {
        $this->actions[] = ($img) => $img->gamma($correction);

        return $this;
    }

    public function rotate(float $angle, string $bgcolor = null)
    {
        $this->actions[] = ($img) => $img->rotate($angle, $bgcolor);

        return $this;
    }

    public function flip($mode)
    {
        $this->actions[] = ($img) => $img->flip($mode);

        return $this;
    }

    public function blur(int $amount = null)
    {
        $this->actions[] = ($img) => $img->blur($amount);

        return $this;
    }

    public function pixelate(int $size)
    {
        $this->actions[] = ($img) => $img->pixelate($size);

        return $this;
    }

    public function greyscale()
    {
        $this->actions[] = ($img) => $img->greyscale();

        return $this;
    }

    public function sharpen(int $amount = null)
    {
        $this->actions[] = ($img) => $img->sharpen($amount);

        return $this;
    }

    public function insert($source, string $position = null, int $x = null, int $y = null)
    {
        $this->actions[] = ($img) => $img->insert($opacity, $position, $x, $y);

        return $this;
    }

    public function perform(File $file)
    {
        $image = Image::make($file);

        foreach ($this->actions as $action) $action($image);

        $tmpfile = join(DIRECTORY_SEPARATOR, sys_get_temp_dir(), uniqid('ELEGANT_MEDIA_'));

        $image->save($tmpfile);

        return new File($tmpfile);
    }
}
