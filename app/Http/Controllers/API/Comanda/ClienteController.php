<?php

namespace App\Http\Controllers\API\Comanda;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;

class ClienteController extends Controller
{
    public function findCliente(Request $request){
        $mask = '(##) #####-####';
        $telefone = preg_replace('/[^0-9]/', '', $request->telefone);

        $telefone = __mask($telefone, $mask);
        $item = Cliente::where('telefone', $telefone)
        ->where('empresa_id', $request->empresa_id)->first();
        
        return response()->json($item, 200);
    }
}
