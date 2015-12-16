<?php

namespace chkt\type;



class Collection {
	
	protected $_items = null;
	
	
	public function __construct() {
		$this->_items = [];
	}
	
	
	public function hasItems() {
		return (bool) count($this->_items);
	}
	
	
	public function getItems(Array $names = null) {
		if (is_null($names)) return $this->_items;
		
		$key = array_combine($names, array_fill(0, count($names), 1));
		
		return array_intersect_key($this->_items, $key);
	}
	
	public function setItems(Array $items) {
		$this->_items = array_merge($this->_items, $items);
		
		return $this;
	}
	
	
	public function hasItem($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		return array_key_exists($name, $this->_items);
	}
	
	
	public function getItem($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		return array_key_exists($name, $this->_items) ? $this->_items[$name] : null;
	}
	
	public function setItem($name, $item) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		$this->_items[$name] = $item;
		
		return $this;
	}
	
	
	public function& useItems() {
		return $this->_items;
	}
}
