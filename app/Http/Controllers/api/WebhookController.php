<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller {
    public function handle(Request $request, string $platform, string $key) {
        Log::info("Webhook recebido de [{$platform}] com a chave [{$key}]");
        if ($platform === 'adoorei') {
            $dadosPadronizados = $this->parseAdooreiPayload($request->all());
            if ($dadosPadronizados) {
                Pedido::create($dadosPadronizados);
                Log::info("SUCESSO: Pedido {$dadosPadronizados['id_pedido_origem']} da Adoorei salvo!");
            }
        }
        return response()->json(['status' => 'recebido']);
    }

    private function parseAdooreiPayload(array $dados_venda): ?array {
         if (!isset($dados_venda['event']) || !in_array($dados_venda['event'], ['order.created', 'order.pending'])) {
            return null;
        }
        $resource = $dados_venda['resource']; $customer = $resource['customer'] ?? [];
        $produtos = []; $valorTotal = (float)($resource['total'] ?? 0);
        if (isset($resource['items']) && is_array($resource['items'])) {
            foreach ($resource['items'] as $item) { $produtos[] = ['nome' => $item['name'], 'qtd' => (int)$item['quantity'], 'valor' => (float)$item['price']]; }
        }
        return [
            'id_pedido_origem' => $resource['number'] ?? uniqid(), 'plataforma_origem' => 'adoorei', 'valor_total' => $valorTotal,
            'dados_comprador' => ['nome' => trim(($customer['first_name'] ?? '').' '.($customer['last_name'] ?? '')), 'email' => $customer['email'] ?? null, 'whatsapp' => $customer['phone'] ?? null],
            'dados_produtos' => $produtos,
        ];
    }
}
