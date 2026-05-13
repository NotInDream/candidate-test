<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLayerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $layer = $this->route('layer');

        return [
            'layer_order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('clt_layers', 'layer_order')
                    ->where('layup_id', $layer?->layup_id)
                    ->ignore($layer),
            ],
            'thickness' => ['required', 'numeric', 'min:0'],
            'width' => ['required', 'numeric', 'min:0'],
            'angle' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}
