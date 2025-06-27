<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigGeral extends Model
{
    use HasFactory;

    protected $fillable = [ 
        'empresa_id', 'balanca_digito_verificador', 'balanca_valor_peso', 'confirmar_itens_prevenda', 'notificacoes',
        'margem_combo', 'gerenciar_estoque', 'percentual_lucro_produto', 'tipos_pagamento_pdv', 'senha_manipula_valor',
        'abrir_modal_cartao', 'percentual_desconto_orcamento', 'agrupar_itens', 'tipo_comissao', 'modelo', 'alerta_sonoro',
        'cabecalho_pdv', 'regime_nfse', 'mercadopago_public_key_pix', 'mercadopago_access_token_pix'
    ];

    public static function getNotificacoes(){
        return [
            'Contas a pagar', 'Contas a receber', 'Alerta de estoque', 'Alerta de validade', 'Ticket'
        ];
    }

    public static function tributacoesNfse(){
        return [
            2 => 'Estimativa', 
            3 => 'Sociedade de profissionais', 
            4 => 'Cooperativa', 
            5 => 'Microempresário individual (MEI)', 
            6 => 'Microempresário e empresa de pequeno porte (MEEPP)', 
        ];
    }
    
}
