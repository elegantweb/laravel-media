<?php

namespace Elegant\Media\Tests\Feature;

use Elegant\Media\Tests\Fixtures\Models\Post;
use Elegant\Media\Tests\RefreshStorage;
use Elegant\Media\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdderTest extends TestCase
{
    use RefreshDatabase, RefreshStorage;

    public function test_can_add_media()
    {
        $image = UploadedFile::fake()->image('image.png', 400, 400);

        $post = Post::factory()->create();

        $post->addMedia($image)->toMediaGroup();

        $this->assertTrue($post->hasMedia());
        $this->assertTrue(Storage::exists($post->getFirstMediaPath()));
    }

    public function test_can_add_with_custom_properties()
    {
        $image = UploadedFile::fake()->image('image.png', 400, 400);

        $post = Post::factory()->create();

        $media = $post->addMedia($image);
        $media->withProperties(['key' => 'value']);
        $media->toMediaGroup();

        $this->assertEquals($post->getFirstMedia()->properties['key'], 'value');
    }
}
