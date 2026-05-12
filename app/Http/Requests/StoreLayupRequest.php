<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLayupRequest extends FormRequest
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
        $supplierId = $this->route('supplier')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('clt_layups', 'name')->where('supplier_id', $supplierId),
            ],
        ];
    }
}
