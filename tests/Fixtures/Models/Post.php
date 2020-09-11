<?php

namespace Elegant\Media\Tests\Fixtures\Models;

use Elegant\Media\Contracts\HasMedia as HasMediaContract;
use Elegant\Media\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model implements HasMediaContract
{
    use HasFactory, HasMedia;
}
