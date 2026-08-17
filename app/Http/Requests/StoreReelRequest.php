<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'video' => ['required', 'file', 'mimetypes:video/mp4,video/quicktime,video/webm,video/x-msvideo', 'max:51200'],
            'caption' => ['nullable', 'string', 'max:2200'],
        ];
    }
}
