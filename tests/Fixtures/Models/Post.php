<?php

namespace Elegant\Media\Tests\Fixtures\Models;

use Elegant\Media\Contracts\HasMedia as HasMediaContract;
use Elegant\Media\HasMedia;
use Elegant\Media\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model implements HasMediaContract
{
    use HasFactory, HasMedia;

    protected $mediaModel;

    public function setMediaModel(string $class): void
    {
        $this->mediaModel = $class;
    }

    public function getMediaModel()
    {
        return $this->mediaModel ?? config('media.model', Media::class);
    }
}
