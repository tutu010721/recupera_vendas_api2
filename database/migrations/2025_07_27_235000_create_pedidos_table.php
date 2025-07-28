<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('id_pedido_origem');
            $table->string('status')->default('PENDENTE');
            $table->string('plataforma_origem');
            $table->decimal('valor_total', 10, 2);
            $table->json('dados_comprador');
            $table->json('dados_produtos');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('pedidos');
    }
};
