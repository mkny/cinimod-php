
/**
 * Class Selection
 *
 * Aloca um elemento select, com pre-definicao de funcionalidades para o ajax
 * 
 * @param {object} Elemento principal
 */
 function Selection(element){
	// Armazena a referencia da variavel
	var that = this;

	var firer,
	oldvalues,
	element,
	isLoading=false;

	var isInit = false;


	// Elemento principal
	if (element) {
		this.setElement(element);
		this.init();
	}



	

}

Selection.prototype = {
	init: function(){
		var that = this;

		if(that.isInit){
			return false;
		}

		that.isInit = true;

		if(!that.firer){
			that.firer = $('#'+that.element.data('depends'));
		}

		that.firer.on('change', function(){
			var e = $(this);
			var load = that.opt('Loading ...');
			that.oldvalues = that.element.html();
			that.element.html(load);

			
			if(e.val() && !that.isLoading){
				that.isLoading = true;
				_mkny.backbone('/p/combo', {
					'method_name' : that.element.attr('name'),
					'filter' : that.firer.val() || that.firer.data('value')
				}, function(data){
					load.html('- Selecione -');
					for(i in data.data){
						var ob = data.data[i];
						that.opt(ob.name,ob.id).appendTo(that.element);
					}
					isLoading = false;
					selectValue(that.element);
					that.element.trigger('change');
				}, function(){
					isLoading = false;
				}, {
					type: 'get'
				});
			} else if(!that.isLoading){
				setTimeout(function(){
					that.element.html(that.oldvalues);
				},1000);
			}
		}).trigger('change');
	},

	opt : function(text,value, isSelected){
		isSelected = isSelected ? true:false;

		return $('<option />').html(text).val(value).prop('selected', isSelected);
	},

	setElement : function(e){
		this.element = e;
	}
};

$(function(){
	$('.mkny-select-depends').each(function(){
		(new Selection($(this)));
	});
});
