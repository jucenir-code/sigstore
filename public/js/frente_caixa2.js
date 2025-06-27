document.addEventListener("DOMContentLoaded", function(event) {
	setTimeout(() => {
		$('.div-main').removeClass('d-none')
	}, 100)
});
$(function(){
	$('#inp-pesquisa').focus()
	
	$(document).on('click', '.categorias-pagination a', function(event){
		event.preventDefault()
		var page = $(this).attr('href').split('page=')[1];
		$.get(path_url + 'api/frenteCaixa/categorias-page?page='+page, { 
			empresa_id: $('#empresa_id').val(), 
		})
		.done((res) => {
			$('.div-categorias').html(res)
		}).fail((err) => {
			console.log(err)
			
		})
	})

	$(document).on('click', '.marcas-pagination a', function(event){
		event.preventDefault()
		var page = $(this).attr('href').split('page=')[1];
		$.get(path_url + 'api/frenteCaixa/marcas-page?page='+page, { empresa_id: $('#empresa_id').val() })
		.done((res) => {
			$('.div-marcas').html(res)
		}).fail((err) => {
			console.log(err)
			
		})
	})

	$(document).on('click', '.produtos-pagination a', function(event){
		event.preventDefault()

		let data = {
			categoria_id : CATEGORIAID,
			marca_id : MARCAID,
			empresa_id: $('#empresa_id').val(),
			lista_id: $('#lista_id').val(),
			local_id: $('#local_id').val(),
		}
		var page = $(this).attr('href').split('page=')[1];
		$.get(path_url + 'api/frenteCaixa/produtos-page?page='+page, data)
		.done((res) => {
			$('.div-produtos').html(res)
		}).fail((err) => {
			console.log(err)
		})
	})

	if($('#venda_id').val() == '0'){
		$('#inp-desconto').val('0')
		$('#inp-acrescimo').val('0')
		$('#inp-valor_total').val('0')
	}else{
		calculaTotal()
	}
})

function resetProdutos() {

	CATEGORIAID = 0
	MARCAID = 0

	getProdutos()
}

var CATEGORIAID = 0
var MARCAID = 0
$(document).on('click', '.list-categoria', function(){
	CATEGORIAID = $(this).data('id')

	$('.list-categoria').removeClass('active')
	$('.categoria-'+CATEGORIAID).addClass('active')
	getProdutos()
})

$(document).on('keyup', '#inp-desconto', function(e){
	calculaDesconto()
});
$(document).on('change', '#inp-tipo_desconto', function(e){
	calculaDesconto()
});

function calculaDesconto(){
	let desconto = convertMoedaToFloat($('#inp-desconto').val())
	let tipo_desconto = $('#inp-tipo_desconto').val()
	let total = $('#inp-valor_total').val()
	let valor_desconto = desconto
	if(tipo_desconto == '%'){
		valor_desconto = total*(desconto/100)
	}

	$('#inp-valor_desconto').val(convertFloatToMoeda(valor_desconto))
	calculaTotal()
}

function removeItem(code){
	swal({
		title: "Você está certo?",
		text: "Deseja realmente remover este item da venda?",
		icon: "warning",
		buttons: true,
		buttons: ["Cancelar", "Excluir"],
		dangerMode: true,
	}).then((isConfirm) => {
		if (isConfirm) {

			$('.product-line-'+code).remove()
			calculaTotal()
		} else {
			swal("", "Este item está salvo!", "info");
		}
	});
}

function editItem(code){
	let produto_id = $('.product-line-'+code).find('.produto_id').val()
	let quantidade = $('.product-line-'+code).find('.quantidade').val()
	let valor_unitario = $('.product-line-'+code).find('.valor_unitario').val()
	let data = {
		produto_id: produto_id,
		code: code,
		quantidade: quantidade,
		valor_unitario: valor_unitario
	}
	$.get(path_url + 'api/frenteCaixa/edit-item', data)
	.done((res) => {
		// console.log(res)
		$('#edit_item_pdv').modal('show')
		$('#edit_item_pdv .modal-body').html(res)

	}).fail((err) => {
		console.log(err)
	})
}

