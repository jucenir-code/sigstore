<?php

use App\Models\Caixa;
use App\Models\ContadorEmpresa;
use App\Models\Localizacao;
use App\Models\UsuarioLocalizacao;
use App\Models\AcaoLog;
use App\Models\ApiLog;
use App\Models\ApiConfig;
use App\Models\MarketPlaceConfig;
use Illuminate\Support\Facades\Auth;

function __convert_value_bd($valor)
{
	if (strlen($valor) >= 8) {
		$valor = str_replace(".", "", $valor);
	}
	$valor = str_replace(",", ".", $valor);

	return $valor;
}

function __validaObjetoEmpresa($objeto)
{
	if(!Auth::user()->empresa){
		return true;
	}
	$empresa_id = Auth::user()->empresa->empresa_id;

	if(isset($objeto->empresa_id)){
		if($objeto->empresa_id !=  $empresa_id){
			abort(403);
		}
	}
	return true;
}

function __moeda($valor, $casas_decimais = 2)
{
	return number_format($valor, $casas_decimais, ',', '.');
}

function __moedaInput($valor, $casas_decimais = 2)
{
	return number_format($valor, $casas_decimais, ',', '');
}

function __data_pt($data, $hora = true)
{
	if ($hora) {
		return \Carbon\Carbon::parse($data)->format('d/m/Y H:i');
	} else {
		return \Carbon\Carbon::parse($data)->format('d/m/Y');
	}
}

function __hora_pt($data)
{
	return \Carbon\Carbon::parse($data)->format('H:i');
}

function __isMaster()
{
	if (Auth::user()->email == env("MAILMASTER")) {
		return 1;
	}
	return 0;
}

function __isEmpresaMaster($empresa)
{
	foreach($empresa->usuarios as $u){
		if($u->usuario->email == env("MAILMASTER")){
			return 1;
		}
	}
	return 0;
}

function __isContador()
{
	if (Auth::user()->tipo_contador == 1) {
		return 1;
	}
	return 0;
}

function __escolheLocalidade()
{
	return Auth::user()->escolher_localidade_venda;
}

function __empresasDoContador()
{
	$contador_id = Auth::user()->empresa->empresa_id;
	return ContadorEmpresa::where('contador_id', $contador_id)->get();
}

function __isAdmin()
{
	return Auth::user()->admin;
}

function __getError($e)
{
	return "Linha: " . $e->getLine() . ", mensagem: " . $e->getMessage() . ", arquivo: " . $e->getFile();
}

function __isCaixaAberto()
{
	$usuario_id = Auth::user()->id;
	return Caixa::where('usuario_id', $usuario_id)->where('status', 1)->first();
}

function get_id_user()
{
	$usr = Auth::user()->id;
	return $usr;
}

function get_name_user()
{
	$usr = Auth::user()->name;
	return $usr;
}

function __mask($val, $mask){
	$maskared = '';
	$k = 0;
	for ($i = 0; $i <= strlen($mask) - 1; ++$i) {
		if ($mask[$i] == '#') {
			if (isset($val[$k])) {
				$maskared .= $val[$k++];
			}
		} else {
			if (isset($mask[$i])) {
				$maskared .= $mask[$i];
			}
		}
	}

	return $maskared;
}

function __setMask($doc){
	$doc = preg_replace('/[^0-9]/', '', $doc);
	$mask = '##.###.###/####-##';
	if (strlen($doc) == 11) {
		$mask = '###.###.###-##';
	}
	return __mask($doc, $mask);
}

function __isPlanoFiscal(){
	$empresa = auth::user()->empresa;
	if(!$empresa) return false;

	$plano = $empresa->empresa->plano;
	if($plano){
		if($plano->plano->fiscal) return 1;
	}
	return false;
}

function __isActivePlan($empresa, $menu){
	if(!$empresa) return false;
	$plano = $empresa->empresa->plano;
	if($plano){
		$modulos = json_decode($plano->plano->modulos);
		if(in_array($menu, $modulos)) return true;
		else return false;
	}
	return false;
}

function __isInternacionalizar($empresa){
	if(!$empresa) return false;
	$config = $empresa->empresa->configuracaoCardapio;
	if(!$config) return false;
	if($config->intercionalizar == 1) return 1;
	return false;
}

