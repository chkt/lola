<?php

namespace chkt\type;



trait TDictionary {
	
	/**
	 * The instance id increment value
	 * @var Uint
	 */
	static private $_itemIncrement = 0;
	/**
	 * The instance hashmap
	 * @var Array
	 */
	static private $_itemInstance  = [];
	
	
	/**
	 * The instance id
	 * @var String
	 */
	private $_itemId;
	/**
	 * The instance item type
	 * @var *
	 */
	private $_itemType;

	/**
	 * The instance items
	 * @var Array
	 */
	private $_item;
	/**
	 * The instance item number
	 * @var UInt
	 */
	private $_itemLen;
	

	
	static public function getItemsOfKey($key) {
		if (!is_string($key)) throw new \ErrorException();
		
		$res = [];
		
		foreach (self::$_itemInstance as $ins) {
			foreach ($ins as $iKey => $iItem) {
				if ($iKey === $key && !in_array($iItem)) $res[] = $iItem;
			}
		}
		
		return $res;
	}
	
	
	/**
	 * Returns instances filtered by <code>$key</code> and <code>$item</code>
	 * @param String $key  The optional filter key
	 * @param *      $item The optional filter item
	 * @return Array
	 * @throws ErrorException if <code>$key</code> is not a <code>String</code>
	 */
	static public function getByKeyItem($key = '', $item = null) {
		if (!is_string($key)) throw new ErrorException();
		
		$res = [];
		
		foreach (self::$_itemInstance as $ins) {
			foreach($ins->_item as $iKey => $iItem) {
				if (($key === '' || $iKey === $key) && ($item === null || $iItem === $item)) $res[] = &$ins;
			}
		}
		
		return $res;
	}
	
	/**
	 * Returns instances filtered by <code>$key</code>
	 * @param String $key The optional filter key
	 * @return Array
	 */
	static public function getByKey($key) {
		return self::getByKeyItem($key);
	}
	
	/**
	 * Returns instances filtered by <code>$item</code>
	 * @param * $item The optional filter item
	 * @return Array
	 */
	static public function getByItem($item) {
		return self::getByKeyItem('', $item);
	}
	
	
	
	/**
	 * Initializes the trait properties
	 */
	private function _init() {
		if (!isset($this->_itemId)) {
			$id = (string) (++self::$_itemIncrement);

			self::$_itemInstance[$id] = $this;
			
			$this->_itemId = $id;
		}
		
		$this->_itemType = '';
		
		$this->_item     = [];
		$this->_itemLen  = 0;
	}
	
	/**
	 * Terminates the trait properties
	 */
	private function _terminate() {
		$id = $this->_itemId;
		
		unset($this->_itemId);
		
		unset(self::$_itemInstance[$id]);
	}
	
	
	/**
	 * Returns <code>true</code> if the type of <code>$item</code> corresponds with the expected type, <code>false</code> otherwise
	 * @param * $item The item
	 * @return boolean
	 */
	private function _isValidItem($item) {
		$type = $this->_itemType;
		
		if ($type instanceof ReflectionClass) return $type->isInstance($item);
		else switch ($type) {
			case ''        : return true;
			case 'boolean' : return is_bool($item);
			case 'integer' : return is_int($item);
			case 'float'   : return is_float($item);
			case 'string'  : return is_string($item);
			case 'array'   : return is_array($item);
			default        : return false;
		}
	}
	
	
	
	/**
	 * Sets the accepted item type
	 * @param String $type The type string
	 * @throws ErrorException if <code>$type</code> is not a <code>String</code>
	 */
	protected function _setItemType($type = '') {
		if (!is_string($type)) throw new ErrorException();
		
		switch ($type) {
			case '' :
			case 'boolean' :
			case 'integer' :
			case 'float' :
			case 'string' :
			case 'array' :
				$this->_itemType = $type;
				
				return;
		}
		
		$this->_itemType = new ReflectionClass($type);
	}
	
	
	/**
	 * Returns the item type
	 * @return *
	 */
	public function getItemType() {
		return $this->_itemType;
	}
	
	
	/**
	 * Returns the number of items
	 * @return Uint
	 */
	public function getNumItems() {
		return $this->_itemLen;
	}
	
	
	/**
	 * Returns <code>true</code> if <code>$key</code> is in the instance, <code>false</code> otherwise
	 * @param String $key The key
	 * @return Boolean
	 * @throws ErrorException if <code>$key</code> is not a <code>String</code>
	 */
	public function hasItem($key) {
		if (!is_string($key) || empty($key)) throw new ErrorException();
		
		return array_key_exists($key, $this->_item);
	}
	
	
	/**
	 * Sets a key
	 * @param String $key  The key
	 * @param *      $item The item
	 * @throws ErrorException if <code>$key</code> is not a <em>nonempty</em> <code>String</code>
	 * @throws ErrorException if <code>$item</code> is not a valid item
	 */
	public function setItem($key, $item) {
		if (
			!is_string($key) || empty($key) ||
			!$this::_isValidItem($item)
		) throw new ErrorException();
		
		if (!array_key_exists($key, $this->_item)) $this->_itemLen += 1;
		
		$this->_item[$key] = $item;
	}
	
	/**
	 * Returns the item referenced by <code>$key</code>
	 * @param String $key The key
	 * @return *
	 * @throws ErrorException if <code>$key</code> is not a <em>nonempty</em> <code>String</code>
	 */
	public function getItem($key) {
		if (!is_string($key) || empty($key)) throw new ErrorException();
		
		return array_key_exists($key, $this->_item) ? $this->_item[$key] : null;
	}
	
	/**
	 * Removes a key
	 * @param String $key The key
	 * @throws ErrorException if <code>$key</code> is not a <em>nonempty</em> <code>String</code>
	 */
	public function removeItem($key) {
		if (!is_string($key) || empty($key)) throw new ErrorException();
		
		if (array_key_exists($key, $this->_item)) {
			$this->_itemLen -= 1;
			
			unset($this->_item[$key]);
		}
	}
	
	
	/**
	 * Sets the keys referenced by <code>$map</code>
	 * @param Array $map The key-item map
	 */
	public function setItems(array $map) {
		foreach ($map as $key => $value) $this->setItem($key, $value);
	}
	
	/**
	 * Returns an Array of the items referenced by <code>$keys</code>
	 * @param Array $keys The keys
	 * @return Array
	 */
	public function getItems(array $keys) {
		$res = [];
		
		foreach ($keys as $key) $res[$key] = $this->getItem($key);
		
		return $res;
	}
	
	/**
	 * Removes the keys referenced by <code>$keys</code>
	 * @param Array $keys The keys
	 */
	public function removeItems(array $keys) {
		foreach ($keys as $key) $this->removeItem($key);
	}
	
	
	/**
	 * Returns an <code>Array<code> of the keys of the instance
	 * @return Array
	 */
	public function getAllKeys() {
		return array_keys($this->_item);
	}
	
	/**
	 * Returns an <code>Array</code> of all keys and items of the instance
	 * @return Array
	 */
	public function getAllItems() {
		return $this->_item;
	}
}