$(document).on('click', '#btn-edit-item', function(e){
	let id = $('#inp-id-item').val()
	let code = $('#inp-code-item').val()
	let qtd = convertMoedaToFloat($('#inp-qtd-item').val())
	let valor_unitario = convertMoedaToFloat($('#inp-valor-unitario-item').val())

	$.get(path_url + "api/orcamentos/valida-desconto", 
	{ 
		produto_id: id, valor: valor_unitario, empresa_id: $('#empresa_id').val(), pdv: 1
	}).done((res) => {
		$('.product-line-'+code).find('.quantidade').val(qtd)
		$('.product-line-'+code).find('.qtd-row').val(qtd)
		$('.product-line-'+code).find('.valor_unitario').val(convertFloatToMoeda(valor_unitario))
		$('.product-line-'+code).find('.subtotal_item').val(convertFloatToMoeda(valor_unitario*qtd))

		$('.product-line-'+code).find('.price').text("R$ " + convertFloatToMoeda(valor_unitario*qtd))
		$('.product-line-'+code).find('.qtd-row').val(qtd)
		$('#edit_item_pdv').modal('hide')
		calculaTotal()
	})
	.fail((err) => {
		console.log(err)
		let v = err.responseJSON
		$('#inp-valor-unitario-item').val(convertFloatToMoeda(v))
		swal("Erro", "Valor minímo para este item " + convertFloatToMoeda(v), "error")
	})

	
})

$(document).on('click', '.increment-decrement', function(e){
	let bt = $(this)[0].innerText
	let code = $(this).data('code')
	let qtd = 0;
	if(bt == '+'){
		qtd = $(this).prev().val()
		qtd++;
	}else{
		qtd = $(this).next().val()
		qtd--;
		if(qtd <= 0){
			removeItem(code)
			return;
		}
	}

	setTimeout(() => {
		$('.product-line-'+code).find('.quantidade').val(qtd)
		$('.product-line-'+code).find('.qtd-row').val(qtd)
		let valor_unitario = $('.product-line-'+code).find('.valor_unitario').val()

		$('.product-line-'+code).find('.price').text("R$ " + convertFloatToMoeda(valor_unitario*qtd))
		$('.product-line-'+code).find('.subtotal_item').val(convertFloatToMoeda(valor_unitario*qtd))

		calculaTotal()

	}, 10)
});

function selecionaLista(){
	let tipo_pagamento_lista = $('#inp-tipo_pagamento_lista').val()
	let funcionario_lista_id = $('#inp-funcionario_lista_id').val()
	let lista_preco_id = $('#inp-lista_preco_id').val()

	if(!lista_preco_id){
		swal("Alerta", "Selecione a lista", "warning")
		return;
	}

	if(tipo_pagamento_lista){
		$('#inp-tipo_pagamento').val(tipo_pagamento_lista).change()
	}
	if(funcionario_lista_id){
		$.get(path_url + "api/funcionarios/find", {id: funcionario_lista_id})
		.done((res) => {
			var newOption = new Option(res.nome, res.id, true, false);
			$('#inp-funcionario_id').append(newOption);
			$('.funcionario_selecionado').text(res.nome)

		})
		.fail((err) => {
			console.log(err);
		});
	}

	$('#lista_id').val(lista_preco_id)
	setTimeout(() => {
		resetProdutos()
	}, 10)
	setTimeout(() => {
		$("#inp-pesquisa").focus();
	}, 500)
}

$("body").on("change", ".tipo_pagamento", function () {
	let cliente_id = $('#inp-cliente_id').val()
	if($(this).val() == '06' && !cliente_id){
		swal("Alerta", "Informe o cliente!", "warning")
		$(this).val('').change()
	}
})

