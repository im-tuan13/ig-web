<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
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
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'images' => ['nullable', 'array', 'min:1', 'max:10'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:2200'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->hasFile('image') || $this->hasFile('images')) {
                return;
            }

            $validator->errors()->add('image', 'Please select at least one photo to share.');
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'image.image' => 'The selected file must be an image.',
            'image.mimes' => 'Use a JPG, PNG, or WEBP image.',
            'image.max' => 'The image may not be larger than 5 MB.',
            'images.array' => 'Please upload one or more images.',
            'images.max' => 'You can upload up to 10 images.',
            'images.*.image' => 'Each selected file must be an image.',
            'images.*.mimes' => 'Use a JPG, PNG, or WEBP image.',
            'images.*.max' => 'Each image may not be larger than 5 MB.',
        ];
    }
}
