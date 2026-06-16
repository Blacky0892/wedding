<?php

namespace Tests\Feature;

use App\Models\WeddingMedia;
use App\Services\YandexDiskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class WeddingMediaDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_image_show_streams_inline_file_from_yandex_disk(): void
    {
        $media = WeddingMedia::create([
            'guest_name' => 'Анна',
            'original_name' => 'photo.png',
            'stored_name' => 'photo.png',
            'disk_path' => '/wedding/originals/photo.png',
            'mime_type' => 'image/png',
            'extension' => 'png',
            'size' => 7,
            'type' => WeddingMedia::TYPE_IMAGE,
            'status' => WeddingMedia::STATUS_UPLOADED,
            'uploaded_at' => now(),
        ]);

        $this->app->instance(YandexDiskService::class, tap(Mockery::mock(YandexDiskService::class), function ($mock): void {
            $mock->shouldReceive('getDownloadUrl')
                ->once()
                ->with('/wedding/originals/photo.png')
                ->andReturn('https://downloader.disk.yandex.ru/photo.png');
        }));

        Http::fake([
            'https://downloader.disk.yandex.ru/photo.png' => Http::response('PNGDATA', 200, [
                'Content-Type' => 'image/png',
            ]),
        ]);

        $response = $this->get(route('wedding.media.show', $media));

        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'inline; filename="photo.png"');
        $response->assertHeader('Content-Type', 'image/png');
        $this->assertSame('PNGDATA', $response->streamedContent());
    }

    public function test_video_show_still_redirects_to_yandex_disk(): void
    {
        $media = WeddingMedia::create([
            'guest_name' => 'Анна',
            'original_name' => 'video.mp4',
            'stored_name' => 'video.mp4',
            'disk_path' => '/wedding/originals/video.mp4',
            'mime_type' => 'video/mp4',
            'extension' => 'mp4',
            'size' => 7,
            'type' => WeddingMedia::TYPE_VIDEO,
            'status' => WeddingMedia::STATUS_UPLOADED,
            'uploaded_at' => now(),
        ]);

        $this->app->instance(YandexDiskService::class, tap(Mockery::mock(YandexDiskService::class), function ($mock): void {
            $mock->shouldReceive('getDownloadUrl')
                ->once()
                ->with('/wedding/originals/video.mp4')
                ->andReturn('https://downloader.disk.yandex.ru/video.mp4');
        }));

        $this->get(route('wedding.media.show', $media))
            ->assertRedirect('https://downloader.disk.yandex.ru/video.mp4');
    }
}