$(document).on("change", "#inp-cliente_id", function () {
	$.get(path_url + "api/clientes/find/" + $(this).val())
	.done((cliente) => {
		console.log(cliente)
		if(cliente.lista_preco){

			$('#lista_id').val(cliente.lista_preco.id)
			setTimeout(() => {
				resetProdutos()
			}, 10)
			setTimeout(() => {
				$("#inp-pesquisa").focus();
			}, 500)
		}
	})
	.fail((err) => {
		console.log(err);
	});
})

$("body").on("change", "#inp-lista_preco_id", function () {
	$.get(path_url + "api/lista-preco/find", {id: $(this).val()})
	.done((res) => {
		$('#inp-tipo_pagamento_lista').val(res.tipo_pagamento).change()

		if(res.funcionario_id){
			$('#inp-funcionario_lista_id').val(res.funcionario_id).change();
		}
	})
	.fail((err) => {
		console.log(err);
	});
})

$("#lista_precos select").each(function () {

	let id = $(this).prop("id");

	if (id == "inp-lista_preco_id") {

		$(this).select2({
			minimumInputLength: 2,
			language: "pt-BR",
			placeholder: "Digite para buscar a lista de preço",
			theme: "bootstrap4",
			dropdownParent: $(this).parent(),
			ajax: {
				cache: true,
				url: path_url + "api/lista-preco/pesquisa",
				dataType: "json",
				data: function (params) {
					var query = {
						pesquisa: params.term,
						empresa_id: $("#empresa_id").val(),
						usuario_id: $("#usuario_id").val(),
						tipo_pagamento_lista: $("#inp-tipo_pagamento_lista").val(),
						funcionario_lista_id: $("#inp-funcionario_lista_id").val(),
					};
					return query;
				},
				processResults: function (response) {
					var results = [];

					$.each(response, function (i, v) {
						var o = {};
						o.id = v.id;

						o.text = v.nome + " " + v.percentual_alteracao + "%";
						o.value = v.id;
						results.push(o);
					});
					return {
						results: results,
					};
				},
			},
		});
	}
});

$(document).on('keyup', '#inp-acrescimo', function(e){
	calculaAcrescimo()
});
$(document).on('change', '#inp-tipo_acrescimo', function(e){
	calculaAcrescimo()
});

function calculaAcrescimo(){
	let acrescimo = $('#inp-acrescimo').val()
	let tipo_acrescimo = $('#inp-tipo_acrescimo').val()
	let total = $('#inp-valor_total').val()
	let valor_acrescimo = acrescimo
	if(tipo_acrescimo == '%'){
		valor_acrescimo = total*(acrescimo/100)
	}

	$('#inp-valor_acrescimo').val(convertFloatToMoeda(valor_acrescimo))
	calculaTotal()
}

$(document).on('keypress', '#inp-pesquisa', function(e){
	let pesquisa = $(this).val()

	if(pesquisa.length > 1){
		let data = {
			pesquisa: pesquisa,
			empresa_id: $('#empresa_id').val()
		}
		$.get(path_url + 'api/frenteCaixa/pesquisa-produto', data)
		.done((res) => {
			$('.results-list').removeClass('d-none')
			$('.results-list').html(res)
		}).fail((err) => {
			console.log(err)
		})
	}

	if(e.which == 13) {
		$.get(path_url + "api/produtos/findByBarcode",
		{
			barcode: pesquisa,
			empresa_id: $('#empresa_id').val(),
			lista_id: $('#lista_id').val(),
			usuario_id: $('#usuario_id').val()
		})
		.done((e) => {

			if(e){
				addProduto(e.id)
			}else{
				buscarPorReferencia(barcode)
			}
		})
		.fail((err) => {
			console.log(err);
			buscarPorReferencia(barcode)
		});

		e.preventDefault();
	}
})

function buscarPorReferencia(barcode) {
	$.get(path_url + "api/produtos/findByBarcodeReference",
	{
		barcode: barcode,
		empresa_id: $('#empresa_id').val(),
		usuario_id: $('#usuario_id').val()
	})
	.done((e) => {
		$(".table-itens tbody").append(e);
		calcTotal();
	})
	.fail((e) => {
		console.log(e);
		swal("Erro", "Produto não localizado!", "error")
	});
}

