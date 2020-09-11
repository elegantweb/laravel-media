<?php

namespace Elegant\Media\Tests\Feature;

use Elegant\Media\Tests\Fixtures\Models\Post;
use Elegant\Media\Tests\RefreshStorage;
use Elegant\Media\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ManipulationTest extends TestCase
{
    use RefreshDatabase, RefreshStorage;

    public function test_can_apply_manipulations()
    {
        $image = UploadedFile::fake()->image('avatar.png', 400, 400);

        $post = Post::factory()->create();
        $post->addMediaManipulation('resized')->width(40)->height(40);
        $post->addMediaGroup('default')->useManipulations(['resized']);

        $post->addMedia($image)->toMediaGroup();

        $path = $post->getFirstMediaPath('default', 'resized');

        $this->assertTrue(Storage::exists($path));

        $size = getimagesizefromstring(Storage::get($path));

        $this->assertNotFalse($size);
        $this->assertEquals($size[0], 40);
        $this->assertEquals($size[1], 40);
    }
}
