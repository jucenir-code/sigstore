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
        Schema::create('fornecedors', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->nullable()->constrained('empresas');

            $table->string('razao_social', 60);
            $table->string('nome_fantasia', 60);
            $table->string('cpf_cnpj', 20);
            $table->string('ie', 20)->nullable();

            $table->boolean('contribuinte')->default(0);
            $table->boolean('consumidor_final')->default(0);
            $table->string('email', 60)->nullable();
            $table->string('telefone', 20)->nullable();

            $table->foreignId('cidade_id')->nullable()->constrained('cidades');

            $table->string('rua', 60);
            $table->string('cep', 9);
            $table->string('numero', 10);
            $table->string('bairro', 40);
            $table->string('complemento', 60)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fornecedors');
    }
};
