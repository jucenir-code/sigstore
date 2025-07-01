@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Despesas</div>
            <div class="card-body">
                <!-- Gráfico ou tabela de despesas -->
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Documentos fiscais emitidos</div>
            <div class="card-body">
                Nenhum dado disponível
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Gráfico financeiro</div>
            <div class="card-body">
                <!-- Gráfico financeiro aqui -->
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Receitas</div>
            <div class="card-body">
                <!-- Gráfico ou tabela de receitas -->
            </div>
        </div>
    </div>
</div>
@endsection 