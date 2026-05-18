<?php

namespace App\Http\Middleware;

use App\Traits\TraitReturnJsonOlirum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CheckModulePermission
{
    use TraitReturnJsonOlirum;

    private const VALID_DOMAINS = ['admin',  'common'];

    public function handle(Request $request, Closure $next, string $modulo): Response
    {
        try {

          // Verifica se a rota atual começa com 'admin/login' ou 'admin/*'
            // No CheckManutencao.php
            if ($request->is('*/admin/login')) {
                return $next($request);
            }


            $user = $request->user();

            if (!$user) {
                throw new AccessDeniedHttpException("Usuário não autenticado.");
            }

            // 🔥 ROOT passa direto
            if ($user->isRootAdmin()) {
                return $next($request);
            }

            if (!$user->isAtivo()) {
                // Revoga o token atual para que ele não funcione na próxima requisição
                $request->user()->currentAccessToken()->delete();

                return $this->ReturnJson(
                    null,
                    "Sua conta está {$user->status}.",
                    false,
                    403
                );
            }

            // 2. 🛑 TRAVA DE MANUTENÇÃO (Para todos os outros usuários)
            $settings = Cache::remember('sys_platform_settings', 60, function () {
                return \App\Models\SysPlatformSetting::first();
            });

            if ($settings && $settings->plataforma_em_manutencao) {
                return $this->ReturnJson(
                    null,
                    'Plataforma em manutenção.',
                    false,
                    503 
                );
            }


            // TEMPORÁRIO:
            // Enquanto a matriz fina de módulo/ação não estiver mapeada para todos os módulos
            // (ex.: parceiro.hospedagens, parceiro.par_hos_precos etc.), validamos apenas:
            // 1) domínio da rota (admin|cliente|parceiro|common)
            // 2) perfil do usuário compatível com o domínio
            $dominio = $this->resolveDomain($request, $modulo);

            if (!$dominio) {
                throw new AccessDeniedHttpException(
                    "Domínio da rota não identificado para validação de acesso."
                );
            }

            if (!$user->canAccessDomain($dominio)) {
                throw new AccessDeniedHttpException(
                    "Você não tem permissão para acessar o domínio '{$dominio}'."
                );
            }

            /*
            |--------------------------------------------------------------------------
            | VALIDAÇÃO ORIGINAL (MÓDULO + AÇÃO) - COMENTADA TEMPORARIAMENTE
            |--------------------------------------------------------------------------
            |
            | Reativar após concluir o mapeamento completo de SysModulo/SysPermissao
            | para todos os endpoints.
            |
            $metodo = $request->route()?->getActionMethod();
            if (!$metodo) {
                throw new AccessDeniedHttpException("Ação não identificada.");
            }

            $metodo = strtolower(
                preg_replace('/([a-z])([A-Z])/', '$1_$2', $metodo)
            );

            $modulo = strtolower($modulo);
            $permission = "{$modulo}.{$metodo}";

            if (!$user->can($permission)) {
                throw new AccessDeniedHttpException(
                    "Você não tem permissão para '{$permission}'."
                );
            }
            */

            return $next($request);
        } catch (AccessDeniedHttpException $e) {

            return $this->ReturnJson(
                data: null,
                message: $e->getMessage(),
                status: false,
                code: 403
            );
        } catch (\Throwable $e) {

            return $this->ReturnJson(
                data: null,
                message: "Erro interno ao validar permissões.",
                status: false,
                code: 500
            );
        }
    }

    private function resolveDomain(Request $request, string $modulo): ?string
    {
        $modulo = strtolower(trim($modulo));
        $domainFromModulo = strtolower(strtok($modulo, '.'));

        if (in_array($domainFromModulo, self::VALID_DOMAINS, true)) {
            return $domainFromModulo;
        }

        // Fallback: /api/v1/{dominio}/...
        $segments = array_map('strtolower', $request->segments());
        $domainFromPath = $segments[2] ?? null;

        if ($domainFromPath && in_array($domainFromPath, self::VALID_DOMAINS, true)) {
            return $domainFromPath;
        }

        return null;
    }
}
