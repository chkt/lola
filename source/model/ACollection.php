<?php

namespace lola\model;

use lola\type\AIterateable;

use lola\model\IResourceCollection;



abstract class ACollection
extends AIterateable
{
	
	const VERSION = '0.2.4';
	
	
	
	private $_resource = null;
	private $_factory = null;
		
	
	public function __construct(IResourceCollection $resource, Callable $itemFactory) {
		parent::__construct();
		
		$this->_resource = $resource;
		$this->_factory = $itemFactory;
		
		$this->_length = $resource->getLength();
	}
	
	
	protected function& _useItem($index) {
		$items =& $this->_items;
		
		if (!array_key_exists($index, $items)) {
			$resource =& $this->_resource->useItem($index);
			$items[$index] =& call_user_func_array($this->_factory, [ & $resource ]);
		}
		
		return $items[$index];
	}
	
	
	public function isLive() {
		return $this->_resource->isLive();
	}
	
	
	public function& hasItem(IResourceQuery $query) {
		return $this->_resource->getIndexOf($query) !== -1;
	}
	
	public function& useItem(IResourceQuery $query) {
		$index = $this->_resource->getIndexOf($query);
		$null = null;
		
		return $index !== -1 ? $this->_useItem($index) : $null;
	}
	
	
	public function update() {
		$this->_resource->update();
		
		return $this;
	}
}
