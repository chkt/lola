<?php

namespace chkt\prov;



abstract class AProvider {
	
	protected $_factory = null;
	
	protected $_ins = null;
	
	
	public function __construct(Callable $factory) {
		$this->_factory = $factory;
		
		$this->_ins = [];
	}
	
	
	public function get($id) {
		if (!is_string($id) || empty($id)) throw new \ErrorException();
		
		$ins =& $this->_ins;
		
		if (!array_key_exists($id, $ins)) $ins[$id] = call_user_func($this->_factory, $id);
		
		return $ins[$id];
	}
	
	public function set($id, $ins) {
		if (!is_string($id) || is_null($ins)) throw new \ErrorException();
		
		$this->_ins[$id] = $ins;
		
		return $this;
	}
	
	public function clear($id) {
		if (!is_string($id) || empty($id)) throw new \ErrorException();
		
		unset($this->_ins[$id]);
		
		return $this;
	}
}
