<?php

namespace Mkny\Cinimod\Logic;

use DB;

class CRUDLogic {

    /**
     * Facilitador para montagem da datagrid
     * 
     * @param  array $rows      Linhas pra montar a datagrid
     * @param  array $fields_config Configuracao de campos
     * @param  string $pkey        Nome da primary key
     * @param  string $modelName   Nome do model
     * @return array              Tabela + paginator
     */
    public function datagrid(Array $rows,$fields_config, $pkey, $modelName)
    {

        // Instancia a tabela
        $table = new TableLogic();

        // Trata os fields, apenas para pegar os nomes
        $field_names = array_keys($fields_config);



        // Adiciona as classes na tabela
        $table->addTableClass(['table','table-striped','check-all-container']);

        // Monta todos os cabecalhos, inclusive os dinamicos
        $headers = array_merge(
            array('checks' => [\Form::checkbox('idx', '', '', ['class' => 'checkbox check-all'])]),
            $field_names,
            array('actions' => trans($modelName.'.actions'))
            );
        // mdd($headers);


        // Traducao dos headers, que vem com os nomes do banco de dados, 
        // aqui eles tomam nomes especificos dos arquivos de configuracao
        $headersTranslated = [];
        foreach ($headers as $field_key => $field_name) {

            // Se nao for numerico, indica um conteudo customizado
            if(is_numeric($field_key)){
                // Busca a traducao para o campo
                $headersTranslated[$field_name] = trans($modelName.'.'.$field_name.'.grid');
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

    public function getForm($Model = false, $Action, $FormFields, $ControllerName)
    {
        exit('use new logic file');

        if($Model){
            // Abre o form com os campos preenchidos 
            $data[] = \Form::model($Model, array('url' => $Action, 'class' => 'form-horizontal col-md-12'));
        } else {
            // Abre o form padrao
            $data[] = \Form::open(array('url' => $Action, 'class' => 'form-horizontal col-md-12'));
        }

        foreach ($FormFields as $field_config) {
            // Form variavel name
            $form_data_name = $ControllerName.'.'.$field_config['name'].'.form';

            // Traducao do campo
            
            $field_trans = isset($field_config['trans'])? $field_config['trans']:trans($form_data_name);

            // Monta o label, adicionando um "*" se o campo for requerido
            $label = \Form::label(
                $field_config['name'],
                $field_trans.((isset($field_config['required']) && $field_config['required']) ? '*':'')
                );

            // Verifica os tipos de campos fornecidos
            // if(isset($field_config['type']))
            switch($field_config['type']){
                case 'select':
                // Helper classname
                
                $options = [];
                $options['class'] = ['form-control'];

                // No select, adiciona o primeiro indice um campo vazio, solicitando alteracao
                $arrDataSelect = ['' => '- Selecione -'];

                // Se for um relacionamento
                if(isset($field_config['relationship']) && $field_config['relationship']){
                    // Quando tem dependencia na tabela
                    // 
                    if(isset($field_config['relationship']['dependsOn']) && $field_config['relationship']['dependsOn']){
                        // class para funcoes ajax
                        $options['class'][] = 'mkny-select-depends';

                        // data-value depends
                        $options['data-depends'] = $field_config['relationship']['dependsOn'];

                        $dataModel = [];
                        // $dataModel = $field_config['relationship']['model']::relation($field_config['relationship'],($Model?$Model->{$field_config['relationship']['dependsOn']}:''));
                    } else {
                        // dd($field_config);exit;
                        $dataModel = $field_config['relationship']['model']::relation($field_config['relationship']);
                    }


                    // if($field_config['relationship']['model'] == '\App\Models\Estado'){
                        // $field_config['relationship']['dependsOn'] = $Model->{$field_config['relationship']['dependsOn']};
                        // print_r($field_config['relationship']);exit;
                    // }

                    foreach ($dataModel as $dm){
                        $arrDataSelect[$dm['id']] = $dm['name'];
                    }
                } elseif(isset($field_config['values']) && $field_config['values']) {
                    // Se houverem valores definidos
                    foreach ($field_config['values'] as $fcv) {
                        $transFields = trans($form_data_name.'_values');

                        // Adiciona a option, verificando se existe traducao para ela
                        $arrDataSelect[$fcv] = is_array($transFields) ? $transFields[$fcv]:$fcv;
                    }
                }

                
                $options['data-value'] = ($Model?$Model->{$field_config['name']}:'');
                
                $options['class'] = implode(' ', $options['class']);


                // Monta o field select
                $field = \Form::select($field_config['name'], $arrDataSelect, null, $options);
                break;
                case 'date':
                case 'string':
                // Monta o field texto
                $field = \Form::text($field_config['name'],(isset($field_config['default_value']) ? $field_config['default_value']:null),['class' => 'form-control', 'placeholder' => $field_trans]);
                break;
                default:
                // Dumpa o item inexistente
                var_dump($field_config['type']);exit;
                break;
            }

            // Formata o label
            $label_format = \Html::tag('div', $label->toHtml(), ['class' => 'col-md-3']);

            // Formata o field
            $field_format = \Html::tag('div', $field->toHtml(), ['class' => 'col-md-9']);

            // Adiciona os items no elemento principal
            $data[] = \Html::tag('div', $label_format.$field_format, ['class' => 'form-group col-md-12']);
        }

        // Botao de enviar
        $data[] = \Form::submit(trans($ControllerName.'.button_save'), ['class'=> 'btn btn-success']);

        // Fecha o form
        $data[] = \Form::close();
        // dd($data);

        return $data;
    }
}
