<?php

namespace lola\model;

use lola\model\IModel;
use lola\model\IResource;

use lola\model\NoPropertyException;



abstract class AModel
implements IModel
{
	
	const VERSION = '0.2.1';
	
	
	
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
	
	
	private function& _useProperty(array& $data, $key) {
		$segs = explode('.', $key);		
		
		for ($i = 0, $l = count($segs); $i < $l; $i += 1) {
			$seg = $segs[$i];
			
			if (strlen($seg) === 0) throw new \ErrorException('INVSEG:' . $key);
			
			if (ctype_digit($seg)) $seg = (int) $seg;
			
			if (!is_array($data)) throw new \ErrorException('INVPROP:' . $key);
			else if (!array_key_exists($seg, $data)) throw new NoPropertyException($data, array_slice($segs, $i));
			
			$data =& $data[$seg];
		}
		
		return $data;
	}
	
	
	protected function _hasResourceProperty($key) {
		if (!is_string($key) || empty($key)) throw new \ErrorException();
		
		try {
			$this->_useProperty($this->_useResource(), $key);
		}
		catch (NoPropertyException $ex) {
			return false;
		}
		
		return true;
	}
	
	protected function& _useResourceProperty($key) {
		if (!is_string($key) || empty($key)) throw new \ErrorException();
		
		return $this->_useProperty($this->_useResource(), $key);
	}
	
	protected function _setResourceProperty($name, $value) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		$prop =& $this->_useProperty($this->_useResource(), $name);
		
		if ($prop === $value) return $this;
		
		$prop = $value;

		if ($this->_update) $this->_updateResource();
		
		return $this;
	}
	
	protected function _addResourceProperty($name, $value) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		try {
			$this->_useProperty($this->_useResource(), $name);
			
			throw new \ErrorException('HASPROP:' . $name);
		}
		catch (NoPropertyException $ex) {
			$prop =& $ex->useResolvedProperty();
			$path = $ex->getMissingPath();
		}
		
		foreach ($path as $seg) {
			$prop[$seg] = [];
			$prop =& $prop[$seg];
		}
		
		$prop = $value;
		
		if ($this->_update) $this->_updateResource();
		
		return $this;
	}
	
	protected function _removeResourceProperty($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		$index = strrpos($name, '.');
		
		if ($index === false) unset($this->_useResource()[$name]);
		else {
			$path = substr($name, 0, $index);
			$prop = substr($name, $index + 1);
			
			if (empty($prop)) throw new \ErrorException('INVSEG');
			
			unset($this->_useProperty($this->_useResource(), $path)[$prop]);
		}
		
		if ($this->_update) $this->_updateResource();
		
		return $this;
	}
	
	
	protected function _updateResource() {		
		if (!is_null($this->_data)) $this->_resource
			->setData($this->_data)
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
	
	
	public function wasCreated() {
		return $this->_resource->wasCreated();
	}
	
	public function wasRead() {
		return $this->_resource->wasRead();
	}
	
	
	public function deferUpdates() {
		$this->_update = false;
		
		return $this;
	}
	
	public function update() {
		$this->_updateResource();
		
		$this->_update = true;
		
		return $this;
	}
}
