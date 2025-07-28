<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loja extends Model
{
    use HasFactory;

    /**
     * O nome da tabela associada com o model.
     *
     * @var string
     */
    protected $table = 'lojas';

    /**
     * Os atributos que podem ser preenchidos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome_loja',
        'webhook_key',
        'plataforma',
        'ativa',
    ];
}
