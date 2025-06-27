<?php

namespace App\Http\Controllers\API\PDV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\ListaPreco;
use App\Models\CategoriaProduto;

class ProdutoController extends Controller
{
    public function produtos(Request $request){
        $updated_at = $request->updated_at;
        $data = Produto::where('empresa_id', $request->empresa_id)
        ->select('id', 'nome', 'valor_unitario', 'categoria_id', 'codigo_barras', 'imagem', 'gerenciar_estoque')
        ->with(['categoria', 'estoque'])
        ->where('status', 1)
        ->get();
        return response()->json($data, 200);
    }

    public function categorias(Request $request){
        $data = CategoriaProduto::where('empresa_id', $request->empresa_id)
        ->get();
        return response()->json($data, 200);
    }

    public function listaPreco(Request $request){
        $data = ListaPreco::where('empresa_id', $request->empresa_id)
        ->with('itens')
        ->get();
        return response()->json($data, 200);
    }
}
