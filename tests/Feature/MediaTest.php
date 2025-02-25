<?php

namespace Elegant\Media\Tests\Feature;

use Elegant\Media\Tests\Fixtures\Models\Order;
use Elegant\Media\Tests\Fixtures\Models\Post;
use Elegant\Media\Tests\RefreshStorage;
use Elegant\Media\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaTest extends TestCase
{
    use RefreshDatabase, RefreshStorage;

    public function test_can_retrieve_url()
    {
        $image = UploadedFile::fake()->image('image.png', 400, 400);

        $post = Post::factory()->create();

        $post->addMedia($image)->toMediaGroup();

        $this->assertEquals(
            Storage::url($post->getFirstMediaPath()),
            $post->getFirstMediaUrl(),
        );
    }

    public function test_removes_media_alongside_model()
    {
        $image = UploadedFile::fake()->image('image.png', 400, 400);

        $post = Post::factory()->create();

        $post->addMedia($image)->toMediaGroup();

        $path = $post->getFirstMediaPath();

        $post->delete();

        $this->assertFalse($post->hasMedia());
        $this->assertFalse(Storage::exists($path));
    }

    public function test_keeps_media_when_soft_deleting_model()
    {
        $image = UploadedFile::fake()->image('image.png', 400, 400);

        $order = Order::factory()->create();

        $order->addMedia($image)->toMediaGroup();

        $path = $order->getFirstMediaPath();

        $order->delete();

        $this->assertTrue($order->hasMedia());
        $this->assertTrue(Storage::exists($path));
    }

    public function test_removes_media_when_force_deleting_model()
    {
        $image = UploadedFile::fake()->image('image.png', 400, 400);

        $order = Order::factory()->create();

        $order->addMedia($image)->toMediaGroup();

        $path = $order->getFirstMediaPath();

        $order->forceDelete();

        $this->assertFalse($order->hasMedia());
        $this->assertFalse(Storage::exists($path));
    }
}
