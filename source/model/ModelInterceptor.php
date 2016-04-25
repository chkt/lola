<?php

namespace lola\model;

use lola\model\ModelInterceptorQueue;



class ModelInterceptor {
	
	const VERSION = '0.1.7';
	
	
	
	private $_get = null;
	private $_update = null;
	private $_delete = null;
	
	
	public function __construct(
		callable $get,
		ModelInterceptorQueue& $add,
		ModelInterceptorQueue& $remove
	) {
		$this->_get = $get;
		
		$this->_update =& $add;
		$this->_delete =& $remove;
	}
	
	
	public function getData() {		
		return call_user_func($this->_get);
	}
	
	
	public function hasUpdateCallback(callable $cb) {
		return $this->_update->has($cb);
	}
	
	public function addUpdateCallback(callable $cb) {
		$this->_update->append($cb);
		
		return $this;
	}
	
	public function removeUpdateCallback(callable $cb) {
		$this->_update->remove($cb);
		
		return $this;
	}
	
	
	public function hasDeleteCallback(callable $cb) {
		return $this->_delete->has($cb);
	}
	
	public function addDeleteCallback(callable $cb) {
		$this->_delete->append($cb);
		
		return $this;
	}
	
	public function removeDeleteCallback(callable $cb) {
		$this->_delete->remove($cb);
		
		return $this;
	}
}
