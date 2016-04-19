<?php

namespace lola\inject;

use lola\prov\ProviderProvider;



class Injector {
	
	const VERSION = '0.1.4';
	
	const TYPE_INJECTOR = 'injector';
	const TYPE_LOCATOR = 'locator';
	const TYPE_SERVICE = 'service';
	const TYPE_FACTORY = 'factory';
	const TYPE_ARGUMENT = 'object';
	
	
	
	private $_locator = null;
	private $_resolve = null;
		
	
	public function __construct(ProviderProvider& $locator, Array $resolve = []) {		
		$this->_locator =& $locator;
		$this->_resolve = $resolve;
	}
	
	
	private function& _resolveFactory(Array $factory) {
		if (array_key_exists('factory', $factory)) {
			$config = array_key_exists('config', $factory) ? $factory['config'] : [];
			
			$ins =& $this
				->_locator
				->using('class')
				->using($factory['factory']);
			
			if (!($ins instanceof IDependencyFactory)) throw new \ErrorException('INJ: not a factory');
			
			$res = $ins
				->setConfig($config)
				->produce();
			
			return $res;
		}
		else if (array_key_exists('function', $factory)) {
			$deps = array_key_exists('dependencies', $factory) ? $factory['dependencies'] : [];
			$res = $this->process($factory['function'], $deps);
			
			return $res;
		}
		else throw new \ErrorException('INJ: malformed factory');
	}
	
	
	private function _resolveDependencies(Array $deps) {
		return array_map(function&($item) {
			if (!is_array($item) || !array_key_exists('type', $item)) throw new \ErrorException();
			
			$type = $item['type'];
						
			switch($type) {
				case self::TYPE_INJECTOR :
					return $this;
				
				case self::TYPE_LOCATOR :
					if (!array_key_exists('provider', $item)) return $this->_locator;
					else if (!array_key_exists('id', $item)) return $this->_locator->using($item['provider']);
					else return $this->_locator->using($item['provider'])->using($item['id']);
					
				case self::TYPE_SERVICE :
					if (!array_key_exists('id', $item)) return $this->_locator->using('service');
					else return $this->_locator->using('service')->using($item['id']);
					
				case self::TYPE_FACTORY : return $this->_resolveFactory($item);
				case self::TYPE_ARGUMENT :
					return $item['data'];
					
				default : 
					if (array_key_exists($type, $this->_resolve)) return $this->_resolve[$type];
					
					throw new \ErrorException();
			}
		}, $deps);
	}
	
	
	public function produce($className, Array $params = []) {
		if (!is_string($className) || empty($className)) throw new \ErrorException();
		
		$class = new \ReflectionClass($className);
		
		if (!$class->implementsInterface('\\lola\\inject\\IInjectable')) throw new \ErrorException();
		
		$deps = call_user_func([$className, 'getDependencyConfig'], $params);
		$args = $this->_resolveDependencies($deps);
		
		return $class->newInstanceArgs($args);
	}
	
	public function process(Callable $fn, Array $deps = []) {
		$args =& $this->_resolveDependencies($deps);
				
		return $fn(...$args);
	}
}