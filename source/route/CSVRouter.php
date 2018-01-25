<?php

namespace lola\route;

use eve\common\access\ITraversableAccessor;
use eve\inject\IInjector;



class CSVRouter
extends Router
{
	
	static public function getDependencyConfig (ITraversableAccessor $config) : array {
		return [
			'injector:',
			[
				'type' => IInjector::TYPE_ARGUMENT,
				'data' => $config->getProjection()
			]
		];
	}
	
	
	
	public function __construct(IInjector $injector, array $config) {
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
