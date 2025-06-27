<?php

namespace App\Http\Controllers\API\PDV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;

class ClienteController extends Controller
{
    public function all(Request $request){
        $data = Cliente::where('empresa_id', $request->empresa_id)
        ->select('id', 'razao_social', 'cpf_cnpj', 'rua', 'numero', 'bairro', 'complemento', 'status', 'ie', 'cidade_id', 'cep')
        ->with('cidade')
        ->get();
        return response()->json($data, 200);
    }
}
