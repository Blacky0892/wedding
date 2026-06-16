<?php

namespace App\Http\Requests\Wedding;

use Illuminate\Foundation\Http\FormRequest;

class StoreWeddingMediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) config('wedding.uploads_enabled');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $allowedExtensions = (array) config('wedding.allowed_extensions');
        $allowedMimeTypes = (array) config('wedding.allowed_mime_types');

        $maxFilesPerRequest = (int) config('wedding.max_files_per_request', 10);
        $maxFileSizeKilobytes = (int) config('wedding.max_file_size_mb', 300) * 1024;

        return [
            'guest_name' => ['required', 'string', 'max:255'],

            'files' => [
                'required',
                'array',
                'min:1',
                'max:' . $maxFilesPerRequest,
            ],

            'files.*' => [
                'required',
                'file',
                'mimetypes:' . implode(',', $allowedMimeTypes),
                'extensions:' . implode(',', $allowedExtensions),
                'max:' . $maxFileSizeKilobytes,
            ],
        ];
    }
}
