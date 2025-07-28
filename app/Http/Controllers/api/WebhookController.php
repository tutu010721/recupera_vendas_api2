<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Loja; // <-- IMPORTAMOS O MODELO DA LOJA
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request, string $platform, string $key)
    {
        Log::info("Webhook recebido da plataforma [{$platform}] com a chave [{$key}]");

        // PASSO DE VALIDAÇÃO: Encontrar a loja pela chave secreta e verificar se está ativa
        $loja = Loja::where('webhook_key', $key)->where('ativa', true)->first();

        // Se a loja não for encontrada, retorna um erro e para a execução
        if (!$loja) {
            Log::warning("Chave de webhook inválida ou loja inativa: [{$key}]");
            return response()->json(['error' => 'Chave de API inválida ou não autorizada'], 401); // 401 Unauthorized
        }

        // Se a loja for encontrada, continuamos o processo...
        if ($platform === $loja->plataforma) { // Checa se o webhook é da plataforma correta da loja
            $dadosPadronizados = null;
            if ($platform === 'adoorei') {
                $dadosPadronizados = $this->parseAdooreiPayload($request->all());
            }
            // Futuramente: else if ($platform === 'yampi') { ... }

            if ($dadosPadronizados) {
                // Adiciona o ID da loja encontrada aos dados do pedido antes de salvar
                $dadosPadronizados['loja_id'] = $loja->id;
                
                Pedido::create($dadosPadronizados);
                Log::info("SUCESSO: Pedido {$dadosPadronizados['id_pedido_origem']} da loja '{$loja->nome_loja}' (ID: {$loja->id}) foi salvo!");
            }
        } else {
            Log::warning("Webhook recebido da plataforma [{$platform}] mas a loja [{$loja->nome_loja}] está configurada para [{$loja->plataforma}]");
        }

        return response()->json(['status' => 'recebido']);
    }

    private function parseAdooreiPayload(array $dados_venda): ?array
    {
        if (!isset($dados_venda['event']) || !in_array($dados_venda['event'], ['order.created', 'order.pending'])) {
            return null;
        }

        $resource = $dados_venda['resource'];
        $customer = $resource['customer'] ?? [];
        $produtos = [];
        $valorTotal = (float)($resource['total'] ?? 0);

        if (isset($resource['items']) && is_array($resource['items'])) {
            foreach ($resource['items'] as $item) {
                $produtos[] = ['nome' => $item['name'], 'qtd' => (int)$item['quantity'], 'valor' => (float)$item['price']];
            }
        }

        return [
            'id_pedido_origem' => $resource['number'] ?? uniqid(),
            'plataforma_origem' => 'adoorei',
            'valor_total' => $valorTotal,
            'dados_comprador' => ['nome' => trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? '')), 'email' => $customer['email'] ?? null, 'whatsapp' => $customer['phone'] ?? null],
            'dados_produtos' => $produtos,
        ];
    }
}
