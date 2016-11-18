<?php

namespace Mkny\Cinimod\Logic;

use Illuminate\Pagination\LengthAwarePaginator;
/**
* 
*/
class DatagridLogic
{

  /**
  * Configuracao de exibicao do botao check
  * @var boolean
  */
  private $hasCheck = true;

  /**
  * Configuracao de exibicao da area de acoes
  * @var boolean
  */
  private $hasActions = true;

  /**
  * Configuracao de exibicao do botao editar
  * @var boolean
  */
  private $hasActionEdit = true;

  /**
  * Configuracao de exibicao do botao status_change
  * @var boolean
  */
  private $hasActionStatusChange = true;

  /**
  * Configuracao de exibicao do botao delete
  * @var boolean
  */
  private $hasActionDelete = true;

  private $buttons = [];

  /**
  * Construtor da classe de grid
  * @param Array|null $configuration Recebe os parametros de configuracao, o modelo pode ser encontrado nos ModelConfig de classes
  */
  function __construct(Array $configuration=null)
  {
  	if(!$configuration){
  		return;
  	}

  	if(isset($configuration['checks']) && !$configuration['checks']){
  		$this->noChecks();
  	}
  	if(isset($configuration['actions']) && !$configuration['actions']){
  		$this->noActions();
  	}

  	if(isset($configuration['actions_edit']) && !$configuration['actions_edit']){
  		$this->noActionEdit();
  	}
  	if(isset($configuration['actions_status_change']) && !$configuration['actions_status_change']){
  		$this->noActionStatusChange();
  	}
  	if(isset($configuration['actions_delete']) && !$configuration['actions_delete']){
  		$this->noActionDelete();
  	}


  	if(isset($configuration['buttons'])){
  		$this->buttons = $configuration['buttons'];
  	}


  }

  /**
  * Monta o datagrid baseado no array informado
  * @param  Array $dataset Dados do datagrid
  * @param  integer $limit   Valor de limite pro dataset
  * @param  integer $offset  Valor de offset / paginacao
  * @return \Illuminate\Pagination\LengthAwarePaginator          Dados pra paginacao
  */
  public function datagridFromArray($dataset, $limit, $offset)
  {
    // Instancia o objeto
  	return (new LengthAwarePaginator(
    // 'Fatia' o array
  		array_slice($dataset, $offset, $limit, true),
      // Informa a quantidade de registros
  		count($dataset),
      // Faz o limitador
  		$limit,
  		null,
      // Monta o caminho pra rota
  		array('path' => \Request::url())
      // Adiciona na rota, os campos para ter o controle da lista
  		))->appends(\Request::only(['order', 'card', 'perpage']));
  }

  /**
  * Informa que a classe nao ira gerar os checks
  * @return this
  */
  public function noChecks()
  {
  	$this->hasCheck = false;

  	return $this;
  }

  /**
  * Informa que a classe nao ira gerar a area de actions
  * @return [type] [description]
  */
  public function noActions()
  {
  	$this->hasActions = false;

  	return $this;
  }

  /**
  * Informa que a classe nao ira gerar o botao de edicao
  * @return [type] [description]
  */
  public function noActionEdit()
  {
  	$this->hasActionEdit = false;

  	return $this;
  }

  /**
  * Informa que a classe nao ira gerar o botao de alterar status
  * @return [type] [description]
  */
  public function noActionStatusChange()
  {
  	$this->hasActionStatusChange = false;

  	return $this;
  }

  /**
  * Informa que a classe nao ira gerar o botao de exclusao
  * @return [type] [description]
  */
  public function noActionDelete()
  {
  	$this->hasActionDelete = false;

  	return $this;
  }



