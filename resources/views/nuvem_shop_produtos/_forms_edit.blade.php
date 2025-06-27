<div class="row g-2">

    <div class="col-md-2">
        {!!Form::tel('nome', 'Nome')
        ->value($produto->name->pt)
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('codigo_barras', 'Código de barras')
        ->value($produto->variants[0]->barcode)
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('nuvem_shop_valor', 'Valor')
        ->attrs(['class' => 'moeda inp-nuvemshop'])
        ->value(__moeda($produto->variants[0]->price))
        !!}
    </div>


    <div class="col-md-2">
        {!!Form::tel('nuvem_shop_valor_promocional', 'Valor promocional')
        ->attrs(['class' => 'moeda'])
        ->value(__moeda($produto->variants[0]->promotional_price))
        !!}
    </div>

    <div class="col-md-4">
        {!!Form::select('categoria_nuvem_shop', 'Categoria')
        ->options($categoria ? $categoria : [])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('altura_nuvem_shop', 'Altura')
        ->attrs(['class' => 'dimensao inp-nuvemshop'])
        ->value($produto->variants[0]->height)
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('largura_nuvem_shop', 'Largura')
        ->attrs(['class' => 'dimensao inp-nuvemshop'])
        ->value($produto->variants[0]->width)
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('comprimento_nuvem_shop', 'Comprimento')
        ->attrs(['class' => 'dimensao inp-nuvemshop'])
        ->value($produto->variants[0]->depth)
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('peso_nuvem_shop', 'Peso')
        ->attrs(['class' => 'peso inp-nuvemshop'])
        ->value($produto->variants[0]->weight)
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('estoque', 'Estoque')
        ->attrs(['class' => 'quantidade'])
        ->value($produto->variants[0]->stock)
        !!}
    </div>

    <div class="col-12">
        {!!Form::textarea('texto_nuvem_shop', 'Descrição')
        ->value($produto->description ? $produto->description->pt : '')
        ->attrs(['class' => 'tiny'])
        !!}
    </div>

    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>
</div>

@section('js')

<script src="/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
    $(function(){
        tinymce.init({ selector: 'textarea.tiny', language: 'pt_BR'})

        setTimeout(() => {
            $('.tox-promotion, .tox-statusbar__right-container').addClass('d-none')
        }, 500)
    })
</script>
@endsection