<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Loja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request, string $platform, string $key)
    {
        Log::info("--- INÍCIO DA REQUISIÇÃO WEBHOOK da Plataforma [{$platform}] ---");

        // Detalhes da Requisição para Depuração
        Log::info("-> Método: " . $request->method());
        Log::info("-> Conteúdo: ", $request->all());

        // ETAPA DE VALIDAÇÃO DA ADOOREI
        // Se a requisição contiver "Adoorei Webhook", é um teste de validação.
        if ($request->has('sucess') && $request->input('sucess') === 'Adoorei Webhook') {
            Log::info("Detectada requisição de validação da Adoorei. Respondendo com sucesso.");
            return response()->json(['status' => 'validado com sucesso']); // Apenas confirma que a URL está viva.
        }

        // Se não for validação, continua para o processamento normal...
        $loja = Loja::where('webhook_key', $key)->where('ativa', true)->first();
        if (!$loja) {
            Log::warning("Chave de webhook inválida ou loja inativa: [{$key}]");
            return response()->json(['error' => 'Chave de API inválida'], 401);
        }

        if ($platform === $loja->plataforma) {
            $dadosPadronizados = null;
            if ($platform === 'adoorei') {
                $dadosPadronizados = $this->parseAdooreiPayload($request->all());
            }

            if ($dadosPadronizados) {
                $dadosPadronizados['loja_id'] = $loja->id;
                Pedido::create($dadosPadronizados);
                Log::info("SUCESSO: Pedido {$dadosPadronizados['id_pedido_origem']} da loja '{$loja->nome_loja}' foi salvo!");
            }
        }
        
        Log::info("--- FIM DA REQUISIÇÃO WEBHOOK ---");
        return response()->json(['status' => 'processado']);
    }

    private function parseAdooreiPayload(array $dados_venda): ?array
    {
        // Agora sabemos que o evento correto é 'order.created'
        if (!isset($dados_venda['event']) || $dados_venda['event'] !== 'order.created') {
            Log::info("Evento '{$dados_venda['event']}' ignorado. Aguardando 'order.created'.");
            return null;
        }

        $resource = $dados_venda['resource'];
        $customer = $resource['customer'] ?? [];
        $addr = $resource['address'] ?? [];
        $produtos = [];

        if (isset($resource['items']) && is_array($resource['items'])) {
            foreach ($resource['items'] as $item) {
                $produtos[] = [
                    'nome' => $item['name'],
                    'qtd' => (int)$item['quantity'],
                    'valor' => (float)$item['price']
                ];
            }
        }

        return [
            'id_pedido_origem' => $resource['number'],
            'plataforma_origem' => 'adoorei',
            'valor_total' => (float)$resource['value_total'], // Usando o campo de valor total que descobrimos
            'dados_comprador' => [
                'nome' => trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? '')),
                'email' => $customer['email'] ?? null,
                'whatsapp' => $customer['phone'] ?? null
            ],
            'dados_produtos' => $produtos,
        ];
    }
}
