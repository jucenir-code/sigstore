@foreach($data as $p)
<div class="autocomplete-list" onclick="addProduto('{{$p->id}}')">
	<span class="autocomplete-result"> 
		<i class="ri-search-line"></i> 
		@if($p->referencia) REF{{ $p->referencia }} @endif {{ $p->codigo_barras }} ({{ $p->nome }}) 
		R$ {{ __moeda($p->valor_unitario) }}
	</span>
</div>
@endforeach