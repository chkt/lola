<?php

namespace chkt\model;

use chkt\model\IResourceCollection;



abstract class ACollection {
	
	const VERSION = '0.1.2';
	
	
	
	private $_resource = null;
	private $_factory = null;
	
	private $_model = null;
	
	private $_length = 0;
	private $_cursor = 0;
		
	
	public function __construct(IResourceCollection $resource, Callable $itemFactory) {
		$this->_resource = $resource;
		$this->_factory = $itemFactory;
		
		$this->_model = [];
		
		$this->_length = $resource->getLength();
		$this->_cursor = 0;
	}
	
	
	private function& _useModel($index) {
		$models =& $this->_model;
		
		if (!array_key_exists($index, $models)) {
			$resource =& $this->_resource->useItem($index);
			$models[$index] =& call_user_func_array($this->_factory, [ & $resource ]);
		}
		
		return $models[$index];
	}
	
	
	public function isLive() {
		return $this->_resource->isLive();
	}
	
	
	public function getLength() {
		return $this->_length;
	}
	
	
	public function& useIndex($index) {
		if (!is_int($index) || $index < 0) throw new \ErrorException();
		
		if ($index > $this->_length - 1) {
			$null = null;
			
			return $null;
		}
		
		$this->_cursor = $index;
		
		return $this->_useModel($index);
	}
	
	public function& useOffset($offset = 0) {
		if (!is_int($offset)) throw new \ErrorException();
		
		$cursor = $this->_cursor + $offset;
		
		if ($cursor < 0 || $cursor > $this->_length - 1) return null;
		
		$this->_cursor = $cursor;
		
		return $this->_useModel($cursor);
	}
	
	
	public function& useFirst() {
		return $this->useIndex(0);
	}
	
	public function& usePrev() {
		return $this->useOffset(-1);
	}
	
	public function& useNext() {
		return $this->useOffset(1);
	}
	
	public function& useLast() {
		return $this->useIndex($this->_length - 1);
	}
	
	
	public function update() {
		$this->_resource->update();
		
		return $this;
	}
}
