<?php

namespace Mkny\Cinimod\Logic;

// WS Type (soap/rest)

/**
* 
*/
class SoapClientDebug extends \SoapClient
{
	
	function __doRequest($request, $location, $action, $version, $one_way = 0) {

      // Add code to inspect/dissect/debug/adjust the XML given in $request here
		// echo '<pre>';
		// print_r($request);
		// var_dump($location, $action, $version,$one_way);
		// exit;
      // Uncomment the following line, if you actually want to do the request
      return parent::__doRequest($request, $location, $action, $version, $one_way);
  }
}