<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Traits\TraitReturnJsonOlirum;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: RateLimitLogin
 * 
 * Proteção contra ataques de Força Bruta (Brute Force) no endpoint de login.
 * Bloqueia excesso de tentativas baseando-se na combinação de E-mail e IP.
 * 
 * @author Murilo Dark
 * @date 2024-06-27
 */
class RateLimitLogin
{
    use TraitReturnJsonOlirum;

    // Configurações de limite
    protected int $maxAttempts = 5;      // Máximo de tentativas
    protected int $decaySeconds = 60;    // Tempo de bloqueio (em segundos)

    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveKey($request);

        /**
         * 1. VERIFICAÇÃO DE EXCESSO
         * Se o limite de tentativas for atingido, interrompe a requisição.
         */
        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return $this->ReturnJson(
                data: [
                    'retry_after_seconds' => $seconds,
                    'max_attempts' => $this->maxAttempts
                ],
                message: "Muitas tentativas de login detectadas. Por segurança, tente novamente em {$seconds} segundos.",
                status: false,
                code: 429 // Too Many Requests
            );
        }

        /**
         * 2. REGISTRO DE TENTATIVA
         * Incrementa o contador de tentativas para esta chave.
         */
        RateLimiter::hit($key, $this->decaySeconds);

        $response = $next($request);

        /**
         * 3. ADICIONAR INFORMAÇÕES NO HEADER (Opcional)
         * Informa ao Frontend quantas tentativas ainda restam.
         */
        $response->headers->add([
            'X-RateLimit-Limit' => $this->maxAttempts,
            'X-RateLimit-Remaining' => RateLimiter::remaining($key, $this->maxAttempts),
        ]);

        return $response;
    }

    /**
     * Gera uma chave única de identificação para o limite.
     * Combina o e-mail (em minúsculo) e o IP do dispositivo.
     */
    protected function resolveKey(Request $request): string
    {
        // Garante que o e-mail esteja padronizado para evitar burlar o limit com Maiúsculas
        $email = Str::lower($request->input('email', 'anonymous'));
        
        // Retorna a chave formatada para o driver de cache (Redis/Database/File)
        return 'login_throttle:' . $email . '|' . $request->ip();
    }
}