$(document).on('click', '.list-marca', function(){
	MARCAID = $(this).data('id')

	$('.list-marca').removeClass('active')
	$('.marca-'+MARCAID).addClass('active')
	getProdutos()
})

function getProdutos(){
	
	let data = {
		categoria_id : CATEGORIAID,
		marca_id : MARCAID,
		empresa_id: $('#empresa_id').val(),
		lista_id: $('#lista_id').val(),
		local_id: $('#local_id').val(),
	}

	$.get(path_url + 'api/frenteCaixa/produtos-page?page=1', data)
	.done((res) => {
		$('.div-produtos').html(res)
	}).fail((err) => {
		console.log(err)
	})
}

function infoProduto(id){
	$.get(path_url + 'api/produtos/info', { produto_id: id })
	.done((res) => {
		$('#modal_info_produto .modal-body').html(res)
		$('#modal_info_produto').modal('show')

	}).fail((err) => {
		console.log(err)

	})
}

function validaCaixa() {
	let abertura = $('#abertura').val()
	if (!abertura) {
		$('#modal-abrir_caixa').modal('show')
		return
	}
}

function addProduto(id){
	$('.results-list').html('').addClass('d-none')
	$('#inp-pesquisa').val('')
	let qtd = 0;

	let abertura = $('#abertura').val()
	let agrupar_itens = $('#agrupar_itens').val()

	$('.products').each(function () {
		if(id == $(this).find('.produto_id').val()){
			qtd += parseFloat($(this).find('.quantidade').val())
		}
	})

	if (abertura) {
		$.post(path_url + 'api/frenteCaixa/add-produto', { 
			produto_id: id, 
			lista_id: $('#lista_id').val(), 
			qtd: qtd,
            local_id: $('#local_id').val()
		})
		.done((res) => {
			let idDup = 0
			if(agrupar_itens == 1){
				$(".produto_row").each(function () {
					if($(this).val() == id){
						idDup = $(this).val()
					}
				})
			}
			setTimeout(() => {
				if(idDup == 0){
					$('.itens-cart').append(res)
				}else{
					$(".itens-cart .products").each(function(){
						if($(this).find('.produto_row').val() == id){
							let qtdAnt = convertMoedaToFloat($(this).find('.quantidade').val())

							$(this).find('.quantidade').val(qtdAnt+1)
							$(this).find('.qtd-row').val(qtdAnt+1)
						}
					})
				}
			},10)
			calculaTotal()
			beepSucesso()
			$('#inp-pesquisa').focus()
		}).fail((err) => {
			console.log(err)
			swal("Erro", err.responseJSON, "error")
			beepErro()
		})
	}else{
		swal("Atenção", "Abra o caixa para continuar!", "warning").then(() => {
			validaCaixa()
		})

	}
}

function calculaTotal(){
	let desconto = convertMoedaToFloat($('#inp-valor_desconto').val())
	let acrescimo = convertMoedaToFloat($('#inp-valor_acrescimo').val())
	setTimeout(() => {
		let total = 0
		$(".price").each(function () {
			total += convertMoedaToFloat($(this)[0].innerText);
		});

		total = total + acrescimo - desconto
		$('.total').text(convertFloatToMoeda(total))
		$('#inp-valor_total').val(total.toFixed(2))
	}, 100)
}

function beepSucesso(){
	let alerta = $('#alerta_sonoro').val()
	if(alerta == 1){
		var audio = new Audio('/audio/beep.mp3');
		audio.addEventListener('canplaythrough', function() {
			audio.play();
		});
	}
}
function beepErro(){
	let alerta = $('#alerta_sonoro').val()
	if(alerta == 1){
		var audio = new Audio('/audio/beep_error.mp3');
		audio.addEventListener('canplaythrough', function() {
			audio.play();
		});
	}
}

