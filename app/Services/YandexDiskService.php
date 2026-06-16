<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class YandexDiskService
{
    private function resourcesUrl(string $suffix = ''): string
    {
        return rtrim((string) config('wedding.yandex_disk.api_base_url', 'https://cloud-api.yandex.net/v1/disk'), '/').'/resources'.$suffix;
    }

    public function __construct(
        private readonly ?string $token = null,
    ) {}

    public function ensureDirectory(string $path): void
    {
        $response = $this->authorizedRequest()->send('PUT', $this->resourcesUrl(), [
            'query' => [
                'path' => $path,
            ],
        ]);

        if ($response->successful() || $response->status() === 409) {
            return;
        }

        $this->throwApiException($response, 'Не удалось создать папку на Яндекс Диске. Попробуйте позже.');
    }

    public function getUploadUrl(string $path, bool $overwrite = false): string
    {
        $response = $this->authorizedRequest()->get($this->resourcesUrl('/upload'), [
            'path' => $path,
            'overwrite' => $overwrite ? 'true' : 'false',
        ]);

        if ($response->successful()) {
            return $this->extractHref($response, 'Не удалось получить ссылку для загрузки файла. Попробуйте позже.');
        }

        $this->throwApiException($response, 'Не удалось получить ссылку для загрузки файла. Попробуйте позже.');
    }

    public function upload(string $localPath, string $diskPath): void
    {
        if (! is_file($localPath) || ! is_readable($localPath)) {
            throw new RuntimeException('Не удалось загрузить файл. Проверьте доступность локального файла.');
        }

        $uploadUrl = $this->getUploadUrl($diskPath, true);
        $stream = fopen($localPath, 'rb');

        if ($stream === false) {
            throw new RuntimeException('Не удалось загрузить файл. Проверьте доступность локального файла.');
        }

        try {
            $response = Http::withBody($stream, mime_content_type($localPath) ?: 'application/octet-stream')
                ->put($uploadUrl);
        } finally {
            fclose($stream);
        }

        if ($response->successful()) {
            return;
        }

        $this->throwApiException($response, 'Не удалось загрузить файл на Яндекс Диск. Попробуйте позже.');
    }

    public function getDownloadUrl(string $diskPath): string
    {
        $response = $this->authorizedRequest()->get($this->resourcesUrl('/download'), [
            'path' => $diskPath,
        ]);

        if ($response->successful()) {
            return $this->extractHref($response, 'Не удалось получить ссылку для скачивания файла. Попробуйте позже.');
        }

        $this->throwApiException($response, 'Не удалось получить ссылку для скачивания файла. Попробуйте позже.');
    }

    public function delete(string $diskPath): void
    {
        $response = $this->authorizedRequest()->send('DELETE', $this->resourcesUrl(), [
            'query' => [
                'path' => $diskPath,
                'permanently' => 'true',
            ],
        ]);

        if ($response->successful()) {
            return;
        }

        $this->throwApiException($response, 'Не удалось удалить файл с Яндекс Диска. Попробуйте позже.');
    }

    private function authorizedRequest(): PendingRequest
    {
        $token = $this->token ?? config('wedding.yandex_disk.token');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('Не удалось выполнить операцию с Яндекс Диском. Сервис временно недоступен.');
        }

        return Http::withHeaders([
            'Authorization' => 'OAuth '.$token,
        ])->acceptJson();
    }

    private function extractHref(Response $response, string $message): string
    {
        $href = $response->json('href');

        if (is_string($href) && $href !== '') {
            return $href;
        }

        $this->logApiError($response, 'Yandex Disk API response does not contain href.');

        throw new RuntimeException($message);
    }

    private function throwApiException(Response $response, string $message): never
    {
        $this->logApiError($response, 'Yandex Disk API request failed.');

        throw new RuntimeException($message);
    }

    private function logApiError(Response $response, string $message): void
    {
        Log::error($message, [
            'status' => $response->status(),
            'body' => $this->sanitizeLogValue($response->json() ?: $response->body()),
        ]);
    }

    /**
     * @param  mixed  $value
     * @return mixed
     */
    private function sanitizeLogValue(mixed $value): mixed
    {
        $token = $this->token ?? config('wedding.yandex_disk.token');

        if (! is_string($token) || $token === '') {
            return $value;
        }

        return is_string($value)
            ? str_replace($token, '[redacted]', $value)
            : json_decode(str_replace($token, '[redacted]', json_encode($value) ?: ''), true);
    }
}
