<?php

namespace App\Http\Controllers;

use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\FuncionarioOs;
use App\Models\OrdemServico;
use App\Models\Produto;
use App\Models\ProdutoOs;
use App\Models\RelatorioOs;
use App\Models\ServicoOs;
use App\Models\Servico;
use App\Models\Funcionario;
use App\Models\NaturezaOperacao;
use App\Models\Nfe;
use App\Models\Convenio;
use App\Models\Transportadora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Psy\CodeCleaner\ReturnTypePass;
use Dompdf\Dompdf;

class OrdemServicoController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:ordem_servico_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:ordem_servico_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:ordem_servico_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:ordem_servico_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $cliente_id = $request->get('cliente_id');
        $start_date = $request->get('start_date');
        $codigo = $request->get('codigo');

        $data = OrdemServico::where('empresa_id', request()->empresa_id)
        ->when(!empty($cliente_id), function ($query) use ($cliente_id) {
            return $query->where('cliente_id', $cliente_id);
        })
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', $start_date);
        })
        ->when(!empty($codigo), function ($query) use ($codigo) {
            return $query->where('codigo_sequencial', $codigo);
        })
        ->orderBy('id', 'desc')
        ->paginate(env("PAGINACAO"));

        return view('ordem_servico.index', compact('data'));
    }

    public function create()
    {

        if (!__isCaixaAberto()) {
            session()->flash("flash_warning", "Abrir caixa antes de continuar!");
            return redirect()->route('caixa.create');
        }
        $hoje = date('d/m/Y');
        $funcionario = Funcionario::where('empresa_id', request()->empresa_id)->first();
        $clientes = Cliente::where('empresa_id', request()->empresa_id)->first();
        $usuario = Auth::user();

        $servicos = Servico::where('empresa_id', request()->empresa_id)->first();

        if ($funcionario == null) {
            session()->flash('flash_warning', 'Cadastrar um funcionario antes de continuar!');
            return redirect()->route('funcionarios.create');
        }
        if ($clientes == null) {
            session()->flash('flash_warning', 'Cadastrar um cliente antes de continuar!');
            return redirect()->route('clientes.create');
        }
        // dd($clientes);
        if ($servicos == null) {
            session()->flash('flash_warning', 'Cadastrar um serviço antes de continuar!');
            return redirect()->route('servicos.create');
        }

        $convenios = Convenio::where('empresa_id', request()->empresa_id)
        ->where('status', 1)->get();
        return view('ordem_servico.create', compact('hoje', 'funcionario', 'usuario', 'servicos', 'convenios'));
    }

    public function edit($id)
    {
        $funcionario = Funcionario::where('empresa_id', request()->empresa_id)->first();
        $clientes = Cliente::where('empresa_id', request()->empresa_id)->first();
        $usuario = Auth::user();

        $servicos = Servico::where('empresa_id', request()->empresa_id)->first();

        $item = OrdemServico::findOrFail($id);
        __validaObjetoEmpresa($item);
        
        return view('ordem_servico.edit', compact('funcionario', 'usuario', 'servicos', 'item'));
    }

    public function store(Request $request)
    {

        $this->_validate($request);
        try {

            $lastItem = OrdemServico::where('empresa_id', $request->empresa_id)
            ->orderBy('codigo_sequencial', 'desc')->first();
            $codigo_sequencial = 1;
            if($lastItem != null){
                $codigo_sequencial = $lastItem->codigo_sequencial+1;
            }
            $ordem = OrdemServico::create([
                'descricao' => $request->descricao ?? '',
                'usuario_id' => get_id_user(),
                'cliente_id' => $request->cliente_id,
                'empresa_id' => $request->empresa_id,
                'codigo_sequencial' => $codigo_sequencial,
                'data_inicio' => $request->data_inicio,
                'data_entrega' => $request->data_entrega,
                'funcionario_id' => $request->funcionario_id
            ]);
            __createLog($request->empresa_id, 'Ordem de Serviço', 'cadastrar', "#$codigo_sequencial - Cliente " . $ordem->cliente->info);
            session()->flash("flash_success", "Ordem de Serviço criada com sucesso");
            return redirect()->route('ordem-servico.show', $ordem->id);
        } catch (\Exception $e) {
            __createLog($request->empresa_id, 'Ordem de Serviço', 'erro', $e->getMessage());
            session()->flash("flash_error", "Algo deu Errado" . $e->getMessage());
            return redirect()->back();
        }
    }

    private function _validate(Request $request){

        $rules = [
            'data_inicio' => 'required',
            'data_entrega' => 'required',
        ];

        $messages = [
            'data_inicio.required' => 'Campo obrigatório',
            'data_entrega.required' => 'Campo obrigatório',
        ];

        $this->validate($request, $rules, $messages);
    }

    public function update(Request $request, $id)
    {
        $item = OrdemServico::findOrFail($id);
        try {
            $request->merge([
                'descricao' => $request->input('descricao'),
                'usuario_id' => get_id_user(),
                'cliente_id' => $request->cliente_id,
                'empresa_id' => $request->empresa_id,
                'data_inicio' => $request->data_inicio,
                'data_entrega' => $request->data_entrega,
                'funcionario_id' => $request->funcionario_id
            ]);

            $item->fill($request->all())->save();
            __createLog($request->empresa_id, 'Ordem de Serviço', 'editar', "#$item->codigo_sequencial - Cliente " . $item->cliente->info);
            session()->flash("flash_success", "Ordem de Serviço alterada com sucesso");
        } catch (\Exception $e) {
            __createLog($request->empresa_id, 'Ordem de Serviço', 'erro', $e->getMessage());
            session()->flash("flash_error", "Algo deu Errado" . $e->getMessage());
        }
        return redirect()->route('ordem-servico.show', $item->id);
    }

    public function show($id)
    {
        if (!__isCaixaAberto()) {
            session()->flash("flash_warning", "Abrir caixa antes de continuar!");
            return redirect()->route('caixa.create');
        }
        $ordem = OrdemServico::findOrFail($id);
        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();
        $servicos = Servico::where('empresa_id', request()->empresa_id)->get();
        // $relatorio = RelatorioOs::all();
        return view('ordem_servico.show', compact('funcionarios', 'ordem', 'servicos'));
    }

    public function storeServico(Request $request)
    {
        $id = $request->ordem_servico_id;
        $ordem = OrdemServico::findOrFail($id);
        $valor = $ordem->valor + (__convert_value_bd($request->valor) * __convert_value_bd($request->quantidade));
        $ordem->valor = $valor;
        $ordem->save();
        try {
            $servico = ServicoOs::create([
                'servico_id' => $request->servico_id,
                'ordem_servico_id' => $ordem->id,
                'quantidade' => __convert_value_bd($request->quantidade),
                'valor' => __convert_value_bd($request->valor),
                'status' => $request->status,
                'subtotal' => __convert_value_bd($request->quantidade) * __convert_value_bd($request->valor)
            ]);

            $descricaoLog = "#$ordem->codigo_sequencial - Serviço Adicionado: " . $servico->servico->nome;

            __createLog(request()->empresa_id, 'Ordem de Serviço - Serviço', 'cadastrar', $descricaoLog);
            session()->flash("flash_success", "Serviço adicionado!");
        } catch (\Exception $e) {
            __createLog($request->empresa_id, 'Ordem de Serviço - Serviço', 'erro', $e->getMessage());
            session()->flash("flash_error", "Algo deu errado" . $e->getMessage());
        }
        return redirect()->back();
    }

    public function storeProduto(Request $request)
    {
        $id = $request->ordem_servico_id;
        $ordem = OrdemServico::findOrFail($id);
        $valor = $ordem->valor + (__convert_value_bd($request->valor_produto) * __convert_value_bd($request->quantidade_produto));
        $ordem->valor = $valor;
        $ordem->save();
        try {
            $produto = ProdutoOs::create([
                'produto_id' => $request->produto_id,
                'ordem_servico_id' => $ordem->id,
                'quantidade' => __convert_value_bd($request->quantidade_produto),
                'valor' => __convert_value_bd($request->valor_produto),
                'subtotal' => __convert_value_bd($request->quantidade_produto) * __convert_value_bd($request->valor_produto)
            ]);
            $descricaoLog = "#$ordem->codigo_sequencial - Serviço Adicionado: " . $produto->produto->nome;

            __createLog(request()->empresa_id, 'Ordem de Serviço - Produto', 'cadastrar', $descricaoLog);
            session()->flash("flash_success", "Produto adicionado!");
        } catch (\Exception $e) {
            __createLog($request->empresa_id, 'Ordem de Serviço - Produto', 'erro', $e->getMessage());
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->back();
    }

    public function deletarProduto($id)
    {
        $produtoOs = ProdutoOs::where('id', $id)->first();
        $ordem = OrdemServico::where('id', $produtoOs->ordem_servico_id)->first();
        $valor = $ordem->valor - $produtoOs->subtotal;
        $ordem->valor = $valor;
        $ordem->save();
        try {
            $produtoOs->delete();
            $descricaoLog = "#$ordem->codigo_sequencial - Serviço Removido: " . $produtoOs->produto->nome;
            __createLog(request()->empresa_id, 'Ordem de Serviço - Produto', 'excluir', $descricaoLog);

            session()->flash("flash_success", "Produto removido");
        } catch (\Exception $e) {
            __createLog(request()->empresa_id, 'Ordem de Serviço - Produto', 'erro', $e->getMessage());
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->back();
    }

    public function deletarServico($id)
    {
        $produtoOs = ServicoOs::where('id', $id)->first();
        $ordem = OrdemServico::where('id', $produtoOs->ordem_servico_id)->first();
        $valor = $ordem->valor - $produtoOs->subtotal;
        $ordem->valor = $valor;
        $ordem->save();
        try {
            $produtoOs->delete();
            $descricaoLog = "#$ordem->codigo_sequencial - Serviço Removido: " . $produtoOs->servico->nome;
            __createLog(request()->empresa_id, 'Ordem de Serviço - Serviço', 'excluir', $descricaoLog);

            session()->flash("flash_success", "Serviço removido");
        } catch (\Exception $e) {
            __createLog(request()->empresa_id, 'Ordem de Serviço - Serviço', 'erro', $e->getMessage());
            session()->flash("flash_error", "Algo deu errado" . $e->getMessage());
        }
        return redirect()->back();
    }

    public function alterarStatusServico($id)
    {
        $servicoOs = ServicoOs::where('id', $id)->first();
        try {
            $servicoOs->status = !$servicoOs->status;
            $servicoOs->save();
            session()->flash("flash_success", "Status Alterado");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado" . $e->getMessage());
        }
        return redirect()->back();
    }

    public function storeFuncionario(Request $request)
    {
        $id = $request->ordem_servico_id;
        $ordem = OrdemServico::findOrFail($id);
        // $this->_validateFuncionario($request);
        try {
            FuncionarioOs::create([
                'usuario_id' => get_id_user(),
                'funcionario_id' => $request->funcionario_id,
                'ordem_servico_id' => $request->ordem_servico_id,
                'funcao' => $request->funcao
            ]);
            session()->flash("flash_success", "Funcionario Adicionado a Ordem de Serviço");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu Errado: " . $e->getMessage());
        }
        return redirect()->back();
    }

    public function addRelatorio($id)
    {
        $ordem = OrdemServico::where('id', $id)->first();
        return view('ordem_servico.add_relatorio', compact('ordem'));
    }

    public function storeRelatorio(Request $request)
    {
        // dd($request->ordem_servico_id);
        try {
            RelatorioOs::create([
                'usuario_id' => get_id_user(),
                'texto' => $request->texto,
                'ordem_servico_id' => $request->ordem_servico_id
            ]);
            session()->flash("flash_success", "Relatório Adicionado");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('ordem-servico.show', $request->ordem_servico_id);
    }

    public function alterarEstado($id)
    {
        $ordem = OrdemServico::where('id', $id)->first();
        return view('ordem_servico.alterar_estado', compact('ordem'));
    }

    public function updateEstado(Request $request, $id)
    {
        $ordem = OrdemServico::findOrFail($id);
        try {
            $ordem->estado = $request->novo_estado;
            if($ordem->estado == 'fz' && $request->faturar){
                $caixa = __isCaixaAberto();
                if($caixa == null){
                    session()->flash("flash_error", "Abra o caixa!");
                    return redirect()->back();
                }
                $ordem->caixa_id = $caixa->id;
            }

            $ordem->save();
            session()->flash("flash_success", "Estado alterado!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('ordem-servico.show', [$ordem->id]);
    }

    public function imprimir($id)
    {
        $ordem = OrdemServico::findOrFail($id);

        __validaObjetoEmpresa($ordem);
        $config = Empresa::where('id', request()->empresa_id)->first();
        if ($config == null) {
            session()->flash("flash_warning", "Configure o emitente");
            return redirect()->route('config.index');
        }

        $p = view('ordem_servico.imprimir', compact('config', 'ordem'));

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $pdf = ob_get_clean();
        $domPdf->setPaper("A4");
        $domPdf->render();
        $domPdf->stream("Ordem de Serviço.pdf", array("Attachment" => false));
        // return view('ordem_servico.print', compact('ordem', 'config'));
    }

    public function editRelatorio($id)
    {
        $item = RelatorioOs::findOrFail($id);

        $ordem = OrdemServico::where('id', $item->ordem_servico_id)->first();

        return view('ordem_servico.edit_relatorio', compact('item', 'ordem'));
    }

    public function updateRelatorio(Request $request, $id)
    {
        $ordem = RelatorioOs::findOrFail($id);
        $item = OrdemServico::findOrFail($request->ordem_servico_id);
        try {
            $ordem->texto = $request->texto;
            $ordem->save();
            session()->flash("flash_success", "Reletório Alterado");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado" . $e->getMessage());
        }
        return redirect()->route('ordem-servico.show', $item);
    }

    public function deleteRelatorio(Request $request, $id)
    {
        $relatorioOs = RelatorioOs::where('id', $id)->first();
        try {
            $relatorioOs->delete();
            session()->flash("flash_success", "Relatório Deletado");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->back();
    }

    public function destroy($id)
    {
        $item = OrdemServico::findOrFail($id);
        try {
            $descricaoLog = "#$item->codigo_sequencial - Cliente " . $item->cliente->info;
            $item->servicos()->delete();
            $item->relatorios()->delete();
            $item->itens()->delete();

            $item->delete();
            __createLog(request()->empresa_id, 'Ordem de Serviço', 'excluir', $descricaoLog);
            session()->flash("flash_success", "Ordem deletada");
        } catch (\Exception $e) {
            __createLog(request()->empresa_id, 'Ordem de Serviço', 'erro', $e->getMessage());
            session()->flash("flash_error", "Algo deu errado" . $e->getMessage());
        }
        return redirect()->back();
    }   

    public function gerarNfe($id)
    {
        $item = OrdemServico::findOrFail($id);
        $cidades = Cidade::all();
        $transportadoras = Transportadora::where('empresa_id', request()->empresa_id)->get();

        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)->get();
        if (sizeof($naturezas) == 0) {
            session()->flash("flash_warning", "Primeiro cadastre um natureza de operação!");
            return redirect()->route('natureza-operacao.create');
        } 
        // $produtos = Produto::where('empresa_id', request()->empresa_id)->get();
        $empresa = Empresa::findOrFail(request()->empresa_id);
        $numeroNfe = Nfe::lastNumero($empresa);

        $isOrdemServico = 1;
        return view('nfe.create', compact('item', 'cidades', 'transportadoras', 'naturezas', 'isOrdemServico', 'numeroNfe'));
    }

    public function destroySelecet(Request $request)
    {
        $removidos = 0;
        for($i=0; $i<sizeof($request->item_delete); $i++){
            $item = OrdemServico::findOrFail($request->item_delete[$i]);
            try {
                $descricaoLog = "#$item->codigo_sequencial - Cliente " . $item->cliente->info;
                $item->servicos()->delete();
                $item->relatorios()->delete();
                $item->itens()->delete();
                $item->delete();
                $removidos++;
                __createLog(request()->empresa_id, 'Ordem de Serviço', 'excluir', $descricaoLog);
            } catch (\Exception $e) {
                __createLog(request()->empresa_id, 'Ordem de Serviço', 'erro', $e->getMessage());
                session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
                return redirect()->back();
            }
        }

        session()->flash("flash_success", "Total de itens removidos: $removidos!");
        return redirect()->back();
    }
}
