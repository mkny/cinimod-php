<?php

namespace Mkny\Cinimod\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mkny\Cinimod\Logic;

// class_basename will halp

abstract class CRUDController extends Controller
{
    /**
     * Controller default model
     * @var object
     */
    protected $model;

    /**
     * CRUDLogic Related
     * @var object
     */
    private $logic;

    /**
     * CRUD Logic
     * @var object
     */
    private $CL;
    /**
     * Class constructor
     * 
     * @param Logic\CRUDLogic $logic
     */
    public function __construct(){
        Logic\UtilLogic::addViewVar('scripts', ['/js/cinimod.js']);

    }

    protected function index()
    {
        $data = $this->datagrid();
        return view('cinimod.admin.default.list')->with('data',$data);
    }


    /**
     * Datagrid action
     */
    protected function datagrid()
    {
        // skip (OFFSET HAHA)
        // Pega as configuracoes de campos para a datagrid
        $config = $this->model->_getConfig('datagrid');

        
        // Ordena os campos
        $order = $this->model->primaryKey;
        $orderParam = \Request::input('order');
        if ($orderParam) {
            $order = array_keys($config)[$orderParam];
        }

        // Quantidade por pagina
        $limit = \Request::input('perpage', 10);

        $rows = $this
        ->model
        ->orderBy($order, \Request::input('card', 'asc'))
        ->paginate(
            // Qtd de campos
            $limit,
            // Pega os fieldnames para o select
            array_keys($config)
            )->appends(\Request::only(['order', 'card', 'perpage']));
        
        // Titulo da pagina
        $data['title'] = $this->_getControllerName()." List ";
        $data['fields'] = $config;
        $data['card'] = (\Request::input('card', 'asc') == 'asc') ? 'desc':'asc';
        // Resultados
        $data['grid'] = $rows;

        // Nome do controlador
        $data['controller'] = $this->_getControllerName();
        // dd($data);
        // Configuracao do link de exibicao da paginacao
        // $data['grid']->setPath('index/'.http_build_query($_GET));

        return $data;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected function create()
    {

        $fields = $this->model->_getConfig('form');
        
        $data['action'] = '';
        $data['controller'] = $this->_getControllerName();
        $data['data'] = $fields;

        return view('cinimod.admin.default.add')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function store(Request $request)
    {
        // Varre o array procurando valores vazios, para settar como nulos
        $post = array_map(function($dataPost){
            return ($dataPost == '') ?null:$dataPost;
        }, $request->only(array_keys($this->model->_getConfig('form'))));

        // Novo filtro no array postado, pq quando grava, nao importa valores nulos!
        // Esta regra não se aplica para update!
        $post = array_filter($post);

        // Pega o id inserido no momento
        $id = $this->model->create($post)->{$this->model->primaryKey};
        
        // Redireciona para a index, com uma mensagem de inserção
        return redirect()->action($this->_getController()."@getIndex")->with(array(
            'status' => 'success',
            'message' => "Register ({$id}) Inserted!"
            ));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    protected function show($id)
    {
        $this->model->find($id)->toArray();
        return view('cinimod.admin.default.show');
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    protected function edit($id)
    {
        $fields = $this->model->_getConfig('form');

        

        $data_values = $this
        ->model
        ->find($id, $this->model->getFillable())
        ->toArray();

        foreach ($fields as $key => $field) {
            $fields[$key]['default_value'] = $data_values[$field['name']];
        }
        
        $data['action'] = '';
        $data['controller'] = $this->_getControllerName();
        $data['data'] = $fields;

        

        // dd($data);
        
        return view('cinimod.admin.default.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    protected function update(Request $request, $id)
    {
        $post = array_map(function($dataPost){
            return ($dataPost == '') ?null:$dataPost;
        }, $request->all());

        // echo '<pre>';print_r($post);exit;

        $this->model->find($id)->update($post);
        return
        redirect()->action($this->_getController()."@getIndex")
        // back()
        ->with(array(
            'status' => 'success',
            'message' => "Register ({$id}) Updated!"
            ));
        // } else {
            // return redirect()->action($this->_getController()."@getEdit", ['id' => $id])->with(array(
            //     'status' => 'error',
            //     'message' => 'xabanaias'
            //     ));;
        // }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    protected function destroy($id)
    {
        $this->model->find($id)->delete();
        return redirect()->action($this->_getController()."@getIndex")->with(array(
            'status' => 'danger',
            'message' => "Register ({$id}) Deleted!"
            ));
    }

    protected function statusChange($id){
        $M = $this->model->find($id);
        $M->ind_status = ($M->ind_status == 'A')?'B':'A';
        $M->save();
        return back()
        ->with(array(
            'status' => 'success',
            'message' => 'Alter with Success!'
            ));
    }

    public function getDashboard(Logic\ReportLogic $r)
    {
        // Lists, Graphs, all
        // $data = $this->datagrid();
        $data['reports'] = isset($this->reports) && $this->reports?$this->reports:array();
        // \App\Logic\UtilLogic::addViewVar('data', $data);

        return view('cinimod.admin.default.dashboard')->with($data);
    }
}
