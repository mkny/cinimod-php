<?php

namespace Mkny\Cinimod\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

// use Illuminate\Filesystem\Filesystem;
// use App\Logic\ReportLogic;
// use App\Logic\WSLogic;
// use GuzzleHttp;
// use Illuminate\Support\HtmlString;
// use Mkny;
use Mkny\Cinimod\Logic AS Logic;

// use App\Logic;

class DashboardController extends Controller
{
	public function getIndex()
	{

		// echo '<pre>';
		// print_r($r);
		// exit('_var');
		// echo 'hallau';
		// exit;


		return view('admin.default_dashboard');
	}
}
