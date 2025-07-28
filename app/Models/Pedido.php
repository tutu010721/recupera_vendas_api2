<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model {
    use HasFactory;
    protected $fillable = ['id_pedido_origem', 'status', 'plataforma_origem', 'valor_total', 'dados_comprador', 'dados_produtos'];
    protected $casts = ['dados_comprador' => 'array', 'dados_produtos' => 'array'];
}
