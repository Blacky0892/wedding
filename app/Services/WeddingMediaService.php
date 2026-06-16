<?php

namespace App\Services;

use App\Models\WeddingMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WeddingMediaService
{
    public function __construct(
        private readonly string $disk = 'public',
        private readonly string $directory = 'wedding-media',
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
        $diskPath = $file->storeAs($this->directory, $storedName, $this->disk);
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $type = $this->detectType($mimeType);
        $thumbnailPath = $type === WeddingMedia::TYPE_IMAGE ? $this->createThumbnail($diskPath) : null;

        return WeddingMedia::create([
            'guest_name' => $guestName,
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $storedName,
            'disk_path' => $diskPath,
            'thumbnail_path' => $thumbnailPath,
            'mime_type' => $mimeType,
            'extension' => $file->getClientOriginalExtension() ?: $file->extension(),
            'size' => $file->getSize() ?: 0,
            'type' => $type,
            'status' => WeddingMedia::STATUS_VISIBLE,
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

        $media->update(['status' => WeddingMedia::STATUS_VISIBLE]);

        return $media->refresh();
    }

    public function delete(WeddingMedia $media): bool
    {
        return (bool) $media->delete();
    }

    public function deleteFiles(WeddingMedia $media): void
    {
        Storage::disk($this->disk)->delete(array_filter([
            $media->disk_path,
            $media->thumbnail_path,
        ]));
    }

    public function generateStoredName(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->extension();

        return Str::uuid()->toString().($extension ? '.'.Str::lower($extension) : '');
    }

    private function detectType(string $mimeType): string
    {
        return Str::startsWith($mimeType, 'video/') ? WeddingMedia::TYPE_VIDEO : WeddingMedia::TYPE_IMAGE;
    }

    private function createThumbnail(string $diskPath): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $sourcePath = Storage::disk($this->disk)->path($diskPath);
        $imageSize = @getimagesize($sourcePath);

        if ($imageSize === false) {
            return null;
        }

        [$width, $height] = $imageSize;
        $source = match ($imageSize['mime'] ?? null) {
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false,
            'image/gif' => @imagecreatefromgif($sourcePath),
            default => false,
        };

        if (! $source) {
            return null;
        }

        $targetWidth = 480;
        $targetHeight = max(1, (int) round($height * ($targetWidth / $width)));
        $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        $thumbnailName = pathinfo($diskPath, PATHINFO_FILENAME).'.jpg';
        $thumbnailPath = $this->directory.'/thumbnails/'.$thumbnailName;
        Storage::disk($this->disk)->makeDirectory($this->directory.'/thumbnails');
        imagejpeg($thumbnail, Storage::disk($this->disk)->path($thumbnailPath), 85);

        imagedestroy($source);
        imagedestroy($thumbnail);

        return $thumbnailPath;
    }
}
