<?php

namespace App\Utils;

use Illuminate\Support\Str;
use App\Models\EvolutionApiConfig;
use App\Models\ContaReceber;
use App\Models\ContaPagar;
use App\Models\Cliente;
use App\Models\Fornecedor;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class EvolutionApiUtil
{
    protected $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 30,
            'verify' => false
        ]);
    }

    public function getConfig($empresa_id)
    {
        $config = EvolutionApiConfig::where('empresa_id', $empresa_id)
            ->where('status', true)
            ->first();
        
        if ($config == null) return null;

        return $config;
    }

    public function sendMessage($numero, $mensagem, $empresa_id, $file = null)
    {
        $config = $this->getConfig($empresa_id);
        if (!$config) {
            return false;
        }

        $numero = $this->formatPhoneNumber($numero);
        
        $data = [
            'number' => $numero,
            'text' => $mensagem
        ];

        if ($file) {
            $data['mediaMessage'] = [
                'mediaType' => 'image',
                'media' => $file
            ];
        }

        try {
            $response = $this->httpClient->post($config->api_url . '/message/sendText/' . $config->instance_name, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'apikey' => $config->api_key
                ],
                'json' => $data
            ]);

            $result = json_decode($response->getBody(), true);
            return $result;
        } catch (RequestException $e) {
            \Log::error('Evolution API Error: ' . $e->getMessage());
            return false;
        }
    }

    public function sendTemplateMessage($numero, $template, $empresa_id, $variables = [])
    {
        $config = $this->getConfig($empresa_id);
        if (!$config) {
            return false;
        }

        $numero = $this->formatPhoneNumber($numero);
        
        $data = [
            'number' => $numero,
            'template' => $template,
            'variables' => $variables
        ];

        try {
            $response = $this->httpClient->post($config->api_url . '/message/sendTemplate/' . $config->instance_name, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'apikey' => $config->api_key
                ],
                'json' => $data
            ]);

            $result = json_decode($response->getBody(), true);
            return $result;
        } catch (RequestException $e) {
            \Log::error('Evolution API Template Error: ' . $e->getMessage());
            return false;
        }
    }

    public function sendContaReceberNotification($conta, $empresa_id, $tipo = 'vencimento')
    {
        $config = $this->getConfig($empresa_id);
        if (!$config) {
            return false;
        }

        $cliente = $conta->cliente;
        if (!$cliente || !$cliente->telefone) {
            return false;
        }

        $numero = $this->formatPhoneNumber($cliente->telefone);
        $valor = __moeda($conta->valor_integral);
        $dataVencimento = __data_pt($conta->data_vencimento, 0);
        $diasAtraso = $this->calcularDiasAtraso($conta->data_vencimento);

        if ($tipo == 'vencimento') {
            $mensagem = $this->getMensagemContaReceberVencimento($cliente, $conta, $valor, $dataVencimento);
        } else {
            $mensagem = $this->getMensagemContaReceberAtraso($cliente, $conta, $valor, $dataVencimento, $diasAtraso);
        }

        return $this->sendMessage($numero, $mensagem, $empresa_id);
    }

    public function sendContaPagarNotification($conta, $empresa_id, $tipo = 'vencimento')
    {
        $config = $this->getConfig($empresa_id);
        if (!$config) {
            return false;
        }

        $fornecedor = $conta->fornecedor;
        if (!$fornecedor || !$fornecedor->telefone) {
            return false;
        }

        $numero = $this->formatPhoneNumber($fornecedor->telefone);
        $valor = __moeda($conta->valor_integral);
        $dataVencimento = __data_pt($conta->data_vencimento, 0);
        $diasAtraso = $this->calcularDiasAtraso($conta->data_vencimento);

        if ($tipo == 'vencimento') {
            $mensagem = $this->getMensagemContaPagarVencimento($fornecedor, $conta, $valor, $dataVencimento);
        } else {
            $mensagem = $this->getMensagemContaPagarAtraso($fornecedor, $conta, $valor, $dataVencimento, $diasAtraso);
        }

        return $this->sendMessage($numero, $mensagem, $empresa_id);
    }

    private function formatPhoneNumber($numero)
    {
        // Remove todos os caracteres não numéricos
        $numero = preg_replace('/[^0-9]/', '', $numero);
        
        // Adiciona código do país se não existir
        if (strlen($numero) == 11 && substr($numero, 0, 2) == '11') {
            $numero = '55' . $numero;
        } elseif (strlen($numero) == 10) {
            $numero = '55' . $numero;
        }
        
        return $numero;
    }

    private function calcularDiasAtraso($dataVencimento)
    {
        $hoje = date('Y-m-d');
        $diferenca = strtotime($hoje) - strtotime($dataVencimento);
        return floor($diferenca / (60 * 60 * 24));
    }

    private function getMensagemContaReceberVencimento($cliente, $conta, $valor, $dataVencimento)
    {
        return "Olá {$cliente->razao_social}! 

Informamos que você possui uma conta a receber vencendo hoje:

📋 Descrição: {$conta->descricao}
💰 Valor: R$ {$valor}
📅 Vencimento: {$dataVencimento}

Agradecemos sua atenção!

*Mensagem automática do sistema*";
    }

    private function getMensagemContaReceberAtraso($cliente, $conta, $valor, $dataVencimento, $diasAtraso)
    {
        return "Olá {$cliente->razao_social}! 

⚠️ ATENÇÃO: Você possui uma conta a receber em atraso:

📋 Descrição: {$conta->descricao}
💰 Valor: R$ {$valor}
📅 Vencimento: {$dataVencimento}
⏰ Dias em atraso: {$diasAtraso} dia(s)

Por favor, entre em contato conosco para regularizar esta situação.

*Mensagem automática do sistema*";
    }

    private function getMensagemContaPagarVencimento($fornecedor, $conta, $valor, $dataVencimento)
    {
        return "Olá {$fornecedor->razao_social}! 

Informamos que temos uma conta a pagar vencendo hoje:

📋 Descrição: {$conta->descricao}
💰 Valor: R$ {$valor}
📅 Vencimento: {$dataVencimento}

Estamos processando o pagamento.

*Mensagem automática do sistema*";
    }

    private function getMensagemContaPagarAtraso($fornecedor, $conta, $valor, $dataVencimento, $diasAtraso)
    {
        return "Olá {$fornecedor->razao_social}! 

⚠️ ATENÇÃO: Temos uma conta a pagar em atraso:

📋 Descrição: {$conta->descricao}
💰 Valor: R$ {$valor}
📅 Vencimento: {$dataVencimento}
⏰ Dias em atraso: {$diasAtraso} dia(s)

Pedimos desculpas pelo atraso. Estamos trabalhando para regularizar esta situação o mais breve possível.

*Mensagem automática do sistema*";
    }

    public function testConnection($empresa_id)
    {
        $config = $this->getConfig($empresa_id);
        if (!$config) {
            return false;
        }

        try {
            $response = $this->httpClient->get($config->api_url . '/instance/connectionState/' . $config->instance_name, [
                'headers' => [
                    'apikey' => $config->api_key
                ]
            ]);

            $result = json_decode($response->getBody(), true);
            return $result;
        } catch (RequestException $e) {
            return false;
        }
    }
} 