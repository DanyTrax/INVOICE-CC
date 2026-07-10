<?php

namespace App\Http\Requests;

use App\Models\Associate;
use App\Services\PermissionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConceptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(PermissionService::class)->userHasPermission('concepts', 'edit');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $categories = Associate::categoryOptions();

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
            'prices' => ['required', 'array', 'min:1'],
            'prices.*.category' => ['required', 'string', Rule::in($categories)],
            'prices.*.amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}
