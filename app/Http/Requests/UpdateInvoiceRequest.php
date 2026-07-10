<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use App\Services\PermissionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(PermissionService::class)->userHasPermission('invoices', 'edit');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'associate_id' => ['required', 'integer', 'exists:associates,id'],
            'concept_id' => ['required', 'integer', 'exists:concepts,id'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'status' => ['required', 'string', Rule::in(array_keys(Invoice::STATUSES))],
        ];
    }
}
