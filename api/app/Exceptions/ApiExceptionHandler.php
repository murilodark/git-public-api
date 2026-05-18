<?php

namespace App\Exceptions;

/**
 * --------------------------------------------------------------------------
 * API Exception Handler - Arquitetura Olirum
 * --------------------------------------------------------------------------
 * 
 * Esta classe centraliza o tratamento de exceções da API, interceptando erros
 * comuns e convertendo-os em respostas JSON padronizadas via Trait.
 * 
 * Benefícios:
 * - Respostas consistentes (status, success, message, data).
 * - Tratamento inteligente de erros de banco (Duplicidade).
 * - Isolamento de erros Web vs API.
 * 
 * @author Murilo Dark
 * @date 2024-06-27 (Criado) | Atualizado em 2026-02-06
 */

use App\Traits\TraitReturnJsonOlirum;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Exception;

class ApiExceptionHandler
{
    use TraitReturnJsonOlirum;

    public function __invoke(Exceptions $exceptions)
    {
        // 1. Forçar resposta JSON sempre que o prefixo for 'api/*'
        $exceptions->shouldRenderJsonWhen(fn(Request $request) => $request->is('api/*'));

        // 2. Renderização customizada de exceções
        $exceptions->render(function (Exception $e, Request $request) {
            if (!$request->is('api/*')) {
                return null; // Deixa o Laravel tratar rotas Web (Blade) normalmente
            }

            return match (true) {

                /**
                 * Erros de Validação (422)
                 * Captura falhas em $request->validate() ou FormRequests.
                 */
                $e instanceof ValidationException =>
                $this->returnJson(collect($e->errors())->flatten()->all(),  'Erro de validação dos campos.', false, 422),

                /**
                 * Violação de Unicidade (409 Conflict)
                 * Atua como rede de segurança caso o dado chegue duplicado ao banco (CPF, Email) e não seja tratado
                 * pela validação do FormRequest ou regras de negócio capturadas pela 
                 * $e instanceof ValidationException
                 */
                $e instanceof UniqueConstraintViolationException =>
                $this->handleUniqueViolation($e),

                /**
                 * Recursos Não Encontrados (404)
                 * Captura rotas inexistentes ou falhas em Model::findOrFail().
                 */
                $e instanceof ModelNotFoundException, $e instanceof NotFoundHttpException =>
                $this->returnJson(null, 'Registro ou rota não encontrada.', false, 404),

                /**
                 * Falha de Autenticação (401)
                 * Erro disparado pelo middleware auth:sanctum.
                 */
                $e instanceof AuthenticationException =>
                $this->returnJson(null, 'Usuário não autenticado.', false, 401),

                /**
                 * Método Não Permitido (405)
                 * Ex: Tentar dar um POST em uma rota que só aceita GET.
                 */
                $e instanceof MethodNotAllowedHttpException =>
                $this->returnJson(null, 'Método HTTP não permitido para esta rota.', false, 405),

                /**
                 * Outras Exceções HTTP (4xx e 5xx)
                 */
                $e instanceof HttpExceptionInterface =>
                $this->returnJson(null, $e->getMessage(), false, $e->getStatusCode()),

                /**
                 * Erros de Negócio / Validação de Dados (400 Bad Request)
                 * Captura Exceptions lançadas por regras de negócio, como indisponibilidade, preços inválidos, etc.
                 * Identifica pela mensagem começar com palavras-chave específicas.
                 */
                $this->isBusinessValidationError($e) =>
                $this->returnJson(null, $e->getMessage(), false, 400),

                /**
                 * Erro Genérico / Fallback (500)
                 * 
                 * Em DEBUG: Retorna a mensagem real e o arquivo/linha para facilitar o fix.
                 * Em PRODUÇÃO: Oculta detalhes técnicos, registrando o erro no Log para análise.
                 */
                default => $this->handleUnexpectedError($e),
            };
        });
    }

    /**
     * Extrai o nome do campo duplicado da mensagem do SQL (MySQL/MariaDB)
     * e retorna uma mensagem amigável para o usuário.
     */
    private function handleUniqueViolation(UniqueConstraintViolationException $e)
    {
        $message = $e->getMessage();
        $column = 'REGISTRO';

        if (preg_match("/for key '.*?\.(.*?)_unique'/", $message, $matches)) {
            $column = strtoupper($matches[1]);
        }

        $friendlyMessage = "O dado informado ({$column}) já está em uso por outro usuário.";

        return $this->returnJson(
            [$friendlyMessage], // Colocado dentro de um array para padronizar o 'data'
            'Erro na validação de campos.',
            false,
            409
        );
    }

    /**
     * Trata erros inesperados (500) garantindo logs e privacidade de dados.
     */
    private function handleUnexpectedError(Exception $e)
    {
        // Registra o erro detalhado nos logs do Laravel (/storage/logs/laravel.log)
        \Illuminate\Support\Facades\Log::error("Erro Crítico API: " . $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'url' => request()->fullUrl(),
        ]);

        $debugData = [
            'error' => $e->getMessage(),
            'file' => "{$e->getFile()} (Linha: {$e->getLine()})",
            'trace' => collect($e->getTrace())->take(5) // Pega apenas os 5 primeiros passos do erro
        ];

        return $this->returnJson(
            config('app.debug') ? $debugData : [], // Em produção retorna array vazio no data
            'Ocorreu um erro interno inesperado. Por favor, tente novamente mais tarde.',
            false,
            500
        );
    }

    /**
     * Verifica se a Exception é um erro de validação de negócio (não erro interno).
     * Identifica por mensagens que começam com palavras-chave específicas.
     */
    private function isBusinessValidationError(Exception $e): bool
    {
        $message = $e->getMessage();
        $businessKeywords = [
            'Indisponível',
            'Hospedagem não encontrada',
            'Unidade não encontrada',
            'Unidade não pertence',
            'Nenhuma regra de preço',
            'Período inválido',
        ];

        foreach ($businessKeywords as $keyword) {
            if (str_starts_with($message, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
