<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('lojas', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained('users'); // Futuramente, para ligar ao usuário lojista
            $table->string('nome_loja');
            $table->string('webhook_key')->unique(); // A chave secreta, única para cada loja
            $table->string('plataforma'); // ex: 'adoorei', 'yampi'
            $table->boolean('ativa')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('lojas');
    }
};
