<?php

namespace chkt\app;

//REVIEW due to a bug in PHP we cannot use the same trait multiple times
//Until the fix we have to magically assume the existance of methods
//use \chkt\app\TAppLocator;


trait TAppInjector {
	//use TAppLocator
	
	
	
	private function _tInjectorResolver(Array $config) {
		return array_map(function($item) {
			if (!is_array($item)) throw new \ErrorException();
			
			switch($item['type']) {
				case 'locator' : return $this->getLocator()->get($item['provider'])->get($item['id']);
				case 'app' : return $this;
				case 'factory' : return call_user_func($item['cb'], $this);
				case 'object' : return $item['data'];
				default : throw new \ErrorException();
			}
		}, $config);
	}
	
	public function InjectorFactory($className, $dependencyId = '') {
		$class = new \ReflectionClass($className);
		
		if (
			!$class->implementsInterface('\\chkt\\type\\IInjectable') ||
			!is_string($dependencyId)
		) throw new \ErrorException();
		
		$deps = call_user_func([$className, 'getDependencyConfig'], $dependencyId);
		$args = $this->_tInjectorResolver($deps);
		
		return $class->newInstanceArgs($args);
	}
}
