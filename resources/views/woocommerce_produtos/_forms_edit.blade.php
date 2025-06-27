<div class="row g-2">

	<div class="col-md-4">
		{!!Form::text('nome', 'Nome')
		->required()
		->value($item->nome)
		!!}
	</div>

	<div class="col-md-2">
		{!!Form::text('woocommerce_slug', 'Slug')
		->required()
		->value($item->woocommerce_slug)
		!!}
	</div>

	<div class="col-md-2">
		{!!Form::select('woocommerce_status', 'Status ', ['publish' => 'Publicado', 'private' => 'Privado', 'pending' => 'Pendente'])
		->required()
		->attrs(['class' => 'form-select'])
		->value($item->woocommerce_status)
		!!}
	</div>

	<div class="col-md-2">
		{!!Form::select('woocommerce_stock_status', 'Status de estoque', ['instock' => 'Em estoque', 'outofstock' => 'Fora de estoque'])
		->required()
		->attrs(['class' => 'form-select'])
		->value($item->woocommerce_stock_status)
		!!}
	</div>

	<div class="col-md-2">
		{!!Form::tel('woocommerce_valor', 'Valor do anúncio')
		->value((isset($item) && $item->woocommerce_valor > 0) ? __moeda($item->woocommerce_valor) : '')
		->attrs(['class' => 'moeda input-ml'])
		->required()
		!!}
	</div>

	<div class="col-md-4">
		<div class="form-group">

			<label for="">Categoria</label>
			<select class="select2 form-control select2-multiple" name="categorias_woocommerce[]"  multiple="multiple" id="categorias_woocommerce">
				@foreach($categorias as $c)
				<option @if(in_array($c->id, $item->categorias_woocommerce)) selected @endif value="{{ $c->id }}">{{ $c->nome }}</option>
				@endforeach
			</select>
		</div>
	</div>

	<div class="col-md-2">
		{!!Form::tel('largura', 'Largura')
		->attrs(['class' => 'dimensao'])
		->value($wooProduto->dimensions ? $wooProduto->dimensions->width : '')
		!!}
	</div>
	<div class="col-md-2">
		{!!Form::tel('comprimento', 'Comprimento')
		->attrs(['class' => 'dimensao'])
		->value($wooProduto->dimensions ? $wooProduto->dimensions->length : '')
		!!}
	</div>
	<div class="col-md-2">
		{!!Form::tel('altura', 'Altura')
		->attrs(['class' => 'dimensao'])
		->value($wooProduto->dimensions ? $wooProduto->dimensions->height : '')
		!!}
	</div>
	<div class="col-md-2">
		{!!Form::tel('peso', 'Peso')
		->attrs(['class' => 'peso'])
		->value($wooProduto->weight)
		!!}
	</div>

	<div class="col-md-12">
		{!!Form::textarea('woocommerce_descricao', 'Descrição')
		->attrs(['rows' => '12', 'class' => 'tiny'])
		->value($item->woocommerce_descricao)
		!!}
	</div>

	<div class="col-12" style="text-align: right;">
		<button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
	</div>
</div>