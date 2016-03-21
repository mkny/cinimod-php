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
		})
	});

	// Select auto-select option
	$('select[data-value]').each(function(){

		selectValue($(this));
	});

	$('.check-all:input:checkbox').change(function(){
		var e = $(this);
		e.closest('.check-all-container').find(':checkbox').not(':disabled').not(e).prop('checked',e.is(':checked')).trigger('change');
	});
	
});
function recount(){
	$('.input-count').val(function(k,v){
		return k;
	});
}

function addDynamicField(obj){
	var block = $(obj).clone();


	block.addClass('clone-block');
	var indice = $(obj).parent().find('.clone-block').length;

	block.find('td:eq(1)').html('<input type="text" name="new_fields[name]" class="form-control" />')
	block.find('td').each(function(){
		var e = $(this);
		e.find(':input').attr('name', function(){
			var e2 = $(this);


			return 'new_fields['+indice+']'+e2.attr('name').match(/\[(.*)\]/)[0];


		});
	});

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
	return selectRequired('form');
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