<hr>
<div class="col-md-12">
        <h4>Receita Ótica</h4>
</div>
<div class="col-md-3">
        {!!Form::select('medico_id', 'Médico')
        !!}
</div>

<div class="col-md-3">
        {!!Form::select('convenio_id', 'Convênio')
        !!}
</div>

<div class="col-md-3">
        {!!Form::select('tipo_armacao_id', 'Tipo de Armação')
        !!}
</div>

<div class="col-md-2">
        {!!Form::date('validade', 'Validade da Receita')
        !!}
</div>

<div class="col-md-12">
        {!!Form::textarea('observacao_receita', 'Observação da Receita')
        ->attrs(['rows' => '4', 'class' => 'tiny'])
        !!}
</div>

<div class="col-md-12">

        <table class="table">
                <thead>
                        <tr>
                                <th></th>
                                <th>Esférico</th>
                                <th>Cilíndrico</th>
                                <th>Eixo</th>
                                <th>Altura</th>
                                <th>DNP</th>
                        </tr>
                </thead>
                <tbody>
                        <tr>
                                <td style="width: 200px">
                                        <i class="ri-eye-line"></i> OD LONGE
                                </td>
                                <td>
                                        <input class="form-control" name="esferico_longe_od">
                                </td>
                                <td>
                                        <input class="form-control" name="cilindrico_longe_od">
                                </td>
                                <td>
                                        <input class="form-control" name="eixo_longe_od">
                                </td>
                                <td>
                                        <input class="form-control" name="altura_longe_od">
                                </td>
                                <td>
                                        <input class="form-control" name="dnp_longe_od">
                                </td>

                        </tr>
                        <tr>
                                <td style="width: 200px">
                                        <i class="ri-eye-line"></i> OE LONGE
                                </td>
                                <td>
                                        <input class="form-control" name="esferico_longe_oe">
                                </td>
                                <td>
                                        <input class="form-control" name="cilindrico_longe_oe">
                                </td>
                                <td>
                                        <input class="form-control" name="eixo_longe_oe">
                                </td>
                                <td>
                                        <input class="form-control" name="altura_longe_oe">
                                </td>
                                <td>
                                        <input class="form-control" name="dnp_longe_oe">
                                </td>
                        </tr>
                        <tr>
                                <td style="width: 200px">
                                        <i class="ri-eye-line"></i> OD PERTO
                                </td>
                                <td>
                                        <input class="form-control" name="esferico_perto_od">
                                </td>
                                <td>
                                        <input class="form-control" name="cilindrico_perto_od">
                                </td>
                                <td>
                                        <input class="form-control" name="eixo_perto_od">
                                </td>
                                <td>
                                        <input class="form-control" name="altura_perto_od">
                                </td>
                                <td>
                                        <input class="form-control" name="dnp_perto_od">
                                </td>

                        </tr>
                        <tr>
                                <td style="width: 200px">
                                        <i class="ri-eye-line"></i> OE PERTO
                                </td>
                                <td>
                                        <input class="form-control" name="esferico_perto_oe">
                                </td>
                                <td>
                                        <input class="form-control" name="cilindrico_perto_oe">
                                </td>
                                <td>
                                        <input class="form-control" name="eixo_perto_oe">
                                </td>
                                <td>
                                        <input class="form-control" name="altura_perto_oe">
                                </td>
                                <td>
                                        <input class="form-control" name="dnp_perto_oe">
                                </td>
                        </tr>
                </tbody>
                
        </table>
</div>

<div class="card col-md-3 mt-3 form-input">
    <div class="preview">
        <button type="button" id="btn-remove-imagem" class="btn btn-link-danger btn-sm btn-danger">x</button>
        
        <img id="file-ip-1-preview" src="/imgs/no-image.png">
</div>
<label for="file-ip-1">Imagem</label>

<input type="file" id="file-ip-1" name="image" accept="image/*" onchange="showPreview(event);">
</div>

<div class="col-md-12">
        <h4>Informações Adicionais</h4>
</div>

<div class="col-md-3">
        {!!Form::select('laboratorio_id', 'Laboratório')
        !!}
</div>

<div class="card">
        <div class="card-header">
                <h5>Lente</h5>
        </div>
        <div class="card-body">
                <div class="row">

                        <div class="col-md-2">
                                {!!Form::select('tipo_lente', 'Tipo da lente', ['' => 'Selecione', 'Pronta' => 'Pronta', 'Surfaçada' => 'Surfaçada'])
                                ->attrs(['class' => 'form-select'])
                                !!}
                        </div>
                        <div class="col-md-2">
                                {!!Form::select('material_lente', 'Material da lente', ['' => 'Selecione', 'Policarbonato' => 'Policarbonato', 'Resina' => 'Resina', 'Trivex' => 'Trivex'])
                                ->attrs(['class' => 'form-select'])
                                !!}
                        </div>
                        <div class="col-md-3">
                                {!!Form::text('descricao_lente', 'Descrição da lente')
                                !!}
                        </div>
                        <div class="col-md-2">
                                {!!Form::text('coloracao_lente', 'Coloração da lente')
                                !!}
                        </div>
                </div>
        </div>
</div>





