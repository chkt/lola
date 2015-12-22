<?php

namespace chkt\prov;



class SimpleProviderResolver implements IProviderResolver {
	
	const VERSION = '0.0.7';
	
	const DEFAULT_ID = 'default';
	
	
	
	protected $_default = '';
	
	
	public function __construct($default = self::DEFAULT_ID) {
		if (!is_string($default) || empty($default)) throw new \ErrorException();
		
		$this->_default = $default;
	}
	
	
	public function& resolve($id, Array& $instances, Callable $factory) {
		if (!is_string($id)) throw new \ErrorException();
		
		if (empty($id)) $id = $this->_default;
		
		if (!array_key_exists($id, $instances)) $instances[$id] = call_user_func($factory, $id);
		
		return $instances[$id];
	}
}