  /**
  * Facilitador para montagem da datagrid
  * 
  * @param  array $rows      Linhas pra montar a datagrid
  * @param  array $fields_config Configuracao de campos
  * @param  string $pkey        Nome da primary key
  * @param  string $modelName   Nome do model
  * @return array              Tabela + paginator
  */
  public function get(Array $rows,$fields_config, $pkey=false)
  {
  	$modelName = view()->shared('controller');

    // Instancia a tabela
  	$table = new TableLogic();

    // Trata os fields, apenas para pegar os nomes
  	$field_names = array_keys($fields_config);



    // Adiciona as classes na tabela
  	$table->addTableClass(['table','table-striped','check-all-container']);

  	$headers = array();
    // Monta todos os cabecalhos, inclusive os dinamicos
  	if($this->hasCheck){
  		$headers['checks'] = [\Form::checkbox('idx', '', '', ['class' => 'checkbox check-all'])];
  	}

  	$headers = array_merge($headers,$field_names);

  	if($this->hasActions){
  		$headers['actions'] = trans($modelName.'.actions');
  	}

    // Traducao dos headers, que vem com os nomes do banco de dados, 
    // aqui eles tomam nomes especificos dos arquivos de configuracao
  	$headersTranslated = [];
  	foreach ($headers as $field_key => $field_name) {

    // Se nao for numerico, indica um conteudo customizado
  		if(is_numeric($field_key)){
    // Busca a traducao para o campo
  			$headersTranslated[$field_name] = isset($fields_config[$field_name]['trans']) ? $fields_config[$field_name]['trans']:trans($modelName.'.'.$field_name.'.grid');


  			if(isset($fields_config[$field_name]['type']) && $fields_config[$field_name]['type'] == 'primaryKey'){
  				$pkey = $fields_config[$field_name]['name'];
  			}
    // Aqui faz o tratamento do link no titulo,
    // Usado para campos de busca, ordenacao, etc
  			if(isset($fields_config[$field_name]['searchable']) && $fields_config[$field_name]['searchable']){


    // Insere o link nos items marcados como searchable (o link e de ordenacao)
  				$headersTranslated[$field_name] = [
    // Link com o campo order e o campo cardinalidade
  				\Html::tag(
  					'a',
  					$headersTranslated[$field_name],
  					['href' =>
  					"?order={$field_name}&card=".(\Request::input('order') == $field_name && \Request::input('card', 'desc') == 'asc' ? 'desc':'asc').
  					"&".http_build_query(\Request::except(['order', 'card', 'page']))]
  					)
  				];
  			}
  		} else {
    // Conteudo customizado
  			$headersTranslated[$field_key] = $field_name;
  		}
  	}

    // Setta os headers
  	$table->setHeaders($headersTranslated);

    // Monta as linhas
    // Fiz essa alteracao por causa do presenter()
  	$dataRows = [];

  	foreach ($rows as $row) {

    // Monta as colunas
  		$dataCols = [];

    // Faz o tratamento campo-a-campo
  		foreach ($field_names as $field_name) {

    // Se for um array simples, tratar como informacoes solidas
  			if(is_array($row)){

    // Verifica se esta sendo passado um retorno-array
  				if(is_array($row[$field_name])){

        // Vericica a presenca do indice formatador
  					if (isset($fields_config[$field_name]['format'])) {

            // Faz o vsprintf no campo, caso esteja sendo passado um array para a formacao do campo
  						$dataCols[$field_name] = vsprintf($fields_config[$field_name]['format'], $row[$field_name]);
  					} else {
            // Variavel que guarda os valores, para informar a serem formatados
  						$data_help = json_encode($row[$field_name]);

            // Variavel que guarda a sugestao de formatacao pro elemento
  						$data_help_f = ['name' => $field_name, 'format' => str_repeat('%s ', count($row[$field_name]))];

            // Joga uma exception pro programador identificar o que pode ser feito no elemento
  						throw new \Exception("This field need format!\nAdd this to your config call: \"".var_export($data_help_f, true)."\"\nFractal data: \"".$data_help."\"");
  					}
  				} else {
        // Tenta identificar um valor multiplo definido no form-values para a traducao
  					$translation_string = "$modelName.{$field_name}.form_values.{$row[$field_name]}";
  					$translation_intent = trans($translation_string);
  					if($translation_string <> $translation_intent){
  						$row[$field_name] = $translation_intent;
  					}

        // Atribui o valor a coluna
  					$dataCols[$field_name] = $row[$field_name];
  				}

  			} else {
    // Tratamento para relacionamentos (para exibir os items dentro da listagem)
  				if (isset($fields_config[$field_name]['relationship']) && $fields_config[$field_name]['relationship']) {
    // Define a relacao
  					$relation = $fields_config[$field_name]['relationship'];

    // Verifica se tem o metodo para exibir os campos adicionais
  					if($row->{$field_name.'s'}){
    // Entra no metodo (Eloquent), retornando ele pega o nome do campo, definido no relationship
    // Existe um projeto ai pra fazer esse campo ser dinamico kk
    // 

    // Possivel acao do controlador (definido no mconfig)
  						$controller_edit_action = (isset($relation['controller']) ? $relation['controller']:'Dummy').'@getEdit';
    // Campo de edicao
  						$field_key = $row->{$field_name.'s'}->{$relation['field_key']};

    // Verifica a existencia de rota-relacionada (click to edit)
  						if(\Route::getRoutes()->getByAction($controller_edit_action)){

    // Link para a edicao do relacionado
  							$dataCols[$field_name] = \Html::tag(
  								'a',
  								$row->{$field_name.'s'}->{$relation['field_show']},
  								['href' => action('Admin\\'.class_basename($controller_edit_action), [$field_key])]
  								);
  						} else {
  							$dataCols[$field_name] = $row->{$field_name.'s'}->{$relation['field_show']};
  						}

  					} else {

            // Se nao existe, bota um zerado ae
  						$dataCols[$field_name] = '';
  					}
  				} else {
        // Setta a coluna com o presenter no array de colunas
  					$dataCols[$field_name] = $row->present()->{$field_name};
        // $dataCols[$field_name] = $row[$field_name];
  				}
  			}
  		}

  		if($pkey){


        // Adiciona o check individual
  			$dataCols['checks'] = array(
  				\Form::checkbox('id_sec[]', $row[$pkey], '', ['class'=> 'checkbox'])
  				);

  			if($this->hasActions){


          // Adiciona os botoes
  				$dataCols['actions'] = array();

          // Botao de edição
  				if($this->hasActionEdit){
  					$dataCols['actions'][] = $table->button(array(
  						'link' => action('Admin\\'.$modelName.'Controller@getEdit', [$dataCols[$pkey]]),
  						'attributes' => [
  						'class' => 'glyphicon glyphicon-edit btn btn-sm btn-success',
  						'title' => trans($modelName.'.button_edit')
  						]
  						));
  				}

          // Botão de alteração de status (personalizado então não é obrigatório)
  				if($this->hasActionStatusChange){
  					if (isset($dataCols['ind_status']) && !empty($dataCols['ind_status'])) {
  						$status = substr($dataCols['ind_status'],0,1);

  						$dataCols['actions'][] = $table->button(array(
  							'link' => action('Admin\\'.$modelName.'Controller@getSwitchStatus', [$dataCols[$pkey]]),
  							'attributes' => [
  							'title' => trans($modelName.'.'.($status == 'A' ? 'button_status_disable':'button_status_enable')),
  							'class' => 'confirm-before-go glyphicon btn btn-sm '.($status == 'A' ? 'glyphicon-ban-circle btn-warning':'glyphicon-ok btn-info')
  							]
  							));
  					}
  				}

          // Botão de exclusão
  				if($this->hasActionDelete){
  					$dataCols['actions'][] = $table->button(array(
  						'link' => action('Admin\\'.$modelName.'Controller@getDelete', [$dataCols[$pkey]]),
  						'attributes' => [
  						'title' => trans($modelName.'.button_delete'),
  						'class' => 'glyphicon glyphicon-trash btn btn-sm btn-danger confirm-before-go'
  						]
  						));
  				}

    // ## Botoes customizados ##
  				if (isset($this->buttons) && !empty($this->buttons)) {
  					$btns = $this->buttons;
  					foreach ($btns as $btn) {
  						$dataCols['actions'][] = sprintf($table->button($btn), $dataCols[$pkey]);
  					}
  				}
  			}


  		} else {
  			$dataCols['checks'] = array();
  			$dataCols['actions'] = array();
  		}
  		$dataRows[] = $dataCols;

    // Setta a linha no array de linhas
  		$table->insertRow($dataCols);
  	}

    // Retorna a tabela montada
  	return $table->getTable();
  }
}