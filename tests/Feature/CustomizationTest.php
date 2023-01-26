<?php

namespace Elegant\Media\Tests\Feature;

use Elegant\Media\Tests\Fixtures\Models\CustomMedia;
use Elegant\Media\Tests\Fixtures\Models\Post;
use Elegant\Media\Tests\RefreshStorage;
use Elegant\Media\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class CustomizationTest extends TestCase
{
    use RefreshDatabase, RefreshStorage;

    public function test_can_customize_media_model()
    {
        $image = UploadedFile::fake()->image('image.png', 400, 400);

        $post = Post::factory()->create();

        $post->setMediaModel(CustomMedia::class);
        $post->addMedia($image)->toMediaGroup();

        $this->assertInstanceOf(
            CustomMedia::class,
            $post->getFirstMedia(),
        );
    }

    public function test_can_globally_customize_media_model()
    {
        $this->app['config']->set('media.model', CustomMedia::class);

        $image = UploadedFile::fake()->image('image.png', 400, 400);

        $post = Post::factory()->create();

        $post->addMedia($image)->toMediaGroup();

        $this->assertInstanceOf(
            CustomMedia::class,
            $post->getFirstMedia(),
        );
    }
}
