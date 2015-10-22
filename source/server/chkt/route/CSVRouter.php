<?php

namespace chkt\route;

use \chkt\route\Router;



class CSVRouter extends Router {
	
	public function __construct($path) {
		if (!is_string($path)) throw new \ErrorException();
		
		parent::__construct();
		
		$handle = fopen($path , 'r');
		
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