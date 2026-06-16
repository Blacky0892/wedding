<?php

namespace App\Http\Controllers\Wedding;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wedding\StoreWeddingMediaRequest;
use App\Models\WeddingMedia;
use App\Services\WeddingMediaService;
use App\Services\YandexDiskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Throwable;

class PublicWeddingMediaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Wedding/Index');
    }

    public function list(Request $request, WeddingMediaService $service): JsonResponse
    {
        $query = WeddingMedia::query()->visible()->latestFirst();

        if ($request->string('type')->toString() === WeddingMedia::TYPE_IMAGE) {
            $query->images();
        }

        if ($request->string('type')->toString() === WeddingMedia::TYPE_VIDEO) {
            $query->videos();
        }

        $media = $query->paginate($request->integer('per_page', 24));
        $media->getCollection()->transform(fn (WeddingMedia $item): array => $service->serializeForPublic($item));

        return response()->json($media);
    }

    public function store(StoreWeddingMediaRequest $request, WeddingMediaService $service): JsonResponse|RedirectResponse
    {
        try {
            $media = $service->storeUploads(
                $request->string('guest_name')->toString(),
                $request->file('files', []),
            );
        } catch (Throwable $exception) {
            Log::error('Wedding media upload failed.', [
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
            ]);

            $message = $exception instanceof RuntimeException
                ? $exception->getMessage()
                : 'Не удалось загрузить файлы. Попробуйте позже.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 503);
            }

            return back()->with('error', $message);
        }

        if ($request->expectsJson()) {
            return response()->json(['data' => $media->map(fn (WeddingMedia $item): array => $service->serializeForPublic($item))], 201);
        }

        return back()->with('success', __('Media uploaded successfully.'));
    }

    public function show(WeddingMedia $media, YandexDiskService $yandexDisk): RedirectResponse
    {
        abort_unless($media->status === WeddingMedia::STATUS_UPLOADED, 404);

        return redirect()->away($yandexDisk->getDownloadUrl($media->disk_path));
    }

    public function download(WeddingMedia $media, YandexDiskService $yandexDisk): RedirectResponse
    {
        abort_unless($media->status === WeddingMedia::STATUS_UPLOADED, 404);

        return redirect()->away($yandexDisk->getDownloadUrl($media->disk_path));
    }
}
