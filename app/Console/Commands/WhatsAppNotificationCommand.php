<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Empresa;
use App\Models\ContaReceber;
use App\Models\ContaPagar;
use App\Models\EvolutionApiConfig;
use App\Utils\EvolutionApiUtil;
use Carbon\Carbon;

class WhatsAppNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:notify {--type=all} {--empresa_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia notificações WhatsApp para contas a pagar e receber';

    protected $evolutionApiUtil;

    public function __construct(EvolutionApiUtil $evolutionApiUtil)
    {
        parent::__construct();
        $this->evolutionApiUtil = $evolutionApiUtil;
    }

    public function handle()
    {
        $tipo = $this->option('type');
        $empresaId = $this->option('empresa_id');

        if ($empresaId) {
            $empresas = Empresa::where('id', $empresaId)->where('status', 1)->get();
        } else {
            $empresas = Empresa::where('status', 1)->get();
        }

        $this->info('Iniciando envio de notificações WhatsApp...');
        $this->info('Total de empresas: ' . $empresas->count());

        foreach ($empresas as $empresa) {
            $this->info("Processando empresa: {$empresa->razao_social}");
            
            $config = EvolutionApiConfig::where('empresa_id', $empresa->id)
                ->where('status', true)
                ->first();

            if (!$config) {
                $this->warn("Empresa {$empresa->razao_social} não possui configuração da Evolution API ativa");
                continue;
            }

            // Testa conexão
            $connectionTest = $this->evolutionApiUtil->testConnection($empresa->id);
            if (!$connectionTest) {
                $this->warn("Falha na conexão com Evolution API para empresa {$empresa->razao_social}");
                continue;
            }

            if ($tipo == 'all' || $tipo == 'receber') {
                $this->processarContasReceber($empresa);
            }

            if ($tipo == 'all' || $tipo == 'pagar') {
                $this->processarContasPagar($empresa);
            }
        }

        $this->info('Processamento concluído!');
    }

    private function processarContasReceber($empresa)
    {
        $this->info("Processando contas a receber para empresa: {$empresa->razao_social}");

        // Contas que vencem hoje
        $contasVencimento = ContaReceber::where('empresa_id', $empresa->id)
            ->where('status', 0)
            ->whereDate('data_vencimento', date('Y-m-d'))
            ->get();

        $this->info("Contas a receber vencendo hoje: {$contasVencimento->count()}");

        foreach ($contasVencimento as $conta) {
            if ($conta->cliente && $conta->cliente->telefone) {
                $result = $this->evolutionApiUtil->sendContaReceberNotification($conta, $empresa->id, 'vencimento');
                if ($result) {
                    $this->info("✓ Notificação enviada para cliente: {$conta->cliente->razao_social}");
                } else {
                    $this->error("✗ Falha ao enviar notificação para cliente: {$conta->cliente->razao_social}");
                }
            } else {
                $this->warn("Cliente sem telefone: " . ($conta->cliente->razao_social ?? 'N/A'));
            }
        }

        // Contas em atraso (2 dias ou mais)
        $contasAtraso = ContaReceber::where('empresa_id', $empresa->id)
            ->where('status', 0)
            ->where('data_vencimento', '<', date('Y-m-d'))
            ->where('data_vencimento', '>=', date('Y-m-d', strtotime('-7 days'))) // Últimos 7 dias
            ->get();

        $this->info("Contas a receber em atraso: {$contasAtraso->count()}");

        foreach ($contasAtraso as $conta) {
            if ($conta->cliente && $conta->cliente->telefone) {
                $result = $this->evolutionApiUtil->sendContaReceberNotification($conta, $empresa->id, 'atraso');
                if ($result) {
                    $this->info("✓ Notificação de atraso enviada para cliente: {$conta->cliente->razao_social}");
                } else {
                    $this->error("✗ Falha ao enviar notificação de atraso para cliente: {$conta->cliente->razao_social}");
                }
            }
        }
    }

    private function processarContasPagar($empresa)
    {
        $this->info("Processando contas a pagar para empresa: {$empresa->razao_social}");

        // Contas que vencem hoje
        $contasVencimento = ContaPagar::where('empresa_id', $empresa->id)
            ->where('status', 0)
            ->whereDate('data_vencimento', date('Y-m-d'))
            ->get();

        $this->info("Contas a pagar vencendo hoje: {$contasVencimento->count()}");

        foreach ($contasVencimento as $conta) {
            if ($conta->fornecedor && $conta->fornecedor->telefone) {
                $result = $this->evolutionApiUtil->sendContaPagarNotification($conta, $empresa->id, 'vencimento');
                if ($result) {
                    $this->info("✓ Notificação enviada para fornecedor: {$conta->fornecedor->razao_social}");
                } else {
                    $this->error("✗ Falha ao enviar notificação para fornecedor: {$conta->fornecedor->razao_social}");
                }
            } else {
                $this->warn("Fornecedor sem telefone: " . ($conta->fornecedor->razao_social ?? 'N/A'));
            }
        }

        // Contas em atraso (2 dias ou mais)
        $contasAtraso = ContaPagar::where('empresa_id', $empresa->id)
            ->where('status', 0)
            ->where('data_vencimento', '<', date('Y-m-d'))
            ->where('data_vencimento', '>=', date('Y-m-d', strtotime('-7 days'))) // Últimos 7 dias
            ->get();

        $this->info("Contas a pagar em atraso: {$contasAtraso->count()}");

        foreach ($contasAtraso as $conta) {
            if ($conta->fornecedor && $conta->fornecedor->telefone) {
                $result = $this->evolutionApiUtil->sendContaPagarNotification($conta, $empresa->id, 'atraso');
                if ($result) {
                    $this->info("✓ Notificação de atraso enviada para fornecedor: {$conta->fornecedor->razao_social}");
                } else {
                    $this->error("✗ Falha ao enviar notificação de atraso para fornecedor: {$conta->fornecedor->razao_social}");
                }
            }
        }
    }
} 