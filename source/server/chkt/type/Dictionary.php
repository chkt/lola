<?php

namespace lola\type;

use lola\type\TDictionary;



class Dictionary {
	use TDictionary;
	
	
	
	/**
	 * Returns an instance from <code>$items</code>
	 * @param Array      $items  The dictionary items
	 * @param String     $type   The dictionary type string
	 * @param Dictionary $target The target instance
	 * @return Dictionary
	 */
	static public function Items(array $items, $type = '', Dictionary $target = null) {
		if (is_null($target)) $target = new Dictionary($type);
		else $target->__construct($type);
		
		$target->setItems($items);
		
		return $target;
	}
	
	
	
	/**
	 * Creates a (new) instance
	 * @param String $type The dictionary type string
	 */
	public function __construct($type = '') {
		$this->_init();
		$this->_setItemType($type);
	}
	
	/**
	 * Destroys the instance
	 */
	public function __destruct() {
		$this->_terminate();
	}
	
	
	/**
	 * Returns a <em>json</em> representation of the instance
	 * @return Array
	 */
	public function toJSON() {
		return $this->_item;
	}
}