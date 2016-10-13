$(function(){

	// Checkbox enabler (manager)
	$(document).on('change','.manager-checkbox', function(){
		var e = $(this);
		e.closest('tr').find('input[type="text"]').prop('disabled', !e.is(':checked'));
	});

	// Autohide success alert
	$('.default-admin .alert-success').each(function(){
		// highlightRow(id);
		$(this).delay(1500).slideUp(function(){
			$(this).remove();
			// highlightRow(id);
		});
	});

	// Select auto-select option
	$('select[data-value]').each(function(){
		selectValue($(this));
	});

	// $('[name="check_ws"]').click(function(){
	// 	var url = $('[name="wsurl"]').val();
	// 	console.log(url)
	// });
	
	// mark all inputs
	$('.check-all:input:checkbox').change(function(){
		var e = $(this);
		e.closest('.check-all-container').find(':checkbox').not(':disabled').not(e).prop('checked',e.is(':checked')).trigger('change');
	});
	
});
function recount(){
	$('.input-count').val(function(k,v){
		return parseInt(k)+1;
	});
}


function addNewFieldTrans() {
	var block = $('.form-data .form-group:eq(0)');
	var cl = block.clone();

	
	cl.find('div:eq(0)').html($('<input />').attr({
		'class' : 'form-control',
		'name' : 'new_fields[key][]',
		'type' : 'text'
	}));
	cl.find('div:eq(1)').html($('<input />').attr({
		'class' : 'form-control',
		'name' : 'new_fields[value][]',
		'type' : 'text'
	}));

	cl.insertBefore(block);
}


function addDynamicField(obj){
	var block = $(obj).clone();


	block.addClass('clone-block');
	var indice = $(obj).parent().find('.clone-block').length;

	block.find('td:eq(1)').html('<input type="text" name="new_fields[name]" class="form-control" />');
	window.bb = block;
	block.find(':input').attr('name', function(){
		var e2 = $(this);
		// console.log(e2.attr('name').match(/\[(.*)\]/));
		return 'new_fields['+indice+']'+(e2.attr('name').match(/\[(.*)\]/)[0].replace(/\[[0-9]\]/g, ''));
	});
	// console.info('hello')
	// console.log(block);

	$(obj).before(block);
}

function selectRequired(type){
	$('[name*="\['+type+'\]"]').attr('checked', false);

	$(':input[name*="\[required\]"]:checked').each(function(){
		var e = $(this);
		e
		.closest('tr')
		.find('[name*="\['+type+'\]"]')
		.prop('checked', e.is(':checked'));
	});
}
function selectFormRequired(){
	selectRequired('form_add');
	return selectRequired('form_edit');
}
function selectGridRequired(){
	return selectRequired('grid');
}

function highlightRow(id){
	$('.default-admin tr[data-rowid="'+id+'"]').css('backgroundColor', function(){
		return ($(this).css('backgroundColor') != 'rgb(240, 248, 255)')?'rgb(240, 248, 255)':'rgba(0, 0, 0, 0)';
	});
}


function selectValue(e){
	// Busca os values do select
	if(!e.data('value')){
		return;
	}
	var valuex = e.data('value').toString().split(',');

	// Filtra os options que correspondem com aqueles values informado e marca
	var cs = e.find('option').prop('selected', function(){
		return $.inArray($(this).val(), valuex) >= 0 ? true:false;
	});
}