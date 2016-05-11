<?php

namespace lola\model;

use lola\model\ProxyResource;
use lola\model\ProxyResourceQueue;



final class ProxyResourceDriver
{
	
	const VERSION = '0.1.8';
	
	
	private $_resource = null;
	
	private $_create = null;
	private $_update = null;
	private $_delete = null;
	
	
	public function __construct() {
		$this->_resource = [];
		
		$this->_create = [];
		$this->_update = [];
		$this->_delete = [];
	}
	
	
	public function register(
		ProxyResource $resource,
		ProxyResourceQueue& $create,
		ProxyResourceQueue& $update,
		ProxyResourceQueue& $delete
	) {
		$this->_resource[] = $resource;
		
		$this->_create[] =& $create;
		$this->_update[] =& $update;
		$this->_delete[] =& $delete;
	}
	
	
	public function hasResource(ProxyResource $resource) {
		return array_search($resource, $this->_resource) !== false;
	}
	
	private function _getResourceIndex(ProxyResource $resource) {
		$index = array_search($resource, $this->_resource);
		
		if ($index === false) throw new \ErrorException();
		
		return $index;
	}
	
	
	public function hasCreateListener(ProxyResource $resource, callable $cb) {
		$index = $this->_getResourceIndex($resource);
		
		return $this->_create[$index]->has($cb);
	}
	
	public function addCreateListener(ProxyResource $resource, callable $cb) {
		$index = $this->_getResourceIndex($resource);
		
		$this->_create[$index]->append($cb);
		
		return $this;
	}
	
	public function removeCreateListener(ProxyResource $resource, callable $cb) {
		$index = $this->_getResourceIndex($resource);
		
		$this->_create[$index]->remove($cb);
		
		return $this;
	}
	
	
	public function hasUpdateListener(ProxyResource $resource, callable $cb) {
		$index = $this->_getResourceIndex($resource);
		
		return $this->_update[$index]->has($cb);
	}
	
	public function addUpdateListener(ProxyResource $resource, callable $cb) {
		$index = $this->_getResourceIndex($resource);
		
		$this->_update[$index]->append($cb);
		
		return $this;
	}
	
	public function removeUpdateListener(ProxyResource $resource, callable $cb) {
		$index = $this->_getResourceIndex($resource);
		
		$this->_update[$index]->remove($cb);
		
		return $this;
	}
	
	
	public function hasDeleteListener(ProxyResource $resource, callable $cb) {
		$index = $this->_getResourceIndex($resource);
		
		return $this->_delete[$index]->has($cb);
	}
	
	public function addDeleteListener(ProxyResource $resource, callable $cb) {
		$index = $this->_getResourceIndex($resource);
		
		$this->_delete[$index]->append($cb);
		
		return $this;
	}
	
	public function removeDeleteListener(ProxyResource $resource, callable $cb) {
		$index = $this->_getResourceIndex($resource);
		
		$this->_delete[$index]->append($cb);
		
		return $this;
	}
}
