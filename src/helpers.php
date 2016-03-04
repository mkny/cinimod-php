<?php


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