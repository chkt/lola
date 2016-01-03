<?php

namespace chkt\route;

use chkt\route\Router;
use chkt\inject\Injector;



class CSVRouter extends Router {
	
	static public function getDependencyConfig(array $config) {
		return [[
			'type' => 'injector'
		], [
			'type' => 'object',
			'data' => $config
		]];
	}
	
	
	
	public function __construct(Injector $injector, Array $config) {
		if (!array_key_exists('path', $config) || !is_string($config['path'])) throw new \ErrorException();
		
		parent::__construct($injector);
		
		$handle = fopen($config['path'], 'r');
		
		if ($handle == false) throw new \ErrorException();
		
		for ($row = fgetcsv($handle); $row !== false; $row = fgetcsv($handle)) {
			$this->_path[]   = $row[0];
			$this->_ctrl[]   = $row[1];
			$this->_action[] = $row[2];
			$this->_view[]   = $row[3];
			$this->_tree[]   = $row[4];
			$this->_data[]   = $row[5];
		}
		
		fclose($handle);
	}
}
