<?php

namespace Mkny\Cinimod\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

// use Illuminate\Filesystem\Filesystem;
use Mkny\Cinimod\Logic\ReportLogic;


// use GuzzleHttp;
// use Illuminate\Support\HtmlString;
// use Mkny;
use Mkny\Cinimod\Logic AS Logic;

// use App\Logic;

class DashboardController extends Controller
{
	public function anyIndex(ReportLogic $r)
	{
		// $r->

		return view('cinimod::admin.default_dashboard');
	}
}

// return view('cinimod.admin.default_dashboard');
/**
 * [title]Cinimod-gen[/title]
 * A complex generator
 * 
 * 
 * *** Features ***
 * 
 * » Webservices
 * Uma forma simples de trabalhar com webservices (SOAP12)
 * 
 * 	» Client (Mkny\Cinimod\Logic\WSClientLogic)
 *  	Ao chamar a classe a mesma cria um ambiente preparado para o 
 *   	consumo de servicos SOAP12
 *  » Server (Mkny\Cinimod\Logic\WSServerLogic)
 *  	Ao chamar esta classe, ela cria um servico automatizado para 
 *   	distribuicao de dados via SOAP12
 * 
 * » Report generator
 * Biblioteca de facilitacao de criacao de charts / reports
 * 	» mkny.report.js
 *  	Arquivo que comunica com um controlador gerador de charts
 * 
 * 
 * 
 */
