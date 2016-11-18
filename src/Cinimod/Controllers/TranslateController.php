<?php

namespace Mkny\Cinimod\Controllers;

use App\Http\Controllers\Controller;

// use Mkny\Cinimod\Logic\AppLogic;

/**
* 
*/
class TranslateController extends Controller
{

    private function _getLangPacks()
    {
        $langFiles = array();

        $langs = \File::directories(mkny_lang_path());
        foreach ($langs as $lang) {

            $langFiles[] = class_basename($lang);
        }

        return $langFiles;
    }


    /**
     * Funcao para edicao da traducao do [Modulo]
     * @param string $controller Nome do controlador
     * @return void
     */
    public function getIndex($controller=false)
    {
        $lang = \Request::input('lang');

        if(!$lang){
            $lang = \App::getLocale();
        }

        return view('cinimod::admin.generator.trans')->with([
            'langlist' => $this->_getLangPacks(),
            'langlist_sel' => $lang,
            'langfiles' => $this->_getTransFiles($lang)
            ]);

    }

    public function getFile($lang=false, $module=false)
    {


        // dd(view()->shared('controller'));
        // app('request')->attributes->get('controller');

        if(!$lang){
            $lang = \App::getLocale();
        }
        // Busca o arquivo especificado
        $cfg_file = mkny_lang_path($lang.'/'.$module).'.php';

        // Field types
        $f_types = array_unique(array_values(app()->make('Mkny\Cinimod\Logic\AppLogic')->_getFieldTypes()));

        // Se o diretorio nao existir
        if (!realpath(dirname($cfg_file))) {
            \File::makeDirectory(dirname($cfg_file));
        }

        // Config file data
        if(!\File::exists($cfg_file)){
            \File::put($cfg_file, "<?php return array( 'teste' => 'teste' );");
        }

        // Arquivo aberto
        $config_str = \File::getRequire($cfg_file);




        $arrFields = array();
        foreach ($config_str as $field_name => $field_value) {
            if(!is_string($field_value)){
                $arrFields[$field_name] = array(
                    'name' => $field_name,
                    'trans' => $field_name,
                    'values' => $field_value,
                    'type' => 'multi',
                    );
            } else {
                $arrFields[$field_name] = array(
                    'name' => $field_name,
                    'trans' => $field_name,
                    'default_value' => $field_value,
                    'type' => 'string',
                    );
            }
        }

        return view('cinimod::admin.generator.trans_detailed')->with([
            'form' => app()->make('\Mkny\Cinimod\Logic\FormLogic', [['fields-default-class' => 'form-control']])->getForm(
                false,
                action('\\'.get_class($this).'@postFile',[$lang, $module]),
                $arrFields,
                $module

                )
            ]);

    }


    /**
     * Varre o diretorio em busca de arquivos de traducao
     * 
     * @return array
     */
    public function _getTransFiles($langpack)
    {
    	$arrExclude = array('auth', 'pagination', 'passwords', 'validation');
        // Pega todos os arquivos do diretorio
    	$configs = \File::files(mkny_lang_path($langpack.'/'));
        
        // Monta o array
    	$arrConfig = array();
    	foreach ($configs as $config) {
    		$c_data = substr(class_basename($config),0,-4);
    		if(in_array($c_data, $arrExclude)){
    			continue;
    		}
    		$arrConfig[] = $c_data;
    	}

    	return $arrConfig; 
    }

    public function postFile($lang, $module=false)
    {
      // Armazena os dados enviados
    	$req_fields = \Request::all();

    	if(isset($req_fields['new_fields'])){
        // Percorre todos os indices, em busca de novos fields
    		foreach ($req_fields['new_fields']['key'] as $key_field => $new_field) {
    			$to_set = $req_fields['new_fields']['value'][$key_field];

          // Faz o nest pro item
    			\Mkny\Cinimod\Logic\UtilLogic::setNestedArrayValue($req_fields,$new_field, $to_set, '.');
    		};

    		unset($req_fields['new_fields']);
    	}

      // mdd($req_fields);

      // if (isset($req_fields['new_file_name'])) {
        // return redirect()->route('adm::trans', [$lang, $req_fields['new_file_name']]);
      // }

      // Arquivo de configuracao
    	$cfg_file = mkny_lang_path($lang.'/'.$module).'.php';


    	\Mkny\Cinimod\Logic\UtilLogic::updateConfigFile($cfg_file, $req_fields);

      // Volta para a tela de selecao
    	return redirect()->action('\\'.get_class($this).'@postFile',[$lang, $module])->with(array(
    		'status' => 'success',
    		'message' => 'Arquivo atualizado!'
    		));
    }
}
