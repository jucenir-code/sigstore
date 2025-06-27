<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TefMultiPlusCard;

class TefController extends Controller
{
    public function teste(Request $request){
        // $str = '000-000 = CRT¬001-000 = 1¬002-000 = 1¬003-000 = 010¬004-000 = 0¬009-000 = 0¬010-000 = PAGSEGURO¬010-001 = 08561701000101¬010-002 = 999¬011-000 = 20¬012-000 = 038002¬013-000 = VY39PW¬015-000 = 1309204008¬016-000 = 1309204008¬017-000 = 0¬018-000 = 01¬022-000 = 13092024¬023-000 = 204003¬027-000 = 09330208011204008¬028-000 = 27¬029-001 = "VENDA EM AMBIENTE DE HOMOLOGACAO"¬029-002 = "******NÃO USAR EM CLIENTES******"¬029-003 = ""¬029-004 = "               PAGBANK                "¬029-005 = "                 VISA                 "¬029-006 = " "¬029-007 = "CARTAO: 417402XXXXXX0653"¬029-008 = "1A VIA CLIENTE  DATA:13/09/24 20:40:06"¬029-009 = "AUTO: VY39PW"¬029-010 = "CV: 623500000"¬029-011 = " "¬029-012 = "COMPRA DEBITO"¬029-013 = "VALOR TOTAL: R0,10"¬029-014 = " "¬029-015 = "AID: A0000000032010"¬029-016 = "ARQC: 7B7F8F85EAD4BFD1"¬029-017 = "LABEL: VISA DEBITO"¬029-018 = "--------------------------------------"¬029-019 = "MULTIPLUS PAY"¬029-020 = "RUA DOZE DE OUTUBRO 623"¬029-021 = "PRESIDENTE PR - SP"¬029-022 = "CNPJ: 45.314.249/0001-00"¬029-023 = "CONTROLE 09330208011  TEF"¬029-024 = " "¬029-025 = "CUPOM FISCAL: 1     PDV: 038"¬029-026 = "CNPJ EMITENTE: 51.403.129/0001-81"¬029-027 = "13/09/2024  20:40:08  DEBITO A VISTA"¬030-000 = Transação Aceita.¬040-000 = ELECTRON¬170-000 = 000623500000¬200-000 = 000¬200-001 = 000¬740-000 = 417402******0653¬741-000 = PAYWAVE/VISA¬742-000 = 260430¬743-000 = 0¬800-001 = 1¬800-007 = 09330208011¬800-000 = v1.416.216¬999-999 = 0';

        // $dataExplode = explode("¬", $str);

        // $valor = explode("=",$dataExplode[3]);
        // $valor = trim($valor[1]);
        // $data = explode("=",$dataExplode[16]);
        // $data = trim($data[1]);

        // $hora = explode("=",$dataExplode[17]);
        // $hora = trim($hora[1]);

        // $nsu = explode("=",$dataExplode[10]);
        // $nsu = trim($nsu[1]);

        // $nome_rede = explode("=",$dataExplode[6]);
        // $nome_rede = trim($nome_rede[1]);

        // dd($nome_rede);

        //testando hash pix

        $item = TefMultiPlusCard::where('empresa_id', $request->empresa_id)
        ->first();
        $hash = $request->hash;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://api.multipluscard.com.br/api/Servicos/GetVendasTef");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $headers = [
            "Content-Type: application/json",
            "Content-Length: 0",
            "hash: $hash",
            "TOKEN: $item->token",
        ];
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $str = curl_exec($curl);
        $dataExplode = explode("¬", $str);

        $tipo = 'cartao';
        $search = 'VENDA PIX';
        if(preg_match("/{$search}/i", $str)) {
            $tipo = 'pix';
        }

        // dd($dataExplode);
        $valor = explode("=",$dataExplode[3]);
        $valor = trim($valor[1]);

        $nome_rede = explode("=",$dataExplode[6]);
        $nome_rede = trim($nome_rede[1]);

        if($tipo == 'pix'){

            $nsu = explode("=",$dataExplode[8]);
            $nsu = trim($nsu[1]);

            $data = explode("=",$dataExplode[14]);
            $data = trim($data[1]);
            $hora = explode("=",$dataExplode[15]);
            $hora = trim($hora[1]);
        }else{

            $nsu = explode("=",$dataExplode[10]);
            $nsu = trim($nsu[1]);

            $data = explode("=",$dataExplode[16]);
            $data = trim($data[1]);

            $hora = explode("=",$dataExplode[17]);
            $hora = trim($hora[1]);
        }
        

        echo "valor: $valor<br>";
        echo "data: $data<br>";
        echo "hora: $hora<br>";
        echo "nsu: $nsu<br>";
        echo "nome_rede: $nome_rede<br>";

    }
}
