<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'real_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'username' => '用户名',
            'real_name' => '真实姓名',
            'email' => '邮箱地址',
            'phone' => '手机号码',
        ];
    }
} 