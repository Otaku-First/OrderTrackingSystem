<?php

namespace App\Http\Controllers\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;


class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|string|email|max:255|exists:users,email',
            'password' => 'required|string|min:6',
        ];
    }

}
