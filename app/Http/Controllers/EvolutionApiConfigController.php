<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EvolutionApiConfig;
use App\Utils\EvolutionApiUtil;

class EvolutionApiConfigController extends Controller
{
    protected $evolutionApiUtil;

    public function __construct(EvolutionApiUtil $evolutionApiUtil)
    {
        $this->evolutionApiUtil = $evolutionApiUtil;
    }

    public function index()
    {
        $configs = EvolutionApiConfig::where('empresa_id', request()->empresa_id)->get();
        return view('evolution-api-config.index', compact('configs'));
    }

    public function create()
    {
        return view('evolution-api-config.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'api_url' => 'required|url',
            'api_key' => 'required|string',
            'instance_name' => 'required|string',
            'webhook_url' => 'nullable|url'
        ]);

        try {
            $data = $request->all();
            $data['empresa_id'] = $request->empresa_id;
            $data['status'] = $request->has('status');

            $config = EvolutionApiConfig::create($data);

            // Testa a conexão
            $testResult = $this->evolutionApiUtil->testConnection($request->empresa_id);
            
            if ($testResult) {
                session()->flash("flash_success", "Configuração salva com sucesso! Conexão testada e aprovada.");
            } else {
                session()->flash("flash_warning", "Configuração salva, mas falha no teste de conexão. Verifique os dados.");
            }

            __createLog($request->empresa_id, 'Evolution API Config', 'cadastrar', 'Configuração criada');
            
        } catch (\Exception $e) {
            __createLog($request->empresa_id, 'Evolution API Config', 'erro', $e->getMessage());
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }

        return redirect()->route('evolution-api-config.index');
    }

    public function edit($id)
    {
        $config = EvolutionApiConfig::findOrFail($id);
        return view('evolution-api-config.edit', compact('config'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'api_url' => 'required|url',
            'api_key' => 'required|string',
            'instance_name' => 'required|string',
            'webhook_url' => 'nullable|url'
        ]);

        try {
            $config = EvolutionApiConfig::findOrFail($id);
            
            $data = $request->all();
            $data['status'] = $request->has('status');

            $config->update($data);

            // Testa a conexão
            $testResult = $this->evolutionApiUtil->testConnection($request->empresa_id);
            
            if ($testResult) {
                session()->flash("flash_success", "Configuração atualizada com sucesso! Conexão testada e aprovada.");
            } else {
                session()->flash("flash_warning", "Configuração atualizada, mas falha no teste de conexão. Verifique os dados.");
            }

            __createLog($request->empresa_id, 'Evolution API Config', 'editar', 'Configuração atualizada');
            
        } catch (\Exception $e) {
            __createLog($request->empresa_id, 'Evolution API Config', 'erro', $e->getMessage());
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }

        return redirect()->route('evolution-api-config.index');
    }

    public function destroy($id)
    {
        try {
            $config = EvolutionApiConfig::findOrFail($id);
            $config->delete();

            session()->flash("flash_success", "Configuração removida com sucesso!");
            __createLog(request()->empresa_id, 'Evolution API Config', 'excluir', 'Configuração removida');
            
        } catch (\Exception $e) {
            __createLog(request()->empresa_id, 'Evolution API Config', 'erro', $e->getMessage());
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }

        return redirect()->route('evolution-api-config.index');
    }

    public function testConnection($id)
    {
        try {
            $config = EvolutionApiConfig::findOrFail($id);
            $result = $this->evolutionApiUtil->testConnection($config->empresa_id);
            
            if ($result) {
                return response()->json(['success' => true, 'message' => 'Conexão estabelecida com sucesso!']);
            } else {
                return response()->json(['success' => false, 'message' => 'Falha na conexão. Verifique os dados.']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
        }
    }

    public function sendTestMessage($id)
    {
        try {
            $config = EvolutionApiConfig::findOrFail($id);
            
            // Envia mensagem de teste para um número específico (você pode ajustar)
            $testNumber = '5511999999999'; // Número de teste
            $testMessage = "Teste de integração com Evolution API - " . date('d/m/Y H:i:s');
            
            $result = $this->evolutionApiUtil->sendMessage($testNumber, $testMessage, $config->empresa_id);
            
            if ($result) {
                return response()->json(['success' => true, 'message' => 'Mensagem de teste enviada com sucesso!']);
            } else {
                return response()->json(['success' => false, 'message' => 'Falha ao enviar mensagem de teste.']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
        }
    }
} 