<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'vehicle_model_id' => ['required', 'integer', 'exists:vehicle_models,id'],
            'color_id' => ['required', 'integer', 'exists:colors,id'],
            'plate' => [
                'required',
                'string',
                'max:20',
                Rule::unique('vehicles', 'plate')->ignore($this->route('vehicle')),
            ],
            'year' => ['nullable', 'integer', 'between:1900,' . (date('Y') + 1)],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }
}
