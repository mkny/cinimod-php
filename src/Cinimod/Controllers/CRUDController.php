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
    protected function _datasource($fields, $order, $card, $limit, $offset=false)
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

    protected function _datagrid(Array $fields, $Model=null, $datasourceAction=null, $recordOnly=false)
    {
        // Nao sei pq fiz isso, nao tem como sair por enquanto haha
        $field_names = array_keys($fields);

        // Ordenacao dos campos {
        
        // Busca no Model, o valor para orderBy (ou diz que e a primeira chave asc)
        $orderModel = ($Model ? $Model->orderBy:array('0', 'asc'));

        // Vindo de parametrizacao (url)
        $order = \Request::input('order',$orderModel[0]);

        // Se for numerico, traduz para o nome do campo propriamente dito
        if (is_numeric($order)) {
            $order = $field_names[$order];
        }

        // Trava anti bug (verifica se existe o campo que ta sendo passado)
        if(!in_array($order, $field_names)){
            $order = $orderModel[0];
        }
        // Ordenacao dos campos }

        // Quantidade por pagina {
        // Recupera a quantidade de campos
        // 
        // *Existe um campo no model eloquent que parece que faz isso automatico, verificar depois*
        $limit = \Request::input('perpage', ($Model ? $Model->maxPerPage:10)) ?:10;

        // Para nao deixar o sistema sobrecarregar, deixa o limit maximo em 1000 registros;
        $limit = ($limit > 1000) ? 1000:$limit;
        // Quantidade por pagina }
        
        // Pagina atual {
        // Complementando o Limit, vem o offset
        // A acao de datagrid principal ja trata isso automaticamente, mas quando e uma dependendte elas precisam dessa informacao pra cortar o array
        $page = \Request::input('page', 1);
        $offset = ($page * $limit) - $limit;
        // Pagina atual }

        // Cardinalidade {
        $card = \Request::input('card', $orderModel[1]);
        if(!in_array($card, array('asc', 'desc'))){
            $card = 'asc';
        }
        // Cardinalidade }
        

        // Escolhe o _datasource que sera utilizado
        $datasourceAction = $datasourceAction?$datasourceAction:'_datasource';

        // Faz a chamada do _datasource
        $_datasource = $this->{$datasourceAction}($field_names, $order, $card, $limit, $offset);

        // Se quiser buscar apenas os registros basicos
        if($recordOnly){
            return $_datasource->toArray();
        }

        $datagrid_config = Logic\UtilLogic::load(mkny_model_config_path($this->_getControllerName()).'.php')['grid'];
        // mdd($datagrid_config);
        $datagrid = app()->make('\Mkny\Cinimod\Logic\DatagridLogic', [$datagrid_config])->get(is_object($_datasource) ? $_datasource->items():$_datasource, $fields);

        return array(
            'configuration' => $datagrid_config,
            'table' => $datagrid,
            'info' => array(
                'total' => $_datasource->total(),
                'links' => $_datasource->links()
                )
            );
    }

    /**
     * Funcao para retornar os campos para montar a listagem
     * 
     * @return array
     */
    protected function _getFields()
    {

        return app()->make('\Mkny\Cinimod\Logic\UtilLogic')->_getConfig($this->_getControllerName(), 'datagrid');
        // return $this->model->_getConfig('datagrid');
    }

    protected function index()
    {
        // Pega as configuracoes de campos para a datagrid
        $modelconfig = $this->_getFields();

        // Monta o datagrid
        $grid = $this->_datagrid($modelconfig, $this->model, null );

        // Monta os dados para exibicao
        return view('cinimod::admin.default.list')->with($grid);
    }


    /**
    * Exibe formulario para criacao de um novo registro
    *
    * @return \Illuminate\Http\Response
    */
    protected function create()
    {
        $form_config = $this->model->_getFormConfig();

        $a = app()->make('\Mkny\Cinimod\Logic\FormLogic', [$form_config])->getForm(
            false,
            action($this->_getController().'@postAdd'),
            $this->model->_getConfig('form_add'),
            $this->_getControllerName()
            );

        // $a->setClass('form-horizontal col-md-12');
        // $a->setModel($this
        // ->model
        // ->findOrFail(56, $this->model->getFillable()));
        // $a;
        // mdd('hwg');

        // return view('cinimod::admin.default.add')->with(['form' => 'helloworld']);

        return view('cinimod::admin.default.add')->with(['form' => $a]);
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

        ;
        return view('cinimod::admin.default.edit')->with(['form' => app()->make('\Mkny\Cinimod\Logic\FormLogic',[$this->model->_getFormConfig()])->getForm($M,
            action($this->_getController().'@postEdit', [$id]),
            app()->make('\Mkny\Cinimod\Logic\UtilLogic')->_getConfig($this->_getControllerName(), 'form_edit'),
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
        // return redirect()->action($this->_getController()."@getIndex")
        // ->with(array(
        //     'status' => 'success',
        //     'message' => "Register ({$id}) Updated!"
        //     ));
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
