<?php

namespace App\Http\Requests;

use App\Models\Associate;
use App\Services\PermissionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssociateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(PermissionService::class)->userHasPermission('associates', 'edit');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'document_id' => ['required', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'category' => ['required', 'string', Rule::in(Associate::categoryOptions())],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
