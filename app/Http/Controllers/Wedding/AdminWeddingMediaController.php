<?php

namespace App\Http\Controllers\Wedding;

use App\Http\Controllers\Controller;
use App\Models\WeddingMedia;
use App\Services\WeddingMediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class AdminWeddingMediaController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        $query = WeddingMedia::latestFirst();

        if ($request->string('type')->toString() === WeddingMedia::TYPE_IMAGE) {
            $query->images();
        }

        if ($request->string('type')->toString() === WeddingMedia::TYPE_VIDEO) {
            $query->videos();
        }

        if ($request->expectsJson()) {
            return response()->json($query->paginate($request->integer('per_page', 24)));
        }

        return Inertia::render('Admin/Wedding/MediaIndex', [
            'media' => $query->paginate($request->integer('per_page', 24)),
        ]);
    }

    public function hide(WeddingMedia $media, WeddingMediaService $service): JsonResponse|RedirectResponse
    {
        $media = $service->hide($media);

        return $this->response($media, __('Media hidden successfully.'));
    }

    public function restore(int $media, WeddingMediaService $service): JsonResponse|RedirectResponse
    {
        $media = WeddingMedia::withTrashed()->findOrFail($media);
        $media = $service->restore($media);

        return $this->response($media, __('Media restored successfully.'));
    }

    public function destroy(WeddingMedia $media, WeddingMediaService $service): JsonResponse|RedirectResponse
    {
        try {
            $service->delete($media);
        } catch (Throwable $exception) {
            report($exception);

            if (request()->expectsJson()) {
                return response()->json([
                    'message' => __('Не удалось удалить файл с Яндекс Диска. Файл не был помечен удалённым.'),
                ], 500);
            }

            return back()->with('error', __('Не удалось удалить файл с Яндекс Диска. Файл не был помечен удалённым.'));
        }

        if (request()->expectsJson()) {
            return response()->json(status: 204);
        }

        return back()->with('success', __('Media deleted successfully.'));
    }

    private function response(WeddingMedia $media, string $message): JsonResponse|RedirectResponse
    {
        if (request()->expectsJson()) {
            return response()->json(['data' => $media]);
        }

        return back()->with('success', $message);
    }
}
