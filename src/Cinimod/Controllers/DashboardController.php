<?php

namespace Mkny\Cinimod\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

// use Illuminate\Filesystem\Filesystem;
// use App\Logic\ReportLogic;
use Mkny\Cinimod\Logic\WSClientLogic;
// use Mkny\Cinimod\Logic\WSServerLogic;
// use GuzzleHttp;
// use Illuminate\Support\HtmlString;
// use Mkny;
use Mkny\Cinimod\Logic AS Logic;

// use App\Logic;

class DashboardController extends Controller
{
	public function getIndex(WSClientLogic $ws)
	{
		return 'alldone';
		
		$ws->init('http://projetos.net4bizidc.com.br/linxhandover/public/site/ws?wsdl',array(
			'cache_wsdl' => WSDL_CACHE_NONE
			));
		$cnpj = $ws->GetCnpj('33569');
		$keys = $ws->GetProjetos($cnpj['cnpj']);
		$projeto = $ws->VerProjeto($keys[0]['idprojeto'],$keys[0]['chave']);
		
		echo '<pre>';
		print_r($projeto);
		exit;
	}
}

// return view('cinimod.admin.default_dashboard');