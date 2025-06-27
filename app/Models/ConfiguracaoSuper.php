<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracaoSuper extends Model
{
    use HasFactory;

    protected $fillable = [
        'cpf_cnpj', 'name', 'email', 'telefone', 'mercadopago_public_key',
        'mercadopago_access_token', 'sms_key', 'token_whatsapp',
        'usuario_correios', 'codigo_acesso_correios', 'cartao_postagem_correios', 'token_auth_nfse',
        'timeout_nfe', 'timeout_nfce', 'timeout_cte', 'timeout_mdfe', 'usar_resp_tecnico',
        'token_api', 'token_integra_notas'
    ];

}
