<?php

namespace lola\type;

use lola\type\AIterateable;



/**
 * Generic selector
 */
class Selector
extends AIterateable
{
	
	/**
	 * The version string
	 */
	const VERSION = '0.3.1';
	
	/**
	 * The parent key
	 */
	const KEY_PARENT = '@';
	/**
	 * The each item key
	 */
	const KEY_EACH = '*';
	
	
	
	/**
	 * Returns a copy of $source
	 * @param Selector $source
	 * @return Selector
	 */
	static public function Copy(Selector $source) {		
		$res = new Selector();
		
		$res->_items = $source->_items;
		$res->_ancestors = $source->_ancestors;
		
		$res->_cursor = $source->_cursor;
		$res->_length = $source->_length;
		
		return $res;
	}
	
	/**
	 * Returns an instance representing the selection of source at the cursor position
	 * @param Selector $source
	 * @return Selector
	 */
	static public function One(Selector& $source) {
		$index = $source->_cursor;
		
		return new Selector(
			$source->_items[$index],
			$source->_ancestors[$index]
		);
	}
	
	
	/**
	 * Returns the key at $index
	 * @param array $item - The source array
	 * @param int $index - The index
	 * @return string
	 * @throws \ErrorException - if $index is out of bounds
	 */
	static private function _indexToName(array $item, $index) {
		$keys = array_keys($item);
		$num = count($keys);
		
		if ($index < 0) $index += $num;
		
		if ($index < 0 || $index >= $num) throw new \ErrorException('SELECT: Missing prop');
		
		return $keys[$index];
	}
	
	
	
	/**
	 * The selection ancestors
	 * @var array
	 */
	protected $_ancestors = null;
	
	
	/**
	 * Creates a new instance
	 * @param array $data - The selection
	 * @param array $ancestors - The ancestor selections of the selection
	 * @throws \ErrorException - if $data and $ancestors are of differing length
	 */
	public function __construct(array& $data = [], array $ancestors = []) {		
		parent::__construct();
		
		$this->_items = [ & $data ];
		$this->_ancestors = [ & $ancestors ];
		
		$this->_length = 1;
	}
	
	
	/**
	 * Selects the $name property for the active item
	 * @param mixed $name - The property name
	 * @throws \ErrorException - if $name is not a property of the active item
	 */
	private function _one($name) {
		$index = $this->_cursor;
		$selection =& $this->_items;
		$ancestors =& $this->_ancestors;
		
		$item =& $selection[$index];
		
		if (is_numeric($name)) $name = self::_indexToName($item, (int) $name);
		else if (!array_key_exists($name, $item)) throw new \ErrorException('SELECT: Missing prop');
		
		$selection[$index] =& $item[$name];
		$ancestors[$index][] =& $item;
	}
	
	
	/**
	 * Selects each property of the active item
	 * @throws \ErrorException - if the active item has no properties
	 */
	private function _each() {
		$index = $this->_cursor;
		$selection =& $this->_items;
		$ancestors =& $this->_ancestors;
		
		$item =& $selection[$index];
		
		if (count($item) === 0) throw new \ErrorException('SELECT: No props');
		
		$chain =& $ancestors[$index];
		$chain[] =& $item;
		
		array_splice($selection, $index, 1);
		array_splice($ancestors, $index, 1);
		$diff = -1;
		
		foreach ($item as $child) {
			$selection[] =& $child;
			$ancestors[] = $chain;
			$diff += 1;
		}
		
		$this->_count += $diff;
	}
	
	
	/**
	 * Selects the parent selection of the current selection
	 * @throws \ErrorException - if no parent selection exists
	 */
	private function _parent() {
		$index = $this->_cursor;
		$selection =& $this->_items;
		$ancestors =& $this->_ancestors;
		
		$last = count($ancestors[$index]) - 1;
		
		if ($last < 0) throw new \ErrorException('SELECT: No parent');
		
		$selection[$index] =& $ancestors[$index][$last];
		
		array_pop($ancestors[$index]);
	}
	
	
	/**
	 * Returns the selection represented by $selector
	 * @param array $selector - The selector
	 * @return $this
	 */
	public function query(array $selector) {
		$index =& $this->_cursor;
		$count =& $this->_length;
		
		foreach ($selector as $key) {
			for ($index = $count - 1; $index > -1; $index -= 1) {
				switch ($key) {
					case self::KEY_EACH :
						$this->_each();
						
						continue;
						
					case self::KEY_PARENT :
						$this->_parent();
						
						continue;
						
					default : $this->_one($key);
				}
			}
		}
		
		$this->_cursor = 0;
		
		return $this;
	}
}
