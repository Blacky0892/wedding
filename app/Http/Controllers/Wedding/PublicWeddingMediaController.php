<?php

namespace App\Http\Controllers\Wedding;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wedding\StoreWeddingMediaRequest;
use App\Models\WeddingMedia;
use App\Services\WeddingMediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicWeddingMediaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Wedding/Media/Index');
    }

    public function list(Request $request): JsonResponse
    {
        $query = WeddingMedia::query()->visible()->latestFirst();

        if ($request->string('type')->toString() === WeddingMedia::TYPE_IMAGE) {
            $query->images();
        }

        if ($request->string('type')->toString() === WeddingMedia::TYPE_VIDEO) {
            $query->videos();
        }

        return response()->json($query->paginate($request->integer('per_page', 24)));
    }

    public function store(StoreWeddingMediaRequest $request, WeddingMediaService $service): JsonResponse|RedirectResponse
    {
        $media = $service->storeUploads(
            $request->string('guest_name')->toString(),
            $request->file('files', []),
        );

        if ($request->expectsJson()) {
            return response()->json(['data' => $media], 201);
        }

        return back()->with('success', __('Media uploaded successfully.'));
    }

    public function show(WeddingMedia $media): JsonResponse
    {
        abort_unless($media->status === WeddingMedia::STATUS_VISIBLE, 404);

        return response()->json(['data' => $media]);
    }

    public function download(WeddingMedia $media): StreamedResponse
    {
        abort_unless($media->status === WeddingMedia::STATUS_VISIBLE, 404);
        abort_unless(Storage::disk('public')->exists($media->disk_path), 404);

        return Storage::disk('public')->download($media->disk_path, $media->original_name);
    }
}
