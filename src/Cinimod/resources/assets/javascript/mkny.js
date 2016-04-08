function Mkny(){
	if(!window.mkny){
		window.mkny = {};
	}
}

Mkny.prototype = {
	load : function(src, callback){
		// Armazena scripts ja carregados
		if(!window.mkny['scripts']){
			window.mkny['scripts'] = [];
		}

		// Verifica se o script ja foi carregado
		if ($.inArray(src,window.mkny.scripts) !== -1) {
			if (typeof callback == 'function') {
				callback();
			}
		} else {
			// Executa o carregamento
			console.info('Loading ('+src+')');
			window.mkny.scripts.push(src);
			$.getScript(src, function(){
				if (typeof callback == 'function') {
					callback();
				}
			});
		}
	},

	backbone : function(caller, jsonForm, callOk, callFail, options){
		if(!options) {
			options = {};
		}
		options['data'] = jsonForm;

		if(typeof callOk != 'function'){
			callOk = function(){};
		}

		if(typeof callFail != 'function'){
			callFail = function(){};
		}

		var _type = options['type'] || 'post';
		// var _async = options['async'] ? true:false;

		var ajx = $.ajax(caller, {
			data : options['data'],
			type : _type,
			// async : _async,
			dataType: 'json'
		});

		ajx
		.done(function( data, textStatus, jqXHR ) {
			if(textStatus == 'success' && data['status'] == 'success'){
				callOk(data);
			} else {
				callFail(data);
			}
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			callFail();
		});
		return ajx;


		// var def = $.Deferred(),
  //           promise = def.promise();


		// ajx.fail(def.resolve.apply(this, arguments));
		// ajx.fail(function(){
		// 	def.reject.apply(this, arguments);
		// });
	}
};

(function(){
	window._mkny = new Mkny();
})();