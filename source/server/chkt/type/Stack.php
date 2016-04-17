<?php

namespace lola\type;



class Stack {
	
	protected $_items = null;
	
	
	public function __construct() {
		$this->_items = [];
	}
	
	
	public function hasItem() {
		return (bool) count($this->_items);
	}
	
	
	public function pushItem($item) {
		$this->_items[] = $item;
		
		return $this;
	}
	
	public function popItem() {
		return array_pop($this->_items);
	}
	
	
	public function& useItem() {
		$items =& $this->_items;
		$len = count($items);
		
		return $len !== 0 ? $items[$len - 1] : null;
	}
}
