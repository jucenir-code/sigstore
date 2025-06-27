<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pagamento;
use App\Models\PlanoEmpresa;
use App\Models\FinanceiroPlano;
use App\Models\ConfiguracaoSuper;

class PaymentController extends Controller
{
    public function status($id){

        $item = Pagamento::where('transacao_id', $id)->first();

        $config = ConfiguracaoSuper::first();
        \MercadoPago\SDK::setAccessToken($config->mercadopago_access_token);

        $payStatus = \MercadoPago\Payment::find_by_id($id);
        // $payStatus->status = "approved";
        if($payStatus->status != $item->status){
            $this->setarLicenca($item);

            $item->status = $payStatus->status;
            $item->save();
        }
        return response()->json($item->status, 200);
    }

    private function setarLicenca($pagamento){
        $plano = $pagamento->plano;
        $empresa = $pagamento->empresa;
        $exp = date('Y-m-d', strtotime("+$plano->intervalo_dias days",strtotime( 
          date('Y-m-d'))));

        $planoEmpresa = PlanoEmpresa::create([
            'empresa_id' => $empresa->id,
            'plano_id' => $plano->id,
            'data_expiracao' => $exp,
            'valor' => $plano->valor,
            'forma_pagamento' => 'pix'
        ]);

        FinanceiroPlano::create([
            'empresa_id' => $empresa->id,
            'plano_id' => $plano->id,
            'valor' => $plano->valor,
            'tipo_pagamento' => 'PIX',
            'status_pagamento' => 'recebido',
            'plano_empresa_id' => $planoEmpresa->id
        ]);
    }
}
