<?php

namespace Mkny\Cinimod\Logic;

use DB;
use Schema;

class AppLogic extends MknyLogic {
	/**
	 * Array associativo de formatos (db) para rules (code)
	 * @var array
	 */
	private $fieldTypes = [
	'select' => 'select',
	'int' => 'integer',
	'integer' => 'integer',
	'bigint' => 'integer',

	'date' => 'date',
	'datetime' => 'date',
	'timestamp without time zone' => 'date',
	'timestamp' => 'date',

	'text' => 'string',
	'varchar' => 'string',
	'char' => 'string',
	'character varying' => 'string',
	'character' => 'string',
	'enum' => 'string',
	'double precision' => 'numeric'

	];

	/**
	 * Table namespace usado para remoçao ao criar o controller
	 * 
	 * @var array
	 */
	private $defaultTableNamespace = ['tab_', 'web_', 'tab_web_'];

	/**
	 * Funcao que retorna as colunas da tabela, com informacoes adicionais
	 * 
	 * @param  string  $table  Tabela para buscar no banco
	 * @param  string $schema Schema do banco (caso PG)
	 * @return array          Dados da tabela
	 */
	public function buildColumns($table, $schema=false)
	{
		// Quando a tabela e fornecida com "schema"."table"
		if(strpos($table, '.')){
			$table_parts = explode('.', $table);
			$schema = $table_parts[0];
			$table = $table_parts[1];
		}

		// Monta os campos
		$sql = "SELECT
		COLUMN_NAME AS name,
		DATA_TYPE AS type,
		IS_NULLABLE AS is_null,
		CHARACTER_MAXIMUM_LENGTH AS length,
		COLUMN_DEFAULT AS default_value";

		// Odeio fazer esses "fix"
		// Funciona para buscar os valores de "enum" do banco MYSQL!
		switch(Schema::getConnection()->getDriverName()){
			case 'mysql':
			$sql .= " ,CASE WHEN DATA_TYPE = 'enum' THEN COLUMN_TYPE ELSE NULL END AS def_constraint ";
			break;
			case 'pgsql':
			default:
			$sql .= " ,NULL AS def_constraint ";
			break;
		}

		$sql .= "
		FROM
		information_schema. COLUMNS
		WHERE
		1 = 1
		AND TABLE_NAME = '{$table}'
		AND TABLE_SCHEMA = '{$schema}'";

		return DB::select($sql);
	}

	/**
	 * Funcao que busca as tabelas
	 * 
	 * @return array Tabelas do banco, no schema especificado
	 */
	public function buildTables()
	{
		$sql = "SELECT
		TABLE_SCHEMA AS \"schema\",
		TABLE_NAME AS \"name\"
		FROM information_schema.TABLES
		WHERE
		TABLE_TYPE = 'BASE TABLE' AND
		TABLE_SCHEMA = '{{schema}}' ";

		// Busca em todos os schemas fornecidos
		$sql = $this->unifySchema($sql). " ORDER BY \"schema\", \"name\"";

		return DB::select($sql);
	}

	/**
	 * Helper pra buscar apenas os nomes das colunas no banco!
	 * 
	 * @param string $table Nome da tabela
	 * @return array
	 */
	public function buildColumnsName($table){
		$arrColumns = $this->buildColumns($table);

		$arrData = [];
		foreach ($arrColumns as $column) {
			$arrData[] = $column->name;
		}
		return $arrData;
	}

	/**
	 * Funcao que busca os relacionamentos entre tabelas do banco de dados
	 * 
	 * @param string $table Nome da tabela
	 * @return array
	 */
	public function buildRelationships($table){
		return array_filter($this->_getConstraints($table), function($arr){
			return $arr->rel_type == 'FOREIGN' ? true:false;
		});
	}

