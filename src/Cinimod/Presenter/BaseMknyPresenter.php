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

	public function dta_cadastro()
	{
		return date('d/m/Y H:i', strtotime($this->entity->dta_cadastro));
		// return Carbon::createFromFormat('d/m/Y H:i', $this->entity->dta_cadastro)->toDateTimeString();
		// return Carbon::createFromFormat('Y-m-d H:i:s', $this->entity->dta_cadastro)->diffForHumans();
	}

	public function dta_atualizacao()
	{
		if(!$this->entity->dta_atualizacao){
			return null;
		}
		return date('d/m/Y H:i', strtotime($this->entity->dta_atualizacao));
	}
}