<?php

namespace Neural;

class Data
{

	protected $typeSource;

	/**
	 * Description
	 * @param type $typeSource 
	 * @return type
	 */
	public function __construct($typeSource = 1)
	{
		$this->typeSource = $typeSource;
	}


	public function importData($file)
	{
		if($this->typeSource == 1) return $this->importCSV($file);
		return false;
	}


	private function importCSV($fileName)
	{


		if(is_file($fileName)){
				
			$data = file($fileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			$dataset = array();
			foreach ($data as $registro) {
			
				$dataset[] = $registro;
		
			}
			return $dataset;

		}
	return array();
	}


	public function exportArrayJson($array, $keyName)
	{
		if( !is_dir('public/')){
			die('diretorio /public inexistrente');
		}
		$fp = fopen('public/'.$keyName.'.json', 'w+');
		fwrite($fp, json_encode($array));
		fclose($fp);
	}


	public function importArrayJson($fileName)
	{
		$data = file('public/'.$fileName);
		$json = json_decode($data[0], true);
		return $json;
	}

}