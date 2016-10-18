<?php

namespace Mkny\Cinimod\Logic;

use Illuminate\Pagination\LengthAwarePaginator;
/**
* 
*/
class DatagridLogic
{
	
	function __construct()
	{
		
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

        // Monta todos os cabecalhos, inclusive os dinamicos
        $headers['checks'] = [\Form::checkbox('idx', '', '', ['class' => 'checkbox check-all'])];
        $headers = array_merge($headers,$field_names);
        $headers['actions'] = trans($modelName.'.actions');

        // Traducao dos headers, que vem com os nomes do banco de dados, 
        // aqui eles tomam nomes especificos dos arquivos de configuracao
        $headersTranslated = [];
        foreach ($headers as $field_key => $field_name) {

            // Se nao for numerico, indica um conteudo customizado
            if(is_numeric($field_key)){
                // Busca a traducao para o campo
                $headersTranslated[$field_name] = trans($modelName.'.'.$field_name.'.grid');

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

        // mdd($rows);
        foreach ($rows as $row) {


            // Monta as colunas
            $dataCols = [];

            foreach ($field_names as $field_name) {
                if(is_array($row)){
                    $dataCols[$field_name] = $row[$field_name];
                } else {
                    // Tratamento para relacionamentos (para exibir os items dentro da listagem)
                    if (isset($fields_config[$field_name]['relationship']) && $fields_config[$field_name]['relationship']) {

                        // Verifica se tem o metodo para exibir os campos adicionais
                        if($row->{$field_name.'s'}){

                            // Entra no metodo (Eloquent), retornando ele pega o nome do campo, definido no relationship
                            // Existe um projeto ai pra fazer esse campo ser dinamico kk
                            $dataCols[$field_name] = $row->{$field_name.'s'}->{$fields_config[$field_name]['relationship']['field_show']};
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

                // Adiciona os botoes
                $dataCols['actions'] = array();

                // Botao de edição
                $dataCols['actions'][] = $table->button(
                    action($modelName.'Controller@getEdit', [$dataCols[$pkey]]),
                    trans($modelName.'.button_edit'),
                    'glyphicon glyphicon-edit btn btn-sm btn-success');

                // Botão de alteração de status (personalizado então não é obrigatório)
                if (isset($dataCols['ind_status']) && !empty($dataCols['ind_status'])) {
                    $status = substr($dataCols['ind_status'],0,1);

                    $dataCols['actions'][] = $table->button(
                        action($modelName.'Controller@getSwitchStatus', [$dataCols[$pkey]]),
                        trans($modelName.'.'.($status == 'A' ? 'button_status_disable':'button_status_enable')),
                        'glyphicon btn btn-sm '.($status == 'A' ? 'glyphicon-ban-circle btn-warning':'glyphicon-ok btn-info'));
                }

                // Botão de exclusão
                $dataCols['actions'][] = $table->button(
                    action($modelName.'Controller@getDelete', [$dataCols[$pkey]]),
                    trans($modelName.'.button_delete'),
                    'glyphicon glyphicon-trash btn btn-sm btn-danger');
            } else {
                $dataCols['checks'] = array();
                $dataCols['actions'] = array();
            }
            $dataRows[] = $dataCols;

            // Setta a linha no array de linhas
            $table->insertRow($dataCols);
        }

        // return $dataRows;

        // mdd($dataRows);
        // dd($table->getTable());
        // Retorna a tabela montada
        return $table->getTable();
    }
}