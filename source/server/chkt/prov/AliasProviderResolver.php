<?php

namespace lola\prov;

use lola\prov\SimpleProviderResolver;



class AliasProviderResolver extends SimpleProviderResolver {
	
	const VERSION = '0.0.7';
	
	
	
	private $_alias = null;
	
	
	public function __construct($default = SimpleProviderResolver::DEFAULT_ID) {
		parent::__construct($default);
		
		$this->_alias = [];
	}
	
	
	private function _isCircularRef($source, $target) {
		$alias = $this->_alias;
		
		if ($source === $target) return true;
		
		for (; array_key_exists($target, $alias); $target = $alias[$target]) {
			if ($source === $alias[$target]) return true;
		}
		
		return false;
	}
	
	private function _getTargetId($id) {
		$alias = $this->_alias;
		
		while (array_key_exists($id, $alias)) $id = $alias[$id];
		
		return $id;
	}
	
	
	public function isAlias($id) {
		if (!is_string($id) || empty($id)) throw new \ErrorException();
		
		return array_key_exists($id, $this->_alias);
	}
	
	public function setAlias($id, $target) {
		if (
			!is_string($id) || empty($id) ||
			!is_string($target) || empty($target) ||
			$this->_isCircularRef($id, $target)
		) throw new \ErrorException();
		
		$this->_alias[$id] = $target;
		
		return $this;
	}
	
	
	public function& resolve($id, Array& $instances, Callable $factory) {
		if (!is_string($id)) throw new \ErrorException();
		
		if (empty($id)) $id = $this->_default;
		
		if (array_key_exists($id, $this->_alias)) $id = $this->_getTargetId($id);
		
		return parent::resolve($id, $instances, $factory);
	}
}
