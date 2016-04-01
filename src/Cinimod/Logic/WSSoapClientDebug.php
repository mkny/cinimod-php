<?php

namespace Mkny\Cinimod\Logic;


/**
* Simples classe para debugar o request do objeto (em casos extremos)
* 
*/
class WSSoapClientDebug extends \SoapClient
{

	/**
	 * 
	 * Reescricao do metodo __doRequest do SoapClient
	 * 
	 */
	function __doRequest($request, $location, $action, $version, $one_way = 0) {

		// Add code to inspect/dissect/debug/adjust the XML given in $request here
		echo '<pre>';
		print_r($request);
		var_dump($location, $action, $version,$one_way);
		exit;
    	// Uncomment the following line, if you actually want to do the request
		$a = parent::__doRequest($request, $location, $action, $version, $one_way);
 	    // dd($a);
		return $a;
	}
}