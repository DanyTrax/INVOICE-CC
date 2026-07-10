<?php

namespace App\Http\Requests;

use App\Services\PermissionService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(PermissionService::class)->userHasPermission('settings_brand', 'edit');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $imageRules = ['nullable', 'file', 'max:2048', 'mimes:jpg,jpeg,png,gif,webp,svg'];

        return [
            'company_name' => ['required', 'string', 'max:255'],
            'nit' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:120'],
            'bank_account_type' => ['nullable', 'string', 'max:80'],
            'bank_account_number' => ['nullable', 'string', 'max:80'],
            'treasurer_signature_title' => ['nullable', 'string', 'max:200'],
            'invoice_email_subject' => ['nullable', 'string', 'max:255'],
            'invoice_email_body' => ['nullable', 'string', 'max:50000'],
            'logo' => $imageRules,
            'treasurer_signature' => $imageRules,
            'remove_logo' => ['nullable', 'boolean'],
            'remove_treasurer_signature' => ['nullable', 'boolean'],
        ];
    }
}
