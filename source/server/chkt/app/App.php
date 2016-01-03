<?php

namespace chkt\app;

use \chkt\app\TAppBase;
use \chkt\app\TAppFile;
use \chkt\inject\TAppInjector;
use \chkt\prov\TAppLocator;



class App implements IApp {
	use TAppBase;
	use TAppFile;
	use TAppInjector;
	use TAppLocator;
	
	
	
	const VERSION = '0.1.0';
	
	
	
	protected $_dict = [];
	
	
	public function __construct(Array $config) {
		$this->_dict = $config;
		
		date_default_timezone_set(array_key_exists('timezone', $config) ? $config['timezone'] : 'UTC');
		
		ob_start();
	}
	
	
	public function getProperty($name) {
		if (!is_string($name) || empty($name) || !array_key_exists($name, $this->_dict)) throw new \ErrorException();
		
		return $this->_dict[$name];
	}
}
