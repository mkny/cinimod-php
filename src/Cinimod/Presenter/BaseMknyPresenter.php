<?php

namespace Mkny\Cinimod\Presenter;

use Laracasts\Presenter\Presenter;

use Carbon\Carbon;

class BaseMknyPresenter extends Presenter {
	public function __call($name, $value)
	{
		if(!method_exists($this, $name)){
			return $this->{$name};
		} else {
			return $this->{$name}();
		}
	}

	private function array_parser($arrData,$value=false)
	{
		if(is_array($arrData) && $value){
			return $arrData[$value];
		} elseif (!is_array($arrData) && $value){
			return $value;
		}

		return $arrData;
	}

	private function date($date)
	{
		if(!$date){
			return null;
		}
		return date('d/m/Y H:i', strtotime($date));
		// return Carbon::createFromFormat('d/m/Y H:i', $this->entity->dta_cadastro)->toDateTimeString();
		// return Carbon::createFromFormat('Y-m-d H:i:s', $this->entity->dta_cadastro)->diffForHumans();
	}

	public function ind_status()
	{
		$str = '';
		// Recupera o arquivo de traducao default
		$translated = trans(class_basename($this->entity).'.ind_status.form_values'); 

		// Verifica se o indice esta presente
		if (isset($translated[$this->entity->ind_status]) && !empty($translated[$this->entity->ind_status])) {
			$str = $translated[$this->entity->ind_status];
		}

		return $str ? $str:$this->entity->ind_status;
	}

	public function dta_cadastro()
	{
		return $this->date($this->entity->dta_cadastro);
	}

	public function dta_atualizacao()
	{
		return $this->date($this->entity->dta_atualizacao);
	}
}