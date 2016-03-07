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
	}
};

