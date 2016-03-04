<?php

namespace Mkny\Cinimod\Logic;

/**
* 
*/
class MknyLogic
{
	
	function __construct()
	{
		UtilLogic::addViewVar('scripts', ['/js/app.js']);
	}
}