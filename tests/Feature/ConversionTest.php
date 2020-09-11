<?php

namespace Elegant\Media\Tests\Feature;

use Elegant\Media\Tests\Fixtures\Models\Post;
use Elegant\Media\Tests\RefreshStorage;
use Elegant\Media\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ConversionTest extends TestCase
{
    use RefreshDatabase, RefreshStorage;

    public function test_removes_conversions_alongside_media()
    {
        $image = UploadedFile::fake()->image('avatar.png', 400, 400);

        $post = Post::factory()->create();
        $post->addMediaManipulation('resized')->width(40)->height(40);
        $post->addMediaGroup('default')->useManipulations(['resized']);

        $post->addMedia($image)->toMediaGroup();

        $media = $post->getFirstMedia();
        $path = $post->getFirstMediaPath('default', 'resized');

        $media->delete();

        $this->assertFalse($media->hasConversion('resized'));
        $this->assertFalse(Storage::exists($path));
    }
}