$(document).on('click', '#btn_nao_fiscal', function(){
	finalizar('nao_fiscal')
})

$(document).on('click', '#btn_fiscal', function(){
	finalizar('fiscal')
})

$('.btn-vendas-suspensas').click(() => {
	$.get(path_url + "api/frenteCaixa/venda-suspensas",
	{
		empresa_id: $('#empresa_id').val(),
	})
	.done((data) => {
		$('.table-vendas-suspensas tbody').html(data)
	})
	.fail((e) => {
		console.log(e);
	});
})

var emitirNfce = false
function finalizar(tipo){
	let soma = 0

	let tipoPagamentoInvalido = 0
	let dataInvalida = 0
	let total = parseFloat($('#inp-valor_total').val())
	$('.fatura .valor_integral_row').each(function () {
		soma += convertMoedaToFloat($(this).val());
	});

	$('.fatura .tipo_pagamento').each(function () {
		if(!$(this).val()){
			tipoPagamentoInvalido = 1
		}
	});

	$('.fatura .data_vencimento').each(function () {
		if(!$(this).val()){
			dataInvalida = 1
		}
	});

	total = parseFloat(total.toFixed(2))
	soma = parseFloat(soma.toFixed(2))

	setTimeout(() => {
		if(total != soma){
			swal("Erro", "Total divergente da soma da fatura.", "error")
			return;
		}
		if(tipoPagamentoInvalido){
			swal("Erro", "Defina o tipo de pagamento para todas linhas de pagamento.", "error")
			return;
		}
		if(dataInvalida){
			swal("Erro", "Defina o vencimento para todas linhas de pagamento.", "error")
			return;
		}

		if(tipo == 'fiscal'){
			emitirNfce = true
		}

		if($("#form-pdv-update")){
			$("#form-pdv-update").submit()
		}
		if($("#form-pdv")){
			$("#form-pdv").submit()
		}
	}, 20)

}


$(document).on('keyup', '.valor_integral_row', function(){
	let soma = 0

	let total = parseFloat($('#inp-valor_total').val())
	$('.fatura .valor_integral_row').each(function () {
		soma += convertMoedaToFloat($(this).val());
	});
	$('.total-restante').text("R$ " + convertFloatToMoeda(total-soma))

});

$(document).on('click', '#btn-finalizar', function(){
	let total = $('#inp-valor_total').val()

	if(total == 0){
		swal("Erro", "Valor da venda deve ser maior que zero!", "error")
		return;
	}
	$('.valor_integral_row').val(convertFloatToMoeda(total))
	$('.total-venda-modal').text("R$ " + convertFloatToMoeda(total))
	$('.total-restante').text("R$ " + convertFloatToMoeda(total))
	$('#modal_finalizar_pdv2').modal('show')
});

$("#form-pdv").on("submit", function (e) {

	e.preventDefault();
	const form = $(e.target);
	var json = $(this).serializeFormJSON();

	json.empresa_id = $('#empresa_id').val()
	json.usuario_id = $('#usuario_id').val()

	json.desconto = convertMoedaToFloat($('#inp-valor_desconto').val())
	json.acrescimo = convertMoedaToFloat($('#inp-valor_acrescimo').val())
	// console.log(">>>>>>>> salvando ", json);
	// alert('teste')
	$.post(path_url + 'api/frenteCaixa/store', json)
	.done((success) => {
		if (emitirNfce == true) {
			gerarNfce(success)
		} else {
            // swal("Sucesso", "Venda finalizada com sucesso, deseja imprimir o comprovante?", "success")

            swal({
            	title: "Sucesso",
            	text: "Venda finalizada com sucesso, deseja imprimir o comprovante?",
            	icon: "success",
            	buttons: true,
            	buttons: ["Não", "Sim"],
            	dangerMode: true,
            }).then((isConfirm) => {
            	if (isConfirm) {
            		window.open(path_url + 'frontbox/imprimir-nao-fiscal/' + success.id, "_blank")
            	} else {
                    // location.reload()
                }
                if($('#pedido_delivery_id').length){
                	location.href = '/pedidos-delivery';
                }else if($('#pedido_id').length){
                	location.href = '/pedidos-cardapio';
                }else{
                	location.href = '/frontbox/create';
                }
            });
        }
    }).fail((err) => {
    	swal("Erro", err.responseJSON, "error")
    	console.log(err)
    })
});

