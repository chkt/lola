<?php

namespace chkt\inject;

use chkt\prov\ProviderProvider;



class Injector {
	
	const VERSION = '0.1.0';
	
	
	
	private $_locator = null;
	private $_resolve = null;
		
	
	public function __construct(ProviderProvider& $locator, Array $resolve = []) {		
		$this->_locator =& $locator;
		$this->_resolve = $resolve;
	}
	
	
	private function _resolveDependencies(Array $deps) {
		return array_map(function&($item) {
			if (!is_array($item) || !array_key_exists('type', $item)) throw new \ErrorException();
			
			$type = $item['type'];
						
			switch($type) {
				case 'injector' :
					return $this;
				
				case 'locator' :
					if (!array_key_exists('provider', $item)) return $this->_locator;
					else if (!array_key_exists('id', $item)) return $this->_locator->using($item['provider']);
					else return $this->_locator->using($item['provider'])->using($item['id']);
					
				case 'factory' :
					return call_user_func($item['function'], $this);
					
				case 'object' :
					return $item['data'];
					
				default : 
					if (array_key_exists($type, $this->_resolve)) return $this->_resolve[$type];
					
					throw new \ErrorException();
			}
		}, $deps);
	}
	
	
	public function produce($className, Array $params = []) {
		$class = new \ReflectionClass($className);
		
		if (!$class->implementsInterface('\\chkt\\inject\\IInjectable')) throw new \ErrorException();
		
		$deps = call_user_func([$className, 'getDependencyConfig'], $params);
		$args = $this->_resolveDependencies($deps);
		
		return $class->newInstanceArgs($args);
	}
}
