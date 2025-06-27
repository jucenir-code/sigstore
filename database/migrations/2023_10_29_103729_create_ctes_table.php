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
        Schema::create('ctes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('remetente_id')->constrained('clientes');
            $table->foreignId('destinatario_id')->constrained('clientes');
            $table->foreignId('recebedor_id')->nullable()->constrained('clientes');
            $table->foreignId('expedidor_id')->nullable()->constrained('clientes');

            $table->foreignId('veiculo_id')->nullable()->constrained('veiculos');

            $table->foreignId('natureza_id')->nullable()->constrained('natureza_operacaos');

            $table->integer('tomador');

            $table->foreignId('municipio_envio')->constrained('cidades');
            $table->foreignId('municipio_inicio')->constrained('cidades');
            $table->foreignId('municipio_fim')->constrained('cidades');

            $table->string('logradouro_tomador', 80)->nullable();
            $table->string('numero_tomador', 20)->nullable();
            $table->string('bairro_tomador', 40)->nullable();
            $table->string('cep_tomador', 10)->nullable();
            $table->foreignId('municipio_tomador')->constrained('cidades');

            $table->decimal('valor_transporte', 10, 2);
            $table->decimal('valor_receber', 10, 2);
            $table->decimal('valor_carga', 10, 2);

            $table->string('produto_predominante', 30);
            $table->date('data_prevista_entrega');

            $table->string('observacao')->nullable();
            $table->integer('sequencia_cce')->default(0);

            $table->string('chave', 44);
            $table->string('recibo', 30)->nullable();
            $table->string('numero_serie', 3);
            $table->integer('numero');
            $table->enum('estado', ['novo', 'rejeitado', 'cancelado', 'aprovado']);
            $table->string('motivo_rejeicao', 200)->nullable();

            $table->boolean('retira');
            $table->string('detalhes_retira', 100);
            $table->string('modal', 2);
            $table->integer('ambiente');

            //doc outros
            $table->string('tpDoc', 2);
            $table->string('descOutros', 100);
            $table->integer('nDoc');
            $table->decimal('vDocFisc', 10, 2);

            $table->integer('globalizado');
            $table->string('cst', 3)->default('00');
            $table->decimal('perc_icms', 5, 2)->default(0);
            $table->decimal('perc_red_bc', 5, 2)->default(0);

            $table->boolean('status_pagamento')->default(0);
            $table->string('cfop', 4)->nullable();
            $table->boolean('api')->default(0);
            $table->integer('local_id')->nullable();

            $table->timestamps();

            // alter table ctes add column status_pagamento boolean default 0;
            // alter table ctes add column api boolean default 0;
            // alter table ctes add column local_id integer default null;

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ctes');
    }
};
