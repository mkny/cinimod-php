<?php

namespace Mkny\Cinimod\Logic;

use DB;

class CRUDLogic {

    /**
     * Facilitador para montagem da datagrid
     * 
     * @param  Illuminate\Pagination\LengthAwarePaginator $dbRows      Paginador
     * @param  array $fields_data Configuracao de campos
     * @param  string $pkey        Nome da primary key
     * @param  string $modelName   Nome do model
     * @return array              Tabela + paginator
     */
    public function datagrid($dbRows,$fields_data, $pkey, $modelName)
    {
        // echo '<pre>';
        // print_r($dbRows);
        // exit;
        // Instancia a tabela
        $table = new TableLogic();

        // Trata os fields, apenas para pegar os nomes
        $fields = array_keys($fields_data);


        // Adiciona as classes na tabela
        $table->addTableClass([
            'table',
            'table-striped',
            'check-all-container'
            ]);

        // Monta todos os cabecalhos, inclusive os dinamicos
        $headers = array_merge(
            array('checks' => [\Form::checkbox('idx', '', '', ['class' => 'checkbox check-all'])]),
            $fields,
            array('actions')
            );


        // Traducao dos headers, que vem com os nomes do banco de dados, 
        // aqui eles tomam nomes especificos dos arquivos de configuracao
        $headersTranslated = [];
        foreach ($headers as $field_key => $field_name) {
            // Se nao for numerico, indica um conteudo customizado
            if(is_numeric($field_key)){
                $headersTranslated[$field_name] = trans($modelName.'.'.$field_name.'_grid');

                // Aqui faz o tratamento do link no titulo,
                // Usado para campos de busca, ordenacao, etc
                if(isset($fields_data[$field_name]['searchable']) && $fields_data[$field_name]['searchable']){
                    // Insere o link nos items marcados como searchable (o link e de ordenacao)
                    $headersTranslated[$field_name] = [
                    \Html::tag(
                        'a',
                        $headersTranslated[$field_name],
                        ['href' => "?order=".array_search($field_name, $fields)."&card=".(\Request::input('card', 'asc') == 'asc' ? 'desc':'asc')]
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
        foreach ($dbRows->items() as $item) {
            // Monta as colunas
            $dataCols = [];
            foreach ($fields as $field_name) {
                if (isset($fields_data[$field_name]['relationship']) && $fields_data[$field_name]['relationship']) {
                    if($item->{$field_name.'s'}){
                        $dataCols[$field_name] = $item->{$field_name.'s'}->{$fields_data[$field_name]['relationship']['field_show']};
                    } else {
                        $dataCols[$field_name] = '';
                    }
                } else {
                    // Setta a coluna com o presenter no array de colunas
                    $dataCols[$field_name] = $item->present()->{$field_name};
                }
            }

            // Setta a linha no array de linhas
            $dataRows[] = $dataCols;
        }

        // Aqui monta o objeto propriamente dito
        foreach ($dataRows as $dataRow) {

            // Adiciona o check individual
            $dataRow['checks'] = array(
                \Form::checkbox('id_sec[]', $dataRow[$pkey], '', ['class'=> 'checkbox'])
                );

            // Adiciona os botoes
            $dataRow['actions'] = array(
                // \Html::tag('center', [
                    $table->button(
                        action($modelName.'Controller@getEdit', [$dataRow[$pkey]]),
                        'Editar',
                        'glyphicon glyphicon-edit btn btn-sm btn-success'),

                    $table->button(
                        action($modelName.'Controller@getSwitchStatus', [$dataRow[$pkey]]),
                        'Editar',
                        'glyphicon btn btn-sm '.($dataRow['ind_status'] == 'A' ? 'glyphicon-ban-circle btn-warning':'glyphicon-ok btn-info')),

                    $table->button(
                        action($modelName.'Controller@getDelete', [$dataRow[$pkey]]),
                        'Editar',
                        'glyphicon glyphicon-trash btn btn-sm btn-danger')
                    // ], ['class' => ''])
                );

            $table->insertRow($dataRow);
        }

        // Retorna a tabela montada
        return $table->getTable();
    }

    public function getForm($Model = false, $Action, $FormFields, $ControllerName)
    {

        if($Model){
            // Abre o form com os campos preenchidos 
            $data[] = \Form::model($Model, array('url' => $Action, 'class' => 'form-horizontal col-md-12'));
        } else {
            // Abre o form padrao
            $data[] = \Form::open(array('url' => $Action, 'class' => 'form-horizontal col-md-12'));
        }

        // echo '<pre>';
        // print_r($FormFields);
        // exit;

        foreach ($FormFields as $field_config) {
            // Form variavel name
            $form_data_name = $ControllerName.'.'.$field_config['name'].'_form';

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
                    // echo 'a';exit;
                    if(isset($field_config['relationship']['dependsOn']) && $field_config['relationship']['dependsOn']){
                        // class para funcoes ajax
                        $options['class'][] = 'mkny-select-depends';

                        // data-value depends
                        $options['data-depends'] = $field_config['relationship']['dependsOn'];

                        $dataModel = [];
                        // $dataModel = $field_config['relationship']['model']::relation($field_config['relationship']);
                    } else {
                        // dd($field_config);exit;
                        $dataModel = $field_config['relationship']['model']::relation($field_config['relationship']);
                    }

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
