<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FormatoArmacaoOticaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:tratamento_otica_view', ['only' => ['create', 'store']]);
        $this->middleware('permission:convenio_create', ['only' => ['edit', 'update']]);
        $this->middleware('permission:convenio_edit', ['only' => ['show', 'index']]);
        $this->middleware('permission:convenio_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {   
        $data = TratamentoOtica::where('empresa_id', request()->empresa_id)
        ->when(!empty($request->nome), function ($q) use ($request) {
            return $q->where('nome', 'LIKE', "%$request->nome%");
        })
        ->paginate(env("PAGINACAO"));

        return view('tratamento_otica.index', compact('data'));
    }

    public function create()
    {
        return view('tratamento_otica.create');
    }

    public function edit($id)
    {
        $item = TratamentoOtica::findOrFail($id);
        __validaObjetoEmpresa($item);
        return view('tratamento_otica.edit', compact('item'));
    }

    public function store(Request $request)
    {
        try {
            
            TratamentoOtica::create($request->all());
            __createLog($request->empresa_id, 'Tratamento Ótica', 'cadastrar', $request->nome);
            session()->flash('flash_success', 'Cadastrado com sucesso');
        } catch (\Exception $e) {
            __createLog($request->empresa_id, 'Tratamento Ótica', 'erro', $e->getMessage());
            session()->flash('flash_error', 'Não foi possível concluir o cadastro' . $e->getMessage());
        }
        return redirect()->route('tratamentos-otica.index');
    }

    public function update(Request $request, $id)
    {
        $item = TratamentoOtica::findOrFail($id);
        __validaObjetoEmpresa($item);
        try {
            
            $item->fill($request->all())->save();
            __createLog($request->empresa_id, 'Tratamento Ótica', 'editar', $request->nome);
            session()->flash('flash_success', 'Alterado com sucesso');
        } catch (\Exception $e) {
            __createLog($request->empresa_id, 'Tratamento Ótica', 'erro', $e->getMessage());
            session()->flash('flash_error', 'Não foi possível alterar o cadastro' . $e->getMessage());
        }
        return redirect()->route('tratamentos-otica.index');
    }

    public function destroy($id)
    {
        $item = TratamentoOtica::findOrFail($id);
        __validaObjetoEmpresa($item);
        try {
            $descricaoLog = $item->nome;
            $item->delete();
            __createLog(request()->empresa_id, 'Formato Armação', 'excluir', $descricaoLog);
            session()->flash('flash_success', 'Deletado com sucesso');
        } catch (\Exception $e) {
            __createLog(request()->empresa_id, 'Formato Armação', 'erro', $e->getMessage());
            session()->flash('flash_error', 'Não foi possível deletar' . $e->getMessage());
        }
        return redirect()->back();
    }
}
