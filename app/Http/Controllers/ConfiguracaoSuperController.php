<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracaoSuper;
use Illuminate\Http\Request;

class ConfiguracaoSuperController extends Controller
{
    public function index()
    {
        $item = ConfiguracaoSuper::first();
        return view('config_super.index', compact('item'));
    }

    public function store(Request $request)
    {
        $this->__validate($request);
        $item = ConfiguracaoSuper::first();
        $request->merge([
            'timeout_nfe' => $request->timeout_nfe ?? 8,
            'timeout_nfce' => $request->timeout_nfe ?? 8,
            'timeout_cte' => $request->timeout_nfe ?? 8,
            'timeout_mdfe' => $request->timeout_nfe ?? 8,
        ]);
        try {
            if ($item == null) {
                ConfiguracaoSuper::create($request->all());
                session()->flash("flash_success", "Dados cadastrado com sucesso!");
            } else {
                $item->fill($request->all())->save();
                session()->flash("flash_success", "Dados alterados com sucesso!");
            }
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->back();
    }

    private function __validate(Request $request)
    {
        $rules = [
            'cpf_cnpj' => 'required',
            'name' => 'required',
            'email' => 'required',
            'telefone' => 'required',
        ];
        $messages = [
            'cpf_cnpj.required' => 'Campo obrigatório',
            'name.required' => 'Campo obrigatório',
            'email.required' => 'Campo obrigatório',
            'telefone.required' => 'Campo obrigatório'
        ];
        $this->validate($request, $rules, $messages);
    }
}
