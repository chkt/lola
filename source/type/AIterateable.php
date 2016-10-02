<?php

namespace lola\type;

use lola\type\IIterateable;



/**
 * Abstract iterateable
 */
abstract class AIterateable
implements IIterateable
{
	/**
	 * The version string
	 */
	const VERSION = '0.2.1';
	
	
	
	/**
	 * The iterateable items
	 * @var array
	 */
	protected $_items = null;
	
	/**
	 * The iteration cursor position
	 * @var int
	 */
	protected $_cursor = 0;
	/**
	 * The iterateable length
	 * @var type 
	 */
	protected $_length = 0;
	
	
	/**
	 * Creates a new instance
	 */
	public function __construct() {
		$this->_items = [];
		
		$this->_cursor = 0;
		$this->_length = 0;
	}
	
	
	/**
	 * Returns a reference to the item at $index
	 * @param int $index - The item index
	 * @return mixed
	 */
	protected function& _useItem($index) {
		return $this->_items[$index];
	}
	
	
	/**
	 * Returns the current cursor position
	 * @return int
	 */
	public function getIndex() {
		return $this->_cursor;
	}
	
	/**
	 * Returns the current iterateable length
	 * @return int
	 */
	public function getLength() {
		return $this->_length;
	}
	
	
	/**
	 * Returns a reference to the item at $index
	 * @param int $index - The iteration index
	 * @return mixed
	 * @throws \ErrorException - if $index is not a positive integer
	 */
	public function& useIndex($index) {
		if (!is_int($index) || $index < 0) throw new \ErrorException();
		
		if ($index >= $this->_length) {
			$null = null;
			
			return $null;
		}
		
		$this->_cursor = $index;
		
		return $this->_useItem($index);
	}
	
	/**
	 * Returns a reference to the item relative to the current iteration position by $offset
	 * @param int $offset - The offset to the iteration index
	 * @return mixed
	 * @throws \ErrorException - if $offset is not an integer
	 */
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
	
	
	/**
	 * Returns a reference to the first item
	 * @return mixed
	 */
	public function& useFirst() {
		return $this->useIndex(0);
	}
	
	/**
	 * Returns a reference to the previous item relative to the iteration index
	 * @return mixed
	 */
	public function& usePrev() {
		return $this->useOffset(-1);
	}
	
	/**
	 * Returns a reference to the next item relative to the iteration index
	 * @return mixed
	 */
	public function& useNext() {
		return $this->useOffset(1);
	}
	
	/**
	 * Return a reference to the last item
	 * @return mixed
	 */
	public function& useLast() {
		return $this->useIndex($this->_length - 1);
	}
}
