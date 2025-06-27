<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estoque;
use App\Utils\EstoqueUtil;
use App\Models\ProdutoLocalizacao;
use App\Models\Localizacao;

class EstoqueController extends Controller
{

    protected $util;

    public function __construct(EstoqueUtil $util)
    {
        $this->util = $util;
        $this->middleware('permission:estoque_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:estoque_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:estoque_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:estoque_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request){

        $data = Estoque::select('estoques.*')
        ->join('produtos', 'produtos.id', '=', 'estoques.produto_id')
        ->where('produtos.empresa_id', request()->empresa_id)
        ->when(!empty($request->produto), function ($q) use ($request) {
            return $q->where('produtos.nome', 'LIKE', "%$request->produto%");
        })
        ->orderBy('id', 'desc')
        ->groupBy('estoques.produto_id')
        ->paginate(env("PAGINACAO"));

        return view('estoque.index', compact('data'));
    }

    public function create()
    {
        return view('estoque.create');
    }

    public function edit(Request $request, $id)
    {
        $local_id = $request->local_id;
        $item = Estoque::findOrFail($id);
        $locais = Estoque::where('produto_id', $item->produto_id)->get();

        $firstLocation = Localizacao::where('empresa_id', $item->produto->empresa_id)->first();
        
        return view('estoque.edit', compact('item', 'locais', 'firstLocation'));
    }

    public function destroy($id)
    {
        $item = Estoque::findOrFail($id);
        $descricaoLog = $item->produto->nome;

        try {
            $item->delete();
            session()->flash("flash_success", "estoque removido com sucesso!");
            __createLog(request()->empresa_id, 'Estoque', 'excluir', $descricaoLog);
        } catch (\Exception $e) {
            __createLog(request()->empresa_id, 'Estoque', 'erro', $e->getMessage());
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
        }
        return redirect()->route('estoque.index');
    }

    public function store(Request $request)
    {
        try {
            if(isset($request->local_id)){
                ProdutoLocalizacao::updateOrCreate([
                    'produto_id' => $request->produto_id, 
                    'localizacao_id' => $request->local_id
                ]);
            }

            $this->util->incrementaEstoque($request->produto_id, $request->quantidade, $request->produto_variacao_id, $request->local_id);

            $transacao = Estoque::where('produto_id', $request->produto_id)->first();
            $tipo = 'incremento';
            $codigo_transacao = $transacao->id;
            $tipo_transacao = 'alteracao_estoque';

            $this->util->movimentacaoProduto($request->produto_id, $request->quantidade, $tipo, $codigo_transacao, $tipo_transacao, \Auth::user()->id, $request->produto_variacao_id);

            __createLog($request->empresa_id, 'Estoque', 'cadastrar', $transacao->produto->nome . " - quantidade " . $request->quantidade);
            session()->flash("flash_success", "Estoque adicionado com sucesso!");
        } catch (\Exception $e) {
            // echo $e->getLine();
            // die;
            __createLog($request->empresa_id, 'Estoque', 'erro', $e->getMessage());
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('estoque.index');
    }

    public function update(Request $request, $id){

        $item = Estoque::findOrFail($id);

        try{
            if(isset($request->local_id)){

                for($i=0; $i<sizeof($request->local_id); $i++){

                    $item = Estoque::findOrFail($request->local_id[$i]);

                    $diferenca = 0;
                    $tipo = 'incremento';

                    if($item->quantidade > $request->quantidade[$i]){
                        $diferenca = $item->quantidade - $request->quantidade[$i];
                        $tipo = 'reducao';
                    }else{
                        $diferenca = $request->quantidade[$i] - $item->quantidade;
                    }
                    $item->quantidade = $request->quantidade[$i];
                    $item->save();

                    $codigo_transacao = $item->id;
                    $tipo_transacao = 'alteracao_estoque';

                    $this->util->movimentacaoProduto($item->produto_id, $diferenca, $tipo, $codigo_transacao, $tipo_transacao, \Auth::user()->id);

                    if(isset($request->novo_estoque)){

                        $firstLocation = Localizacao::where('empresa_id', $item->produto->empresa_id)->first();
                        ProdutoLocalizacao::updateOrCreate([
                            'produto_id' => $item->produto_id, 
                            'localizacao_id' => $firstLocation->id
                        ]);
                    }

                }
            }else{

                $diferenca = 0;
                $tipo = 'incremento';

                if($item->quantidade > $request->quantidade){
                    $diferenca = $item->quantidade - $request->quantidade;
                    $tipo = 'reducao';
                }else{
                    $diferenca = $request->quantidade - $item->quantidade;
                }
                $item->quantidade = $request->quantidade;
                $item->save();

                $codigo_transacao = $item->id;
                $tipo_transacao = 'alteracao_estoque';

                $this->util->movimentacaoProduto($item->produto_id, $diferenca, $tipo, $codigo_transacao, $tipo_transacao, \Auth::user()->id);
            }
            __createLog($request->empresa_id, 'Estoque', 'editar', $item->produto->nome . " - quantidade " . $request->quantidade);
            session()->flash("flash_success", "Estoque alterado com sucesso!");
        }catch (\Exception $e) {
            // echo $e->getLine();
            // die;
            __createLog($request->empresa_id, 'Estoque', 'erro', $e->getMessage());
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('estoque.index');
    }
}