	/**
	 * Busca na tabela as constraints UNIQUE, FOREIGN e CHECK (pgsql)
	 * 
	 * @param string $table Nome da tabela
	 * @return array
	 * // sqlserver, sqlite
	 */
	public function _getConstraints($table){
		if(strpos($table, '.')){
			$table_parts = explode('.', $table);
			$schema = $table_parts[0];
			$table = $table_parts[1];
		}

		switch(Schema::getConnection()->getDriverName()){
			
			// Banco MYSQL
			case 'mysql':
			$sql = "SELECT
			kcu.table_schema AS schema_foreign,
			kcu.table_name AS table_foreign,
			kcu.column_name AS table_foreign_field,

			kcu.referenced_table_schema AS schema_primary,
			kcu.referenced_table_name AS table_primary,
			kcu.referenced_column_name AS table_primary_field,
			'RESTRICT' AS update_type
			-- falta os check
			,CASE
			WHEN tc.CONSTRAINT_TYPE = 'UNIQUE' THEN
			'UNIQUE'
			WHEN tc.CONSTRAINT_TYPE = 'FOREIGN KEY' AND kcu.referenced_table_schema IS NOT NULL THEN
			'FOREIGN'
			WHEN kcu.referenced_table_schema IS NULL THEN
			'PRIMARY'
			END AS rel_type

			FROM
			information_schema.KEY_COLUMN_USAGE AS kcu
			LEFT JOIN information_schema.TABLE_CONSTRAINTS AS tc ON tc.table_name = kcu.table_name AND kcu.table_schema = tc.table_schema
			WHERE
			1=1
			AND tc.constraint_type IN ('UNIQUE','FOREIGN KEY')
			AND kcu.table_name = '{$table}'
			AND kcu.TABLE_SCHEMA = '{$schema}'

			ORDER BY kcu.TABLE_NAME, kcu.COLUMN_NAME;";
			break;


			// Banco PGSQL
			case 'pgsql':
			$sql = "SELECT
			pns.nspname AS schema_foreign,
			pgcs.relname AS table_foreign,
			is1.column_name AS table_foreign_field,

			pns2.nspname AS schema_primary,
			pgcs2.relname AS table_primary,
			is2.column_name AS table_primary_field,
			CASE
			WHEN pgc.confupdtype = 'a' THEN
			'NOACTION'
			WHEN pgc.confupdtype = 'r' THEN
			'RESTRICT'
			WHEN pgc.confupdtype = 'c' THEN
			'CASCADE'
			WHEN pgc.confupdtype = 'n' THEN
			'SETNULL'
			WHEN pgc.confupdtype = 'd' THEN
			'DEFAULT'
			END AS update_type,
			CASE
			WHEN pgc.contype = 'u' AND pns2.nspname is null THEN
			'UNIQUE'
			WHEN pgc.contype = 'c' THEN
			'CHECK'
			WHEN pgc.contype = 'f' THEN
			'FOREIGN'
			END AS rel_type
			,pgc.consrc AS expression

			FROM
			pg_constraint AS pgc

			INNER JOIN pg_namespace AS pns ON pns.oid = pgc.connamespace
			INNER JOIN pg_class AS pgcs ON pgcs.oid = pgc.conrelid
			LEFT JOIN pg_class AS pgcs2 ON pgcs2.oid = pgc.confrelid
			LEFT JOIN pg_namespace AS pns2 ON pgcs2.relnamespace = pns2.oid

			LEFT JOIN information_schema.COLUMNS is1 ON is1.TABLE_NAME = pgcs.relname AND is1.ordinal_position = (SUBSTRING(pgc.conkey::varchar,2,LENGTH(pgc.conkey::VARCHAR)-2)::INTEGER)
			LEFT JOIN information_schema.COLUMNS is2 ON is2.TABLE_NAME = pgcs2.relname AND is2.ordinal_position = (SUBSTRING(pgc.confkey::varchar,2,LENGTH(pgc.confkey::VARCHAR)-2)::INTEGER)

			WHERE
			1=1
			AND pgc.contype IN ('u','c', 'f')
			AND pns.nspname = '{$schema}'
			AND pgcs.relname = '{$table}'
			ORDER BY pns.nspname, pgcs.relname ";

			break;
			default:
			throw new Exception('Driver não tratado');
			break;
		}

		return DB::select($sql);
	}

	/**
	 * Funcao que retorna o possivel nome do controller
	 * @param  string $table Tabela
	 * @return string        Controller name camelCased
	 */
	public function controllerName($table){
		return studly_case(str_replace($this->defaultTableNamespace,'', $table));
	}
	
	/**
	 * Funcao que retorna a variavel protegida fieldTypes
	 * @return array FieldTypes
	 */
	public function _getFieldTypes()
	{
		return $this->fieldTypes;
	}

	/**
	 * Funcao para auxiliar buscar em todos os schemas disponiveis
	 * 
	 * @param string $sql Select do banco
	 * @return string Sqls com UNION, e os schemas preenchidos (necessario tag {{schema}})
	 */
	private function unifySchema($sql){
		$schemas = explode(',',$this->_getSchemas());

		$sqls = [];
		foreach ($schemas as $schema) {
			$sqls[] = str_replace('{{schema}}', $schema, $sql);
		}
		
		return '('.implode(') UNION ALL (', $sqls).')';
	}

	/**
	 * Funcao para buscar todos os schemas disponiveis no sistema
	 * 
	 * @return string
	 */
	public function _getSchemas(){
		$schema = DB::getConfig('schema') ?:DB::getConfig('database');
		$schema_aux = DB::getConfig('schema_aux');
		$schema = $schema_aux ? $schema_aux.','.$schema:$schema;

		// var_dump($schema);exit;
		return $schema;
	}

	public function buildColumnsWithKeys($table){
		$arrUnify = [];

		$columns = $this->buildColumns($table);
		$constraints = $this->_getConstraints($table);

		foreach ($columns as $key => $value) {
			$arrUnify[$value->name] = $value;
		}

		foreach ($constraints as $ckey => $cvalue) {
			if ($cvalue->rel_type == 'CHECK') {
				$arrUnify[$cvalue->table_foreign_field]->def_constraint = $cvalue->expression;
			}

			if($cvalue->rel_type == 'UNIQUE'){
				$arrUnify[$cvalue->table_foreign_field]->uniq_field = "{$cvalue->table_foreign},{$cvalue->table_foreign_field},'.(\$this->one?:0).',{$constraints[0]->table_foreign_field}";
			}
		}


		return array_values($arrUnify);
	}



    /**
     * Constroi a definicao do "checkIn"
     * 
     * @param string $value checagem crua
     * @return array
     */
    public function _getCheck($value){
    	$arrValues = [];
        // echo '<pre>';
        // print_r($value);
        // exit;
    	if(substr($value,0,4) == 'enum'){
    		$arrValues = explode("','", substr($value,6,-2));
    	} elseif(substr($value,0,1) == '(' && strpos($value, 'ANY (') > 0){
    		$matches = [];
    		preg_match_all('/\'(.*?)\'/', $value, $matches);

    		$arrValues = $matches[1];
    	} elseif(substr($value,0,1) == '(' && strpos($value, ' OR ') > 0){
            // se comeca com "(" e tem "bpchar" no meio

    		$value = preg_replace('/[\(\)]/', '', $value);
    		$value_parts = explode('OR', $value);
    		foreach ($value_parts as $value_part) {
    			$matches = array();
    			preg_match_all('/\'(.*)\'/', $value_part, $matches);
    			$arrValues[] = $matches[1][0];
    		}
    	}

    	return $arrValues;
    }
}
