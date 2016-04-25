<?php

namespace lola\type;



abstract class AQueue {
	
	const VERSION = '0.1.7';
	
	
	
	protected $_items = null;
	
	
	public function __construct(Array $cbs = null) {
		$this->_items = [];
		
		if (!is_null($cbs)) $this->appendMany($cbs);
	}
	
	
	public function getLength() {
		return count($this->_items);
	}
	
	
	public function has(callable $cb) {
		return in_array($cb, $this->_items);
	}
	
	
	public function insert(callable $cb, callable $before) {
		$index = array_search($before, $this->_items);
		
		if ($index === false) throw new \ErrorException();
		
		array_splice($this->_items, $index, 0, $cb);
		
		return $this;
	}
	
	public function insertMany(array $cbs, callable $before) {
		$items = $this->_items;
		$index = array_search($before, $items);
		
		if ($index === false) throw new \ErrorException();
				
		foreach ($cbs as $item) {
			if (in_array($item, $items) || !is_callable($item)) throw new \ErrorException();
			
			$items[] = $item;
		}
		
		$this->_items = $items;
		
		return $this;
	}
	
	public function append(callable $cb) {
		if (in_array($cb, $this->_items)) throw new \ErrorException();
		
		$this->_items[] = $cb;
		
		return $this;
	}
	
	public function appendMany(array $cbs) {
		$items = $this->_items;
		
		foreach ($cbs as $cb) {
			if (in_array($cb, $items) || !is_callable($cb)) throw new \ErrorException();
			
			$items[] = $cb;
		}
		
		$this->_items = $items;
		
		return $this;
	}
	
	public function remove(callable $cb) {
		$index = array_search($cb, $this->_items);
		
		if ($index === false) throw new \ErrorException();
		
		array_splice($this->_items, $index, 1);
		
		return $this;
	}
	
	public function removeMany(array $cbs) {
		$items = $this->_items;
		
		foreach ($cbs as $cb) {
			$index = array_search($cb, $items);
			
			if ($index === false) throw new \ErrorException();
			
			array_splice($items, $index, 1);
		}
		
		$this->_items = $items;
		
		return $this;
	}
}
