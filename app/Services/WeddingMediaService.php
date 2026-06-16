<?php

namespace App\Services;

use App\Models\WeddingMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class WeddingMediaService
{
    public function __construct(
        private readonly YandexDiskService $yandexDisk,
        private readonly string $thumbnailDisk = 'public',
        private readonly string $thumbnailDirectory = 'wedding/thumbs',
    ) {}

    /**
     * @param  array<int, UploadedFile>  $files
     * @return Collection<int, WeddingMedia>
     */
    public function storeUploads(string $guestName, array $files): Collection
    {
        return collect($files)->map(fn (UploadedFile $file): WeddingMedia => $this->storeUpload($guestName, $file));
    }

    public function storeUpload(string $guestName, UploadedFile $file): WeddingMedia
    {
        $storedName = $this->generateStoredName($file);
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $extension = Str::lower($file->getClientOriginalExtension() ?: $file->extension() ?: pathinfo($storedName, PATHINFO_EXTENSION));
        $type = $this->detectType($mimeType);
        $diskPath = rtrim((string) config('wedding.yandex_disk.originals_path'), '/').'/'.$storedName;

        $this->ensureYandexDirectories();
        $this->yandexDisk->upload($file->getRealPath() ?: $file->path(), $diskPath);

        $thumbnailPath = $type === WeddingMedia::TYPE_IMAGE
            ? $this->createThumbnail($file, $storedName, $extension)
            : null;

        return WeddingMedia::create([
            'guest_name' => $guestName,
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $storedName,
            'disk_path' => $diskPath,
            'thumbnail_path' => $thumbnailPath,
            'mime_type' => $mimeType,
            'extension' => $extension,
            'size' => $file->getSize() ?: 0,
            'type' => $type,
            'status' => WeddingMedia::STATUS_UPLOADED,
            'uploaded_at' => Carbon::now(),
        ]);
    }

    public function hide(WeddingMedia $media): WeddingMedia
    {
        $media->update(['status' => WeddingMedia::STATUS_HIDDEN]);

        return $media->refresh();
    }

    public function restore(WeddingMedia $media): WeddingMedia
    {
        if ($media->trashed()) {
            $media->restore();
        }

        $media->update(['status' => WeddingMedia::STATUS_UPLOADED]);

        return $media->refresh();
    }

    public function delete(WeddingMedia $media): WeddingMedia
    {
        $this->yandexDisk->delete($media->disk_path);
        Storage::disk($this->thumbnailDisk)->delete(array_filter([$media->thumbnail_path]));
        $media->update(['status' => WeddingMedia::STATUS_DELETED]);
        $media->delete();

        return $media;
    }

    public function serializeForPublic(WeddingMedia $media): array
    {
        return [
            'id' => $media->id,
            'guest_name' => $media->guest_name,
            'type' => $media->type,
            'thumbnail_url' => $media->thumbnail_path ? Storage::disk($this->thumbnailDisk)->url($media->thumbnail_path) : null,
            'view_url' => route('wedding.media.show', $media),
            'download_url' => route('wedding.media.download', $media),
            'created_at' => optional($media->uploaded_at ?? $media->created_at)->toISOString(),
        ];
    }

    public function generateStoredName(UploadedFile $file): string
    {
        $extension = Str::lower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');

        return Str::uuid()->toString().'.'.$extension;
    }

    private function ensureYandexDirectories(): void
    {
        $this->yandexDisk->ensureDirectory((string) config('wedding.yandex_disk.base_path'));
        $this->yandexDisk->ensureDirectory((string) config('wedding.yandex_disk.originals_path'));
    }

    private function detectType(string $mimeType): string
    {
        return Str::startsWith($mimeType, 'video/') ? WeddingMedia::TYPE_VIDEO : WeddingMedia::TYPE_IMAGE;
    }

    private function createThumbnail(UploadedFile $file, string $storedName, string $extension): ?string
    {
        if (! extension_loaded('gd') || in_array($extension, ['heic', 'heif'], true)) {
            return null;
        }

        $sourcePath = $file->getRealPath() ?: $file->path();
        $imageSize = @getimagesize($sourcePath);

        if ($imageSize === false) {
            return null;
        }

        [$width, $height] = $imageSize;
        if ($width <= 0 || $height <= 0) {
            return null;
        }

        $source = match ($imageSize['mime'] ?? null) {
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false,
            default => false,
        };

        if (! $source) {
            return null;
        }

        $targetWidth = min(600, $width);
        $targetHeight = max(1, (int) round($height * ($targetWidth / $width)));
        $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        $thumbnailPath = $this->thumbnailDirectory.'/'.pathinfo($storedName, PATHINFO_FILENAME).'.jpg';
        Storage::disk($this->thumbnailDisk)->makeDirectory($this->thumbnailDirectory);

        if (! imagejpeg($thumbnail, Storage::disk($this->thumbnailDisk)->path($thumbnailPath), 85)) {
            imagedestroy($source);
            imagedestroy($thumbnail);

            throw new RuntimeException('Не удалось создать превью изображения.');
        }

        imagedestroy($source);
        imagedestroy($thumbnail);

        return $thumbnailPath;
    }
}
