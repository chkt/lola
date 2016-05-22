<?php

namespace lola\type;

use lola\type\IIterateable;



abstract class AIterateable
implements IIterateable
{
	const VERSION = '0.2.1';
	
	
	
	protected $_items = null;
	
	protected $_cursor = 0;
	protected $_length = 0;
	
	
	public function __construct() {
		$this->_items = [];
		
		$this->_cursor = 0;
		$this->_length = 0;
	}
	
	
	protected function& _useItem($index) {
		return $this->_items[$index];
	}
	
	
	public function getIndex() {
		return $this->_cursor;
	}
	
	public function getLength() {
		return $this->_length;
	}
	
	
	public function& useIndex($index) {
		if (!is_int($index) || $index < 0) throw new \ErrorException();
		
		if ($index >= $this->_length) {
			$null = null;
			
			return $null;
		}
		
		$this->_cursor = $index;
		
		return $this->_useItem($index);
	}
	
	public function& useOffset($offset) {
		if (!is_int($offset)) throw new \ErrorException();
		
		$cursor = $this->_cursor + $offset;
		
		if ($cursor < 0 || $cursor >= $this->_length) {
			$null = null;
			
			return $null;
		}
		
		$this->_cursor = $cursor;
		
		return $this->_useItem($cursor);
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
}
