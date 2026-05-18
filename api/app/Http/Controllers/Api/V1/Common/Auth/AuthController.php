<?php

namespace App\Http\Controllers\Api\V1\Common\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Common\Auth\LoginRequest;
use App\Http\Resources\Api\V1\Common\Profile\ProfileResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Kreait\Laravel\Firebase\Facades\Firebase;

class AuthController extends Controller
{
    /**
     * Decide qual tipo de login será usado
     */
    public function login(LoginRequest $request)
    {
        
        return $this->loginEmailSenha($request);
    }

    /**
     * 🔐 LOGIN CLÁSSICO (EMAIL + SENHA)
     */
    public function loginEmailSenha(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        $key = 'login:' . strtolower($credentials['email']) . '|' . $request->ip();

        // =============================
        // RATE LIMIT
        // =============================
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return $this->ReturnJson(
                ['retry_after' => $seconds],
                "Muitas tentativas. Aguarde {$seconds}s.",
                false,
                429
            );
        }

        // =============================
        // AUTENTICAÇÃO
        // =============================
        if (!Auth::attempt($credentials)) {
            RateLimiter::hit($key, 60);

            return $this->ReturnJson(
                null,
                'Credenciais inválidas.',
                false,
                401
            );
        }

        /** @var User $user */
        $user = Auth::user();

        // 🔥 IMPORTANTE (agora com grupos)
        $user->load('pessoa.perfis.grupos');

        // =============================
        // STATUS
        // =============================
        if (!$user->isAtivo()) {
            Auth::logout();

            return $this->ReturnJson(
                null,
                "Sua conta está {$user->status}.",
                false,
                403
            );
        }

        RateLimiter::clear($key);

        // =============================
        // TOKEN
        // =============================
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->ReturnJson([
            'user'  => new ProfileResource($user->pessoa),
            'token' => $token
        ], 'Login realizado com sucesso.');
    }

    /**
     * 👤 USUÁRIO LOGADO
     */
    public function me(Request $request)
    {
        $user = $request->user()->load('pessoa.perfis.grupos');

        return $this->ReturnJson(
            new ProfileResource($user->pessoa),
            'Dados do usuário autenticado.'
        );
    }

    /**
     * 🚪 LOGOUT
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->ReturnJson(null, 'Logout realizado com sucesso.');
    }
}