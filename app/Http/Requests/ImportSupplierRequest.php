<?php

namespace App\Http\Requests;

use App\Services\SupplierImportService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportSupplierRequest extends FormRequest
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
        return [
            'file'     => ['required', 'file', 'mimes:json,txt', 'max:10240'],
            'strategy' => ['required', Rule::in(SupplierImportService::STRATEGIES)],
            'dry_run'  => ['nullable', 'boolean'],
        ];
    }
}
