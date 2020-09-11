<?php

namespace Elegant\Media\Tests\Feature;

use Elegant\Media\Tests\Fixtures\Models\Post;
use Elegant\Media\Tests\RefreshStorage;
use Elegant\Media\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class GroupTest extends TestCase
{
    use RefreshDatabase, RefreshStorage;

    public function test_can_upload_to_custom_groups()
    {
        $image = UploadedFile::fake()->image('image.png', 400, 400);

        $post = Post::factory()->create();

        $post->addMedia($image)->toMediaGroup('custom');

        $this->assertTrue($post->hasMedia('custom'));
        $this->assertTrue(Storage::exists($post->getFirstMediaPath('custom')));
    }

    public function test_groups_can_limit_number_of_files()
    {
        $image1 = UploadedFile::fake()->image('image1.png', 400, 400);
        $image2 = UploadedFile::fake()->image('image2.png', 400, 400);

        $post = Post::factory()->create();
        $post->addMediaGroup('default')->onlyKeepLatest(1);

        $post->addMedia($image1)->toMediaGroup();
        $post->addMedia($image2)->toMediaGroup();

        $this->assertEquals(count($post->getMedia()), 1);
        $this->assertEquals($post->getFirstMedia()->name, $image2->hashName());
    }

    public function test_retrieves_fallback_url_when_media_does_not_exists()
    {
        $post = Post::factory()->create();
        $post->addMediaGroup('default')->useFallbackUrl('http://example.com');

        $this->assertEquals($post->getFirstMediaUrl(), 'http://example.com');
    }

    public function test_retrieves_fallback_path_when_media_does_not_exists()
    {
        $post = Post::factory()->create();
        $post->addMediaGroup('default')->useFallbackPath('/example');

        $this->assertEquals($post->getFirstMediaPath(), '/example');
    }
}
