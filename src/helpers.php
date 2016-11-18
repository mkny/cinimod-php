<?php

if (!function_exists('trataPath')) {
	/**
	 * Tratamento dos paths com o separador de diretorios
	 * @param  string $path Caminho fornecido
	 * @return string       Retorna a string fornecida
	 */
	function trataPath($path){
		// Pega o separador dinamico
		$ds = DIRECTORY_SEPARATOR;
		$regex = "/[\/\\\]/";

		return preg_replace($regex, $ds, $path);
	}
}

if (!function_exists('mdd')) {
	/**
	 * Funcao para debug simples, semelhante ao dd() do Laravel
	 * @param  mixed  $input   Variavel a ser exposta
	 * @param  boolean $indDump Verificador de dump ou print
	 * @return string           Variavel exposta
	 */
	function mdd($input, $indDump=false){
		echo '<pre>';
		if($indDump){
			var_dump($input);
		} else {
			print_r($input);
		}
		exit;
	}
}

if (!function_exists('mkny_path')) {
	function mkny_path(){
		$string = func_get_args();
		return trataPath(__DIR__.(isset($string[0])?implode('/',$string):null));
	}
}

// if (!function_exists('mkny_app_path')) {
// 	function mkny_app_path(){
// 		return __DIR__.'/App';
// 	}
// }

// if (!function_exists('mkny_app_ns')) {
// 	function mkny_app_ns(){
// 		return '';
// 	}
// }

if(!function_exists('mkny_app_basepath')){
	/**
	 * Funcao para retornar o caminho base
	 */
	function mkny_app_basepath($path=''){

		return trataPath(app_path().'/'.$path);
	}
}

if(!function_exists('mkny_models_path')){
	/**
	 * Funcao para retornar o caminho relativo de [Models]
	 */
	function mkny_models_path($c=false){
		return mkny_app_basepath('Models'.($c ? '/'.ucfirst($c):''));
	}
	
}
if(!function_exists('mkny_model_config_path')){
	/**
	 * Funcao para retornar o caminho relativo de [Modelconfig]
	 */
	function mkny_model_config_path($c=false){
		return mkny_app_basepath('Modelconfig'.($c ? '/'.ucfirst($c):''));
	}
	
}
if(!function_exists('mkny_presenters_path')){
	/**
	 * Funcao para retornar o caminho relativo de [Presenters]
	 */
	function mkny_presenters_path($c=false){
		return mkny_app_basepath('Presenters'.($c ? '/'.ucfirst($c):''));
	}
	
}
if(!function_exists('mkny_controllers_path')){
	/**
	 * Funcao para retornar o caminho relativo de [Controllers]
	 */
	function mkny_controllers_path($c=false){
		return mkny_app_basepath('Http/Controllers/Admin'.($c ? '/'.ucfirst($c):''));
	}
	
}
if(!function_exists('mkny_requests_path')){
	/**
	 * Funcao para retornar o caminho relativo de [Requests]
	 */
	function mkny_requests_path($c=false){
		return mkny_app_basepath('Http/Requests'.($c ? '/'.ucfirst($c):''));
	}
	
}
if(!function_exists('mkny_lang_path')){
	/**
	 * Funcao para retornar o caminho relativo de [lang]
	 */
	function mkny_lang_path($c=false){
		return mkny_app_basepath('../resources/lang'.($c ? '/'.($c):''));
	}
	
}

if (!function_exists('jsonp')) {
	/**
	 * Funcao para tratar o retorno em jsonp
	 * @param  array $data Dados para codificar
	 * @return string       String formatada com o callback
	 */
	function jsonp(array $data)
	{
		return \Request::input('callback').'('.json_encode($data).');';
	}
}