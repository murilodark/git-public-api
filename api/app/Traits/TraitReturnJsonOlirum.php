<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Trait: TraitReturnJsonOlirum
 * 
 * Descrição: Padroniza a estrutura de respostas JSON em toda a API. 
 * Garante que o frontend receba sempre o mesmo formato de objeto, facilite o tratamento 
 * de erros e assegure a integridade dos códigos HTTP e caracteres Unicode.
 * 
 * @author Murilo Dark
 * @date 2024-06-27 (Criado)
 */
trait TraitReturnJsonOlirum
{
    /**
     * Gera uma resposta JSON padronizada para a plataforma.
     *
     * @param mixed $data Conteúdo da resposta (arrays, objetos ou null).
     * @param string $message Mensagem descritiva (sucesso ou erro).
     * @param bool $status Indicador booleano de sucesso da operação.
     * @param int $code Código de status HTTP (ex: 200, 403, 422).
     * @return JsonResponse
     */
    public function ReturnJson($data = null, $message = '', $status = true, $code = 200): JsonResponse
    {
        /**
         * VALIDAÇÃO DE SEGURANÇA:
         * Garante que o código HTTP esteja dentro da faixa oficial permitida (100-599).
         * Caso contrário, força o status 200 para evitar quebras no protocolo de resposta.
         */
        if ($code < 100 || $code > 599) {
            $code = 200;
        }

        /**
         * NORMALIZAÇÃO:
         * Garante que o status seja sempre um valor estritamente booleano (true/false).
         */
        $status = (bool) $status;

        // Estrutura base da resposta
    $response = [
        'message' => $message,
        'status'  => $status,
        'code'    => $code
    ];

       /**
     * Lógica de Normalização:
     * Se $data for um array e já possuir a chave 'data' (comum em Resources paginados),
     * nós mesclamos tudo no primeiro nível. Caso contrário, colocamos dentro de 'data'.
     */
    if (is_array($data) && isset($data['data'])) {
        $response = array_merge($response, $data);
    } else {
        $response['data'] = $data;
    }

    return response()->json($response, $code, [], JSON_UNESCAPED_UNICODE);
    }
}
