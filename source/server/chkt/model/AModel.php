<?php

namespace chkt\model;

use chkt\model\IResource;



abstract class AModel {
	
	const VERSION = '0.1.5';
	
	
		
	private $_resource = null;
	private $_data = null;
	
	private $_update = true;
	
	
	public function __construct(IResource $resource) {
		$this->_resource = $resource;
		$this->_data = null;
		
		$this->_update = true;
	}
	
	
	protected function& _useResource() {
		if (is_null($this->_data)) $this->_data = $this->_resource->getData();
		
		return $this->_data;
	}
	
	public function _setResourceProperty($name, $value) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		$data = $this->_useResource();
		
		if ($data[$name] === $value) return $this;
		
		$data[$name] = $value;
		
		return $this->_updateResource($data);
	}
		
	protected function _updateResource(Array $data) {
		$this->_data = $data;
		
		if ($this->_update) $this->_resource
			->setData($data)
			->update();
		
		return $this;
	}
	
	protected function _deleteResource() {
		$this->_resource->delete();
		
		return $this;
	}
	
	
	protected function _getProjection(Callable $fn, Array $props = []) {
		$data =& $this->_useResource();
		$res = [];
		
		foreach ($props as $prop) $res[$prop] = $fn($data, $prop);
		
		return $res;
	}
	
	
	public function isLive() {
		return $this->_resource->isLive();
	}
	
	
	public function deferUpdates() {
		$this->_update = false;
		
		return $this;
	}
	
	public function update() {
		$this->_resource
			->setData($this->_data)
			->update();
		
		$this->_update = true;
		
		return $this;
	}
}
