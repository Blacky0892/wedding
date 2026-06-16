<?php

namespace App\Http\Requests\Wedding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $maxFileSizeKilobytes = (int) config('wedding.max_file_size_mb') * 1024;

        return [
            'guest_name' => ['required', 'string', 'max:255'],
            'files' => ['required', 'array', 'min:1', 'max:'.config('wedding.max_files_per_request')],
            'files.*' => [
                'required',
                'file',
                Rule::mimetypes($allowedMimeTypes),
                'extensions:'.implode(',', $allowedExtensions),
                'max:'.$maxFileSizeKilobytes,
            ],
        ];
    }
}
