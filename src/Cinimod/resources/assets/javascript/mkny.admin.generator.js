/**
 * Funcao para fazer o tratamento anti-bubbling de requisicoes em "tempo-real"
 * 
 * @param  {string}   selector   Seletor, usado para encontrar o elemento (jquery)
 * @param  {Function} callback   Callback a ser executado
 * @param  {string}   fireEvent  Evento(s) que serao escutados para disparar o evento
 * @param  {string}   blockEvent Evento(s) que serao escutados para travar o disparo
 * @param  {integer}   timeoutMs  Intervalo de fireEvent
 * @return {void}
 */
function fireFluid(selector, callback, fireEvent, blockEvent, timeoutMs, callbackBlocking) {

	fireEvent = fireEvent || 'keyup';
	blockEvent = blockEvent || 'keydown';
	timeoutMs = timeoutMs || 1000;
	callback = callback || function() {};
	callbackBlocking = callbackBlocking || function() {};

	// Disparador do ajax
	var ajaxCall = null;

	// Variavel para guardar o timeout do keyup/down
	var timeout = null;

	// callbackAjax = function(){
	// 	ajaxCall = $.ajax({
	// 		url : 'link',
	// 		type: 'post',
	// 		dataType: 'json',
	// 		error : function(){},
	// 		success : function(){}
	// 	});
	// };

	// Busca o seletor e coloca os event-listener
	$(selector).on(fireEvent, function(event) {
		// Variavel por referencia
		var e = $(this);

		// Ao executar o keyup, o elemento aloca a instancia do timeout
		// caso nao esteja em execucao ainda
		if (!timeout) {
			// Timeout consiste no pressionar da tecla, com timeout de 1seg
			timeout = setTimeout(function() {
				// Verifica se a tecla, eh uma tecla operacional
				if (!util.keys['non-operational'][event.keyCode]) {
					callback(event, e);
				}

				// No final do timeout, faz o release do timeout da memoria
				clearTimeout(timeout);
				timeout = null;

				// callbackBlocking(callback);
			}, timeoutMs);
		}
	}).on(blockEvent, function() {
		// Ao executar o keydown, o elemento faz o reset da variavel timeout
		// bloqueando execucao continua (bubbling)
		clearTimeout(timeout);
		timeout = null;

		callbackBlocking(callback);
	});
}


function recount() {
	$('.input-count').val(function(k, v) {
		return parseInt(k) + 1;
	});
}


function addNewFieldTrans() {
	var block = $('.form-data .form-group:eq(0)');
	var cl = block.clone();


	cl.find('.row div:eq(0)').html($('<input />').attr({
		'class': 'form-control',
		'name': 'new_fields[key][]',
		'type': 'text'
	}));
	cl.find('.row div:eq(1)').html($('<input />').attr({
		'class': 'form-control',
		'name': 'new_fields[value][]',
		'type': 'text'
	}));

	cl.insertBefore(block);
}


function addDynamicField(obj) {
	var block = $(obj).clone();


	block.addClass('clone-block');
	var indice = $(obj).parent().find('.clone-block').length;

	block.find('td:eq(1)').html('<input type="text" name="new_fields[name]" class="form-control" />');
	window.bb = block;
	block.find(':input').attr('name', function() {
		var e2 = $(this);
		// console.log(e2.attr('name').match(/\[(.*)\]/));
		return 'new_fields[' + indice + ']' + (e2.attr('name').match(/\[(.*)\]/)[0].replace(/\[[0-9]\]/g, ''));
	});
	// console.info('hello')
	// console.log(block);

	$(obj).before(block);
}

function selectRequired(type) {
	$('[name*="\[' + type + '\]"]').attr('checked', false);

	$(':input[name*="\[required\]"]:checked').each(function() {
		var e = $(this);
		e
			.closest('tr')
			.find('[name*="\[' + type + '\]"]')
			.prop('checked', e.is(':checked'));
	});
}

function selectFormRequired() {
	selectRequired('form_add');
	return selectRequired('form_edit');
}

function selectGridRequired() {
	return selectRequired('grid');
}

function highlightRow(id) {
	$('.default-admin tr[data-rowid="' + id + '"]').css('backgroundColor', function() {
		return ($(this).css('backgroundColor') != 'rgb(240, 248, 255)') ? 'rgb(240, 248, 255)' : 'rgba(0, 0, 0, 0)';
	});
}


function selectValue(e) {
	// Busca os values do select
	if (!e.data('value')) {
		return;
	}
	var valuex = e.data('value').toString().split(',');

	// Filtra os options que correspondem com aqueles values informado e marca
	var cs = e.find('option').prop('selected', function() {
		return $.inArray($(this).val(), valuex) >= 0 ? true : false;
	});
}

$(function() {

	// Confirmacao antes de lancar um item
	$('.confirm-before-go').on('click', function(evt) {
		var e = $(this);
		var msg = e.data('msg') || 'Deseja executar a ação?';
		if (!confirm(msg)) {
			return false;
		}
	});


	// Checkbox enabler (manager)
	$(document).on('change', '.manager-checkbox', function() {
		var e = $(this);
		e.closest('tr').find('input[type="text"]').prop('disabled', !e.is(':checked'));
	});

	// Autohide success alert
	$('.default-admin .alert-success').each(function() {
		// highlightRow(id);
		$(this).delay(1500).slideUp(function() {
			$(this).remove();
			// highlightRow(id);
		});
	});

	// Select auto-select option
	$('select[data-value]').each(function() {
		selectValue($(this));
	});

	// $('[name="check_ws"]').click(function(){
	// 	var url = $('[name="wsurl"]').val();
	// 	console.log(url)
	// });

	// mark all inputs
	$('.check-all:input:checkbox').change(function() {
		var e = $(this);
		e.closest('.check-all-container').find(':checkbox').not(':disabled').not(e).prop('checked', e.is(':checked')).trigger('change');
	});

	// The Call
	// fireFluid('.admin-list .admin-list-search', function(event, element){


	// 	if(!window.loadall){
	// 		window.loadall = $.ajax({
	// 			url : 'http://localhost:8000/cidade/datagrid',
	// 			type: 'get',
	// 			dataType: 'jsonp',
	// 			data: {
	// 				'filter': element.val()
	// 			},
	// 			error : function(){},
	// 			success : function(d){
	// 				console.log(d.status)
	// 			}
	// 		});
	// 	}
	// }, null, null, null, function(callback){
	// 	if(window.loadall){
	// 		console.log('abort call');
	// 		window.loadall.abort();
	// 		window.loadall = null;
	// 	}
	// });

	// $('.admin-list .admin-list-search').keyup(function(){

	// });
});