<?php

namespace Mkny\Cinimod\Logic;

use DB;

class CRUDLogic extends MknyLogic {
	public function getForm($Model = false, $Action, $FormFields, $ControllerName)
	{
        if($Model){
            // Abre o form com os campos preenchidos 
            $data[] = \Form::model($Model, array('url' => $Action, 'class' => 'form-horizontal col-md-12'));
        } else {
            // Abre o form padrao
            $data[] = \Form::open(array('url' => $Action, 'class' => 'form-horizontal col-md-12'));
        }

        foreach ($FormFields as $field_config) {
            // Form variavel name
            $form_data_name = $ControllerName.'.'.$field_config['name'].'_form';

            // Traducao do campo
            $field_trans = trans($form_data_name);

            // Monta o label, adicionando um "*" se o campo for requerido
            $label = \Form::label(
                $field_config['name'],
                $field_trans.($field_config['required'] ? '*':'')
                );

            // Verifica os tipos de campos fornecidos
            switch($field_config['type']){
                case 'select':
                // No select, adiciona o primeiro indice um campo vazio, solicitando alteracao
                $arrDataSelect = ['' => '- Selecione -'];

                // Se for um relacionamento
                if($field_config['relationship']){

                    $dataModel = $field_config['relationship']['model']::relation($field_config['relationship']);
                    foreach ($dataModel as $dm){
                        $arrDataSelect[$dm['id']] = $dm['name'];
                    }
                } elseif($field_config['values']) {
                    // Se houverem valores definidos
                    foreach ($field_config['values'] as $fcv) {
                        $transFields = trans($form_data_name.'_values');

                        // Adiciona a option, verificando se existe traducao para ela
                        $arrDataSelect[$fcv] = is_array($transFields) ? $transFields[$fcv]:$fcv;
                    }
                }
                // Monta o field select
                $field = \Form::select($field_config['name'], $arrDataSelect, null, ['class' => 'form-control']);
                break;
                case 'date':
                case 'string':
                // Monta o field texto
                $field = \Form::text($field_config['name'],null,['class' => 'form-control', 'placeholder' => $field_trans]);
                break;
                default:
                // Dumpa o item inexistente
                var_dump($field_config['type']);exit;
                break;
            }

            // Formata o label
            $label_format = \Html::tag('div', $label->toHtml(), ['class' => 'col-md-2']);

            // Formata o field
            $field_format = \Html::tag('div', $field->toHtml(), ['class' => 'col-md-10']);

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
