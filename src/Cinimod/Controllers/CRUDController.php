<?php

namespace Mkny\Cinimod\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mkny\Cinimod\Logic;

use DB;


// use Illuminate\Pagination\LengthAwarePaginator;


abstract class CRUDController extends Controller
{
    /**
    * Controller default model
    * @var object
    */
    protected $model;

    /**
    * CRUDLogic
    * 
    * @var object
    */
    private $CL;

    /**
    * Construtor da classe
    * @return void
    */
    public function __construct()
    {
        // $this->CL = new Logic\CRUDLogic;

        Logic\UtilLogic::addViewVar('controller', $this->_getControllerName());
    }



    /**
    * Datasource de dados para a listagem
    * @param  array $fields Campos para serem buscados
    * @param  int $limit  Valor de limite
    * @param  int|string $order  Campo de ordenacao
    * @param  string $card   Cardinalidade do campo de ordenacao
    * @return object
    */
    public function _datasource($fields, $limit, $order, $card)
    {
        $rows = $this
        ->model
        ->orderBy($order, $card)
        ->paginate(
            // Qtd de linhas
            $limit,
            // Pega os fieldnames para o select
            $fields
            )
        ->appends(\Request::only(['order', 'card', 'perpage']));

        return $rows;
    }

    protected function datagrid()
    {
        
    }

    /**
     * Funcao para retornar os campos para montar a listagem
     * 
     * @return array
     */
    public function _getFields()
    {
        return $this->model->_getConfig('datagrid');
    }

    protected function index()
    {

        // Recupera o nome do controlador principal
        $controller = $this->_getControllerName();

        // Pega as configuracoes de campos para a datagrid
        $modelconfig = $this->_getFields();

        // Separa apenas o nome dos campos
        $config_fields = array_keys($modelconfig);



        // Ordena os campos
        // Default busca no model, o valor para orderBy
        $orderModel = ($this->model ? $this->model->orderBy:array('0', 'asc'));
        // Vindo de parametrizacao
        $order = \Request::input('order',$orderModel[0]);
        // Se for numerico, indica que veio da parametrizacao de url (ou precisa do tratamento numerico)
        if (is_numeric($order)) {
            $order = $config_fields[$order];
        }
        // Aqui protege de bugar o sistema
        if(!in_array($order, $config_fields)){
            $order = $orderModel[0];
        }

        // Cardinalidade
        $card = \Request::input('card', $orderModel[1]);
        if(!in_array($card, array('asc', 'desc'))){
            $card = 'asc';
        }

        // Quantidade por pagina
        $limit = \Request::input('perpage', ($this->model ? $this->model->maxPerPage:10));

        // Para nao deixar o sistema sobrecarregar, deixa o limit maximo em 1000 registros;
        $limit = ($limit > 1000) ? 1000:$limit;



        // Pega os dados do _datasource
        $rows = $this->_datasource($config_fields, $limit, $order, $card);

        // Monta o datagrid
        $datagrid = app()->make('\Mkny\Cinimod\Logic\CRUDLogic')->datagrid(is_object($rows) ? $rows->items():$rows, $modelconfig, ($this->model ? $this->model->primaryKey:null ), $controller);

        // Monta os dados para exibicao
        return view('cinimod::admin.default.list')->with(array(
            'table' => $datagrid,
            'grid' => array(
                'total' => $rows->total(),
                'links' => $rows->links()
                )
            ));
    }


    /**
    * Exibe formulario para criacao de um novo registro
    *
    * @return \Illuminate\Http\Response
    */
    protected function create()
    {

        return view('cinimod::admin.default.add')->with(['form' => app()->make('\Mkny\Cinimod\Logic\FormLogic')->getForm(
            false,
            action($this->_getController().'@postAdd'),
            $this->model->_getConfig('form_add'),
            $this->_getControllerName()
            )]);
    }

    /**
    * Exibe formulario para edicao de um registro
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    protected function edit($id)
    {
        $M = $this
        ->model
        ->findOrFail($id, $this->model->getFillable());

        $a = new \Mkny\Cinimod\Logic\FormLogic();
        return view('cinimod::admin.default.edit')->with(['form' => $a->getForm($M,
            action($this->_getController().'@postEdit', [$id]),
            $this->model->_getConfig('form_edit'),
            $this->_getControllerName())]);
    }

    // CRUD
    /**
    * Armazena um novo registro no database
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    protected function store(Request $request)
    {
        // Varre o array procurando valores vazios, para settar como nulos
        $post = array_map(function($dataPost){
            return ($dataPost == '') ?null:$dataPost;
        }, $request->only(array_keys($this->model->_getConfig('form_add'))));

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

    // CRUD
    /**
    * Atualiza o registro na base de dados
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

        $M = $this->model->findOrFail($id);
        $M->update($post);
        return redirect()->action($this->_getController()."@getIndex")
        ->with(array(
            'status' => 'success',
            'message' => "Register ({$id}) Updated!"
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
        $this->model->findOrFail($id)->toArray();
        return view('cinimod::admin.default.show');
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    protected function destroy($id)
    {
        $M = $this
        ->model
        ->findOrFail($id)
        ->delete();

        return back()->with(array(
            'status' => 'danger',
            'message' => "Register ({$id}) Deleted!"
            ));
    }

    /**
    * Atualiza o status (ativo/bloqueado) do item
    * 
    * @param int $id Id a ser atualizado
    * @return void
    */
    protected function statusChange($id)
    {
        $M = $this->model->findOrFail($id);
        $M->ind_status = ($M->ind_status == 'A')?'B':'A';
        $M->save();
        return back()->with(array(
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

        return view('cinimod::admin.default.dashboard')->with($data);
    }

    /**
    * Retorna o nome deste controller para o CRUD
    * @return string Nome da classe controlador
    */
    protected function _getController(){
        return class_basename($this);
    }

    /**
    * Retorna o nome deste controller para o CRUD
    * @return string Nome da classe controlador
    */
    protected function _getControllerName(){
        return str_replace('Controller', '', $this->_getController());
    }

    public function getCombo()
    {



        $fields = $this->model->_getConfig('form_add');
        $field_request = \Request::input('method_name');

        $data = $this->model->relation($fields[Logic\UtilLogic::array_finder($fields, $field_request)]['relationship'], \Request::input('filter'));


        return [
        'status' => 'success',
        'data' => $data
        ];
    }
}
