<?php

namespace App\Http\Requests\Api\V1\Common\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Se houver um token do firebase, não exigimos email/senha
        // if ($this->has('firebase_token')) {
        //     return [
        //         'firebase_token' => ['required', 'string'],
        //     ];
        // }

        // Caso contrário, segue o fluxo padrão de email e senha
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'O e-mail é obrigatório para login tradicional.',
            'email.email'    => 'Informe um e-mail válido.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter no mínimo :min caracteres.',
            'firebase_token.required' => 'O token de autenticação é necessário.',
        ];
    }
}
