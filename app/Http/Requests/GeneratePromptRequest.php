<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GeneratePromptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image'=>'required|file|image|mimes:jpeg,png,svg,gif|max:10240|dimensions:min_width=100,min_height=100,max_width=10000,max_height=10000',

        ];
    }

    public function messages()
    {
        return [
        
        'image.required' => 'Please upload an image.',
        'image.file' => 'The uploaded file must be a valid file.',
        'image.image' => 'The uploaded file must be an image.',
        'image.mimes' => 'The image must be a file of type: jpeg, png, svg, gif.',
        'image.max' => 'The image size must not exceed 10MB.',
        'image.dimensions' => 'The image dimensions must be at least 100x100 and at most 10000x10000 pixels.'
        ];
    }
}