function __isNotificacao($empresa){
	if(!$empresa) return false;
	$config = $empresa->empresa->configuracaoCardapio;
	if(!$config) return false;
	return 1;
}

function __isNotificacaoMarketPlace($empresa){
	if(!$empresa) return false;
	$config = $empresa->empresa->configuracaoMarketPlace;
	if(!$config) return false;
	return 1;
}

function __isNotificacaoEcommerce($empresa){
	if(!$empresa) return false;
	$config = $empresa->empresa->configuracaoEcommerce;
	if(!$config) return false;
	return 1;
}

function __countLocalAtivo(){
	if(!Auth::user()->empresa){
		return 0;
	}
	$empresa_id = Auth::user()->empresa->empresa_id;
	return Localizacao::where('empresa_id', $empresa_id)
	->where('status', 1)->count();
}

function __getLocaisAtivos(){
	$empresa_id = Auth::user()->empresa->empresa_id;
	return Localizacao::where('empresa_id', $empresa_id)
	->where('status', 1)->get();
}

function __getLocalAtivo(){
	if(!Auth::user()->empresa){
		return 0;
	}
	$empresa_id = Auth::user()->empresa->empresa_id;
	return Localizacao::where('empresa_id', $empresa_id)
	->where('status', 1)->first();
}

function __getLocaisAtivoUsuario(){
	$usuario_id = Auth::user()->id;
	return Localizacao::where('usuario_localizacaos.usuario_id', $usuario_id)
	->select('localizacaos.*')
	->join('usuario_localizacaos', 'usuario_localizacaos.localizacao_id', '=', 'localizacaos.id')
	->where('localizacaos.status', 1)->get();
}

function __objetoParaEmissao($empresa, $local_id){

	$primeiraLocalizacao = Localizacao::where('empresa_id', $empresa->id)
	->where('status', 1)->first();

	$count = Localizacao::where('empresa_id', $empresa->id)
	->where('status', 1)->count();
	if($count <= 1) return $empresa;

	$localizacao = Localizacao::findOrFail($local_id);
	if($primeiraLocalizacao == $localizacao) return $empresa;
	return $localizacao;
}

function __createLog($empresa_id, $local, $acao, $descricao){
	AcaoLog::create([
		'empresa_id' => $empresa_id,
		'local' => $local,
		'acao' => $acao,
		'descricao' => substr($descricao, 0, 255),
	]);
}

function __createApiLog($empresa_id, $token, $status, $descricao, $tipo, $prefixo){
	ApiLog::create([
		'empresa_id' => $empresa_id,
		'token' => $token,
		'status' => $status,
		'descricao' => substr($descricao, 0, 255),
		'tipo' => $tipo,
		'prefixo' => $prefixo
	]);
}

function __validaPermissaoToken($token, $permissao){
	$item = ApiConfig::where('token', $token)->first();
	if($item){
		$permissoes_acesso = $item->permissoes_acesso != 'null' ? json_decode($item->permissoes_acesso) : [];

		if(in_array($permissao, $permissoes_acesso)) return 1;
	}
	return 0;
}

function __isSegmentoPlanoOtica(){
	$empresa = auth::user()->empresa;
	if(!$empresa) return false;

	$plano = $empresa->empresa->plano;
	if($plano){
		if($plano->plano->segmento && $plano->plano->segmento->nome == 'Ótica') return 1;
	}
	return false;
}

function __isSegmentoServico($empresa_id){
	$config = MarketPlaceConfig::where('empresa_id', $empresa_id)->first();
	if($config == null) return 0;
	$segmento = json_decode($config->segmento);
	if(in_array('servicos', $segmento)) return 1;
	return 0;
}

function __isSegmentoProduto($empresa_id){
	$config = MarketPlaceConfig::where('empresa_id', $empresa_id)->first();
	if($config == null) return 0;
	$segmento = json_decode($config->segmento);
	if(in_array('produtos', $segmento)) return 1;
	return 0;
}

function __isProdutoServicoDelivery($empresa_id){
	if(__isSegmentoProduto($empresa_id) && __isSegmentoServico($empresa_id)) return 1;
	return 0;
}
