<?php

namespace Elegant\Media\Tests\Fixtures\Models;

use Elegant\Media\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory, HasMedia;
}
