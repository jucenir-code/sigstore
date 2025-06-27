<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('config_gerals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->enum('balanca_valor_peso', ['peso', 'valor']);
            $table->integer('balanca_digito_verificador')->nullable();
            $table->boolean('confirmar_itens_prevenda')->default(0);
            $table->boolean('gerenciar_estoque')->default(0);
            $table->boolean('agrupar_itens')->default(0);
            $table->text('notificacoes');
            $table->decimal('margem_combo', 5,2)->default(50);
            $table->decimal('percentual_desconto_orcamento', 5,2)->nullable();
            $table->decimal('percentual_lucro_produto', 10,2)->default(0);

            $table->text('tipos_pagamento_pdv');
            $table->string('senha_manipula_valor', 20)->nullable();
            $table->boolean('abrir_modal_cartao')->default(1);
            $table->enum('tipo_comissao', ['percentual_vendedor', 'percentual_margem'])->nullable();
            $table->string('modelo', 20)->default('light');
            $table->boolean('alerta_sonoro')->default(1);
            $table->boolean('cabecalho_pdv')->default(1);
            $table->integer('regime_nfse')->nullable();

            $table->string('mercadopago_public_key_pix', 120)->nullable();
            $table->string('mercadopago_access_token_pix', 120)->nullable();

            // alter table config_gerals add column confirmar_itens_prevenda boolean default 0;
            // alter table config_gerals modify column balanca_digito_verificador integer default null;
            // alter table config_gerals add column notificacoes text;
            // alter table config_gerals add column margem_combo decimal(5,2) default 50;
            // alter table config_gerals add column gerenciar_estoque boolean default 0;
            // alter table config_gerals add column percentual_lucro_produto decimal(10,2) default 0;
            // alter table config_gerals add column tipos_pagamento_pdv text;
            // alter table config_gerals add column senha_manipula_valor varchar(20) default null;
            // alter table config_gerals add column abrir_modal_cartao boolean default 1;

            // alter table config_gerals add column percentual_desconto_orcamento decimal(5,2) default null;
            // alter table config_gerals add column agrupar_itens boolean default 0;
            // alter table config_gerals add column alerta_sonoro boolean default 0;
            // alter table config_gerals add column tipo_comissao enum('percentual_vendedor', 'percentual_margem') default 'percentual_vendedor';

            // alter table config_gerals add column modelo varchar(20) default 'light';
            // alter table config_gerals add column cabecalho_pdv boolean default 1;
            // alter table config_gerals add column regime_nfse integer default null;

            // alter table config_gerals add column mercadopago_public_key_pix varchar(120) default null;
            // alter table config_gerals add column mercadopago_access_token_pix varchar(120) default null;


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_gerals');
    }
};
