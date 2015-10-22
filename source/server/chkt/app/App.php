<?php

namespace chkt\app;

use \chkt\app\TAppBase;
use \chkt\app\TAppFile;



class App implements IApp {
	use TAppBase;
	use TAppFile;
	
	
	const VERSION = '0.0.1';
	
	
	protected $_dict = [];
	
	
	
	static public function File($name) {		
		$config = include $name;
		
		return new static($config);
	}
	
	
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