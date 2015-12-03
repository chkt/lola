<?php

namespace chkt\prov;

use \chkt\prov\IProviderResolver;
use \chkt\prov\SimpleProviderResolver;



abstract class AProvider {
	
	const VERSION = '0.0.7';
	
	
	
	protected $_factory = null;
	
	protected $_ins = null;
	
	protected $_resolver = null;
	
	
	public function __construct(Callable $factory, IProviderResolver $resolver = null) {
		$this->_factory = $factory;
		
		$this->_ins = [];
		
		$this->_resolver = !is_null($resolver) ? $resolver : new SimpleProviderResolver();
	}
	
	
	public function get($id = '') {
		return $this->_resolver->resolve($id, $this->_ins, $this->_factory);
	}
	
	public function& useInstance($id = '') {
		return $this->_resolver->resolve($id, $this->_ins, $this->_factory);
	}
	
	public function set($id, $ins) {
		if (
			!is_string($id) || empty($id) ||
			is_null($ins)
		) throw new \ErrorException();
			
		$this->_ins[$id] = $ins;
		
		return $this;
	}
	
	public function reset($id) {
		if (!is_string($id) || empty($id)) throw new \ErrorException();
		
		unset($this->_ins[$id]);
		
		return $this;
	}
	
	
	public function& useResolver() {
		return $this->_resolver;
	}
	
	public function setResolver(IProviderResolver $resolve) {
		$this->_resolver = $resolve;
		
		return $this;
	}
	
	public function clearResolver() {
		$this->_resolver = null;
		
		return $this;
	}
}
