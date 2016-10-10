<?php

namespace Mkny\Cinimod\Logic;

// $form = new Form($Model, false, array(), $controller);



class FormLogic
{
	/**
	 * Variavel pra guardar a acao do formulario
	 * @var [type]
	 */
	private $action;

	/**
	 * Variavel pra guardar o EloquentModel
	 * @var \Illuminate\Database\Eloquent\Model
	 */
	private $Model;

	/**
	 * Variavel pra guardar o modulo que esta sendo tratado (aka Controlador)
	 * @var [type]
	 */
	private $Module;
	
	function __construct()
	{
		
	}

	public function isEdit()
	{
		return (isset($this->Model) && !empty($this->Model));
	}

	/**
	 * Retorna a instancia do formulario com os campos
	 * @param  \Model $Model          Model para insercao no db (ou falso)
	 * @param  string  $Action         Acao do formulario
	 * @param  array  $FormFields     Campos pra montar o form
	 * @param  Nome do controlador  $ControllerName
	 * @return string
	 */
	public function getForm($Model = false, $Action, $FormFields, $ControllerName){
		$this->Module = $ControllerName;
		$this->Model = $Model;

		if($Model){
			$this->Model = $Model;
            // Abre o form com os campos preenchidos 
			$data[] = \Form::model($Model, array('url' => $Action, 'class' => 'form-horizontal col-md-12'));
		} else {
            // Abre o form padrao
			$data[] = \Form::open(array('url' => $Action, 'class' => 'form-horizontal col-md-12'));
		}
		
		// $data = [];

		foreach ($FormFields as $field_name => $field_config) {
			// Monta o label, adicionando um "*" se o campo for requerido
			$label = \Form::label($field_config['name'],trans($this->Module.".{$field_config['name']}.form").((isset($field_config['required']) && $field_config['required']) ? '*':''));

			// Monta o field
			$field = $this->input($field_config);


            // Formata o label
			$label_format = \Html::tag('div', $label->toHtml(), ['class' => 'col-md-3']);

            // Formata o field
			$field_format = \Html::tag('div', $field->toHtml(), ['class' => 'col-md-9']);

            // Adiciona os items no elemento principal
			$data[] = \Html::tag('div', $label_format.$field_format, ['class' => 'form-group col-md-12']);
		}

        // Botao de enviar
		$data[] = \Form::submit(trans($this->Module.'.button_save'), ['class'=> 'btn btn-success']);

        // Fecha o form
		$data[] = \Form::close();

		return $data;

	}

	public function input($config)
	{
		$config['attributes'] = isset($config['attributes']) && !empty($config['attributes']) ? $config['attributes']:array();
		if(isset($config['attributes']['data']) && is_array($config['attributes']['data'])){
			$dd = array();
			foreach ($config['attributes']['data'] as $key => $value) {
				$dd['data-'.$key] = $value;
			}
			unset($config['attributes']['data']);
			$config['attributes'] = array_merge($config['attributes'], $dd);
		}

		// Trata as classes como array, pra facilitar
		if(isset($config['attributes']['class'])){
			$config['attributes']['class'] = is_array($config['attributes']['class']) ? $config['attributes']['class']:[$config['attributes']['class']];
		}

		// Regra para desabilitar o campo, quando em modo de edicao
		if(isset($config['attributes']['disabled_edit']) && $config['attributes']['disabled_edit'] && $this->isEdit()){
			$config['attributes']['disabled'] = 'disabled';
		}
		unset($config['attributes']['disabled_edit']);

		// Regra para desabilitar o campo, quando em modo de adicionar
		if(isset($config['attributes']['disabled_add']) && $config['attributes']['disabled_add'] && !$this->isEdit()){
			$config['attributes']['disabled'] = 'disabled';
		}
		unset($config['attributes']['disabled_add']);


		// Formatador de campos
		$field = null;
		switch($config['type']){
			case 'select':
			$field = $this->select($config);
			break;
			case 'date':
			case 'string':
			$field = $this->text($config);
			break;
			default:
			echo $config['type'];
			break;
		}

		return $field;
	}

	public function multi($config, $translation)
	{
		// mdd($config);
		$fields = [];
		foreach($config['values'] as $key => $value){

			$translated = trans($translation.'.'.$key);

			# need fix
			if(is_array($value)){
				continue;
				$translated = '';

				// mdd($translated);
				// $value = $this->multi()
				$value = (string) (json_encode($value));
				// dd($value);
			}
			

			$fields[] = \Form::label($config['name'].'['.$key.']', $config['name'].'['.$key.']');
			$fields[] = $this->text(array('name' => $config['name'].'['.$key.']','default_value' => $value), $translated);
			
		}

		// $fields[] = \Html::tag('hr', '');
		return \Html::tag('div', $fields);
	}

	public function text($config)
	{
		$attributes = $config['attributes'];

		$attributes['class'][] = 'form-control';
		$attributes['placeholder'] = trans($this->Module.".{$config['name']}.form");
		$attributes['class'] = implode(" ", $attributes['class']);

		$value = null;

		if(!$this->isEdit() && isset($config['default_value'])){
			$value = $config['default_value'];
		}

		return \Form::text($config['name'],$value,$attributes);
	}

	public function select($config)
	{


        // Helper classname
		$attributes = $config['attributes'];
		// $options['class'] = (isset($config['attributes']['class']) && is_array($config['attributes']['class'])) ? $config['attributes']['class']:[];
		$attributes['class'][] = 'form-control';


        // No select, adiciona o primeiro indice um campo vazio, solicitando alteracao
		$arrDataSelect = ['' => '- Selecione -'];

        // Se for um relacionamento
		if(isset($config['relationship']) && $config['relationship']){

			$dataModel = [];
			// Quando tem dependencia na tabela
			if(isset($config['relationship']['dependsOn']) && $config['relationship']['dependsOn']){
                // class para funcoes ajax
				$attributes['class'][] = 'mkny-select-depends';

                // data-value depends
				$attributes['data-depends'] = $config['relationship']['dependsOn'];

				// Implementacao para nao depender do ajax (temporariamente desativada);
                // $dataModel = $config['relationship']['model']::relation($config['relationship'],($Model?$Model->{$config['relationship']['dependsOn']}:''));
			} else {
                // Busca dinamica das opcoes
				$dataModel = $config['relationship']['model']::relation($config['relationship']);
			}

			// Monta as opções vindas do datamodel
			foreach ($dataModel as $dm){
				$arrDataSelect[$dm['id']] = $dm['name'];
			}
		} elseif(isset($config['values']) && $config['values']) {
            // Se houverem valores definidos
			foreach ($config['values'] as $values) {
				$transFields = trans($this->Module.".{$config['name']}.form_values");

                // Adiciona a option, verificando se existe traducao para ela
				// $arrDataSelect[$values] = $values;
				$arrDataSelect[$values] = is_array($transFields) ? $transFields[$values]:$values;
			}
		}

		// Atribui o valor do select (para ser selecionado pelo ajax, posteriormente)
		$attributes['data-value'] = ($this->isEdit() ? $this->Model->{$config['name']}:'');

		// Tratamento da classe
		if(isset($attributes['class']) && !empty($attributes['class'])){
			$attributes['class'] = implode(' ', $attributes['class']);
		}

        // Monta o field select
		return \Form::select($config['name'], $arrDataSelect, null, $attributes);
	}
}