<?php

namespace chkt\type;



class Collection {
	
	const VERSION = '0.1.6';
	
	
	
	/**
	 * The collection items
	 * @var array
	 */
	protected $_items = null;
	
	
	/**
	 * Creates an instance
	 * @param array $items
	 */
	public function __construct(Array $items = []) {
		$this->_items = $items;
	}
	
	
	/**
	 * Returns true if the instance has items, false otherwise
	 * @return bool
	 */
	public function hasItems() {
		return (bool) count($this->_items);
	}
	
	
	/**
	 * Gets the items, optionally filtered by name
	 * @param array $names The name filter
	 * @return array
	 */
	public function getItems(Array $names = null) {
		if (is_null($names)) return $this->_items;
		
		$key = array_combine($names, array_fill(0, count($names), 1));
		
		return array_intersect_key($this->_items, $key);
	}
	
	/**
	 * Sets the items in $items
	 * @param array $items
	 * @return Collection
	 */
	public function setItems(Array $items) {
		$this->_items = array_merge($this->_items, $items);
		
		return $this;
	}
	
	
	/**
	 * Returns true if the instance has item $name, false otherwise
	 * @param string $name
	 * @return bool
	 * @throws \ErrorException if $name is not a nonempty string
	 */
	public function hasItem($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		return array_key_exists($name, $this->_items);
	}
	
	
	/**
	 * Gets item $name
	 * @param string $name
	 * @return mixed
	 * @throws \ErrorException if $name is not a nonempty string
	 */
	public function getItem($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		return array_key_exists($name, $this->_items) ? $this->_items[$name] : null;
	}
	
	/**
	 * Returns a reference to item $name
	 * @param string $name
	 * @return mixed
	 * @throws \ErrorException if $name is not a nonempty string
	 */
	public function& useItem($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		return array_key_exists($name, $this->_items) ? $this->_items[$name] : ($null = null);
	}
	
	/**
	 * Sets the item $name
	 * @param string $name
	 * @param mixed $item
	 * @return Collection
	 * @throws \ErrorException if $name is not a nonempty string
	 */
	public function setItem($name, $item) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		$this->_items[$name] = $item;
		
		return $this;
	}
	
	
	/**
	 * Returns a reference to the items
	 * @return array
	 */
	public function& useItems() {
		return $this->_items;
	}
}
