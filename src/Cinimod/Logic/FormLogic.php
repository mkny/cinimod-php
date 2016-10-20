<?php

namespace Mkny\Cinimod\Logic;

// $form = new Form($Model, false, array(), $controller);
use Illuminate\Database\Eloquent\Model AS Model;


// 'fields-default-class'

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

	private $form_configurations = [];

	public function __construct($form_config=false)
	{
		if($form_config){
			$this->form_configurations = $form_config;
		}
		
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
			$data['head'] = \Form::model($Model, array('url' => $Action, 'class' => 'form-horizontal col-md-12'));
		} else {
            // Abre o form padrao
			$data['head'] = \Form::open(array('url' => $Action, 'class' => 'form-horizontal col-md-12'));
		}
		
		// $data = [];

		foreach ($FormFields as $field_name => $field_config) {
			// Monta o label, adicionando um "*" se o campo for requerido
			// $label = \Form::label($field_config['name'],($this->Module.".{$field_config['name']}.form"));
			
			$translation = trans($this->Module.".{$field_config['name']}.form").((isset($field_config['required']) && $field_config['required']) ? '*':'');
			if(isset($field_config['trans'])){
				$translation = $field_config['trans'];
			}
			

			// Monta a label
			$label = \Form::label($field_config['name'],$translation);

			// Monta o field
			$field = $this->input($field_config);

			// mdd([$label, $field]);


            // Formata o label
			// $label_format = \Html::tag('div', $label->toHtml(), ['class' => 'col-md-3']);

            // Formata o field
			// $field_format = \Html::tag('div', $field->toHtml(), ['class' => 'col-md-9']);

            // Adiciona os items no elemento principal
			// $data[$field_name] = \Html::tag('div', $label_format.$field_format, ['class' => 'form-group col-md-12']);
			$data['fields'][$field_name]['label'] = $label;
			$data['fields'][$field_name]['field'] = $field;
			// mdd($data[$field_name]);
		}

        // Botao de enviar
		$data['submit'] = \Form::submit(trans($this->Module.'.button_save'), ['class'=> 'btn btn-success']);

        // Fecha o form
		$data['foot'] = \Form::close();

		// mdd($data);
		
		return $data;

	}


	public function input($config)
	{
		// Atributos fornecidos para o input
		$config['attributes'] = isset($config['attributes']) && is_array($config['attributes']) ? $config['attributes']:array();
		$config['attributes']['class'] = isset($config['attributes']['class']) ? $config['attributes']['class']:'';

		// Verifica a existencia do atributo "data-", o que precisa de um tratemento
		if(isset($config['attributes']['data']) && is_array($config['attributes']['data'])){
			// Percorre o array com o atributo data
			$keys_format = array_map(function($key){
				// Adiciona por referencia o "data-"
				return 'data-'.$key;
			},array_keys($config['attributes']['data']));

			// Faz a combinacao com os valores, merge com o attributes e sobreescreve o mesmo
			$config['attributes'] = array_merge($config['attributes'],array_combine($keys_format,$config['attributes']['data']));

			// Remove o attributes.data do elemento
			unset($config['attributes']['data']);
		}

		// Trata as classes como array, pra facilitar
		$config['attributes']['class'] = is_array($config['attributes']['class']) ? $config['attributes']['class']:[$config['attributes']['class']];
		

		if(isset($this->form_configurations['fields-default-class'])){
			$config['attributes']['class'][] = $this->form_configurations['fields-default-class'];
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
		// $config['type'] = (isset($config['type']) ? :'string');
		switch($config['type']){
			case 'select':
			$field = $this->select($config);
			break;
			case 'date':
			case 'string':
			case 'integer':
			$field = $this->text($config);
			break;
			case 'multi':
			$field = $this->multi($config);
			break;
			default:
				throw new \Exception($config['type']. ' is not recognized (yet);');exit;
			break;
		}

		return $field;
	}

	public function multi($config)
	{
		if($config['name'] == 'ind_status'){
			// mdd($config);
		}
		$fields = [];
		foreach($config['values'] as $key => $value){
			// mdd($value);
			$translated = trans($this->Module.".{$config['name']}.{$key}");
			$inp_name = "{$config['name']}[{$key}]";

			# need fix
			// if(is_array($value)){
			// 	continue;
			// 	$translated = '';

				// mdd($translated);
				// $value = $this->multi()
				// $value = (string) (json_encode($value));
				// dd($value);
			// }
			
			if(is_array($value)){
				$cfg = [];
				$cfg['type'] = 'multi';
				$cfg['name'] = $inp_name;
				// $cfg['trans'] = $inp_name;
				$cfg['values'] = $value;

				$input = $this->input($cfg);
			} else {
				$input = $this->input(array(
					'type' => 'string',
					'name' => $inp_name,
					'default_value' => $value));
			}

			$label = \Form::label($inp_name, $inp_name);
			$fields[] = \Html::tag('li', array($label, $input));
		}

		

		
		return \Html::tag('div',array(

			\Html::tag('hr', ''),
			\Html::tag('ul', $fields),
			// \Html::tag('hr', '')
			));
	}

	public function text($config)
	{
		// Variavel atributos do objeto
		$attributes = $config['attributes'];

		// Setta o place holder
		$attributes['placeholder'] = ($config['name']) ? :trans($this->Module.".{$config['name']}.form");
		// mdd($config);
		// Junta os nomes de classes
		$attributes['class'] = implode(" ", $attributes['class']);

		$value = null;

		// Verifica se e edicao, para adicionar o default value
		if(!$this->isEdit() && isset($config['default_value'])){
			$value = $config['default_value'];
		}

		return \Form::text($config['name'],$value,$attributes);
	}

	public function select($config)
	{
		// Helper classname
		$attributes = $config['attributes'];
		
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