<?php

if (!function_exists('trataPath')) {
	function trataPath($path){
		$ds = DIRECTORY_SEPARATOR;
		$regex = "/[\/\\\]/";

		return preg_replace($regex, $ds, $path);
	}
}

if (!function_exists('mdd')) {
	function mdd($input){
		echo '<pre>';
		print_r($input);
		exit;
	}
}

if (!function_exists('mkny_path')) {
	function mkny_path(){
		$string = func_get_args();


		return trataPath(__DIR__.(isset($string[0])?$string[0]:null));
	}
}

if (!function_exists('mkny_app_path')) {
	function mkny_app_path(){
		return __DIR__.'/App';
	}
}

if (!function_exists('mkny_app_ns')) {
	function mkny_app_ns(){
		return '';
	}
}

if(!function_exists('mkny_app_basepath')){
	/**
	 * Funcao para retornar o caminho base
	 */
	function mkny_app_basepath(){
		return base_path().'/app/';
	}
}

if(!function_exists('mkny_models_path')){
	/**
	 * Funcao para retornar o caminho relativo de [Models]
	 */
	function mkny_models_path($c=false){
		return mkny_app_basepath().'Models/'.($c ? ucfirst($c):'');
	}
	
}
if(!function_exists('mkny_model_config_path')){
	/**
	 * Funcao para retornar o caminho relativo de [Modelconfig]
	 */
	function mkny_model_config_path($c=false){
		return mkny_app_basepath().'Modelconfig/'.($c ? ucfirst($c):'');
	}
	
}
if(!function_exists('mkny_presenters_path')){
	/**
	 * Funcao para retornar o caminho relativo de [Presenters]
	 */
	function mkny_presenters_path($c=false){
		return mkny_app_basepath().'Presenters/'.($c ? ucfirst($c):'');
	}
	
}
if(!function_exists('mkny_controllers_path')){
	/**
	 * Funcao para retornar o caminho relativo de [Controllers]
	 */
	function mkny_controllers_path($c=false){
		return mkny_app_basepath().'Http/Controllers/'.($c ? ucfirst($c):'');
	}
	
}
if(!function_exists('mkny_requests_path')){
	/**
	 * Funcao para retornar o caminho relativo de [Requests]
	 */
	function mkny_requests_path($c=false){
		return mkny_app_basepath().'Http/Requests/'.($c ? ucfirst($c):'');
	}
	
}
if(!function_exists('mkny_lang_path')){
	/**
	 * Funcao para retornar o caminho relativo de [lang]
	 */
	function mkny_lang_path($c=false){
		return mkny_app_basepath().'../resources/lang/'.($c ? ($c):'');
	}
	
}