var update = false
$("#form-pdv-update").on("submit", function (e) {
	update = true
	e.preventDefault();
	const form = $(e.target);
	var json = $(this).serializeFormJSON();

	json.empresa_id = $('#empresa_id').val()
	json.usuario_id = $('#usuario_id').val()

	json.desconto = convertMoedaToFloat($('#valor_desconto').text())
	json.acrescimo = convertMoedaToFloat($('#valor_acrescimo').text())
	console.log(">>>>>>>> salvando ", json);
	$.post(path_url + 'api/frenteCaixa/update/'+$('#venda_id').val(), json)
	.done((success) => {

		if (emitirNfce == true) {
			gerarNfce(success)
		} else {

			swal({
				title: "Sucesso",
				text: "Venda atualizada com sucesso, deseja imprimir o comprovante?",
				icon: "success",
				buttons: true,
				buttons: ["Não", "Sim"],
				dangerMode: true,
			}).then((isConfirm) => {
				if (isConfirm) {
					window.open(path_url + 'frontbox/imprimir-nao-fiscal/' + success.id, "_blank")
				} else {
                    // location.reload()
                }
                if($('#pedido_delivery_id').length){
                	location.href = '/pedidos-delivery';
                }else if($('#pedido_id').length){
                	location.href = '/pedidos-cardapio';
                }else{
                	if(update){
                		location.href = path_url+'frontbox'
                	}else{
                		location.reload()
                	}
                }
            });
		}
	}).fail((err) => {
		console.log(err)
	})
});

function gerarNfce(venda) {

	let empresa_id = $("#empresa_id").val();

	$.post(path_url + "api/nfce_painel/emitir", {
		id: venda.id,
	})
	.done((success) => {
		swal("Sucesso", "NFCe emitida " + success.recibo + " - chave: [" + success.chave + "]", "success")
		.then(() => {
			window.open(path_url + 'nfce/imprimir/' + venda.id, "_blank")
			setTimeout(() => {
				if(!update){
					location.reload()
				}else{
					location.href = path_url+'frontbox'
				}
			}, 100)
		})
	})
	.fail((err) => {
		console.log(err)

		swal("Algo deu errado", err.responseJSON, "error")

	})
}

$.fn.serializeFormJSON = function () {

	var o = {};
	var a = this.serializeArray();
	$.each(a, function () {
		if (o[this.name]) {
			if (!o[this.name].push) {
				o[this.name] = [o[this.name]];
			}
			o[this.name].push(this.value || '');
		} else {
			o[this.name] = this.value || '';
		}
	});
	return o;
};

$("body").on("click", "#btn-suspender", function () {

	let total = $('#inp-valor_total').val()

	if(total == 0){
		swal("Erro", "Valor da venda deve ser maior que zero!", "error")
		return;
	}
	swal({
		title: "Você esta certo?",
		text: "Deseja suspender esta venda?",
		icon: "warning",
		buttons: true,
		buttons: ["Cancelar", "Suspender"],
	}).then(confirm => {
		if (confirm) {
			// console.clear()

			var json = $("#form-pdv").serializeFormJSON();
			json.empresa_id = $('#empresa_id').val()
			json.usuario_id = $('#usuario_id').val()

			$.post(path_url + 'api/frenteCaixa/suspender', json)
			.done((success) => {
				// console.log(success)
				swal("Sucesso", "Venda suspensa!", "success")
				.then(() => {
					location.reload()
				})
			})
			.fail((err) => {
				console.log(err)
				swal("Erro", "Algo deu errado", "error")
			})
		}
	});
})

