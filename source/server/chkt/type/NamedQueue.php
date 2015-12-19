<?php

namespace chkt\type;



class NamedQueue {
	
	const VERSION = '0.0.8';
	
	
	
	protected $_keys = null;
	protected $_cbs = null;
	
	
	/**
	 * Creates a new instance
	 */
	public function __construct() {
		$this->_keys = [];
		$this->_cbs = [];
	}
	
	
	/**
	 * Returns the number of registered callbacks
	 * @return int
	 */
	public function getLength() {
		return count($this->_cbs);
	}
	
	
	/**
	 * Returns true if the instance has a callback with identifier $name, false otherwise
	 * @return boolean
	 */
	public function has($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		return in_array($this->_keys, $name);
	}
	
	
	/**
	 * Inserts $cb before the callback named $before
	 * @param string $name The callback identifier
	 * @param callable $cb The callback function
	 * @param string $before The target identifier
	 * @return NamedQueue
	 * @throws \ErrorException if $name is not a nonempty string or is a registered name
	 * @throws \ErrorException if $before is not a nonempty string or is not a registered name
	 */
	public function insert($name, Callable $cb, $before) {
		if (
			!is_string($name) || empty($name) || in_array($name, $this->_keys) ||
			!is_string($before) || empty($before)
		) throw new \ErrorException();
		
		$index = array_search($before, $this->_keys);
		
		if ($index === false) throw new \ErrorException();
		
		array_splice($this->_keys, $index, 0, $name);
		array_splice($this->_cbs, $index, 0, $cb);
		
		return $this;
	}
	
	/**
	 * Appends $cb
	 * @param string $name The callback identifier
	 * @param callable $cb The callback function
	 * @return NamedQueue
	 * @throws \ErrorException if $name is not a nonempty string or is a registered name
	 */
	public function append($name, Callable $cb) {
		if (!is_string($name) || empty($name) || in_array($name, $this->_keys)) throw new \ErrorException();
		
		$this->_keys[] = $name;
		$this->_cbs[] = $cb;
		
		return $this;
	}
	
	/**
	 * Removes the callback identified by $name
	 * @param string $name The callback identifier
	 * @return NamedQueue
	 * @throws \ErrorException if $name is not a nonempty string or is not a registered name
	 */
	public function remove($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		$index = array_search($name, $this->_keys);
		
		if ($index === false) throw new \ErrorException();
		
		array_splice($this->_keys, $index, 1);
		array_splice($this->_cbs, $index, 1);
		
		return $this;
	}
	
	/**
	 * Replaced the callback identified by $name with $cb
	 * @param string $name The callback identifier
	 * @param callable $cb The callback function
	 * @return NamedQueue
	 * @throws \ErrorException if $name is not a nonempty string or is not a registered name
	 */
	public function replace($name, Callable $cb) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		$index = array_search($name, $this->_keys);
		
		if ($index === false) throw new \ErrorException();
		
		array_splice($this->_keys, $index, 1, $cb);
		
		return $this;
	}
}
