<?php

namespace lola\type;



class NamedQueue {
	
	const VERSION = '0.1.5';
	
	
	
	protected $_keys = null;
	protected $_cbs = null;
	
	
	/**
	 * Creates a new instance
	 */
	public function __construct(Array $callbacks = null) {
		$this->_keys = [];
		$this->_cbs = [];
		
		if (!is_null($callbacks)) $this->appendMany($callbacks);
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
	
	public function get($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		$index = array_search($name, $this->_keys);
		
		if ($index === false) throw new \ErrorException();
		
		return $this->_cbs[$index];
	}
	
	public function getMany(Array $names = []) {
		if (empty($names)) return array_combine($this->_keys, $this->_cbs);
		
		$res = [];
		
		foreach($names as $name) {
			$index = array_search($name, $this->_keys);
			
			if ($index === false) throw new \ErrorException();
			
			$res[$name] = $this->_cbs[$index];
		}
		
		return $res;
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
	 * Inserts $callbacks before the callback named $before
	 * @param array $callbacks The callbacks
	 * @param string $before The target identifier
	 * @return NamedQueue
	 * @throws \ErrorException if $before is not a nonempty string or is a registered name
	 * @throws \ErrorException if any $callbacks key is a registered name
	 * @throws \ErrorException if any $callbacks value is not a callable
	 */
	public function insertMany(Array $callbacks, $before) {
		if (!is_string($before) ||empty($before)) throw new \ErrorException();
		
		$keys = $this->_keys;
		$index = array_search($before, $keys);
		
		if ($index === false) throw new \ErrorException();
		
		$names = [];
		$cbs = [];
		
		foreach ($callbacks as $name => $cb) {
			if (in_array($name, $keys) || !is_callable($cb)) throw new \ErrorException();
			
			$names[] = $name;
			$cbs[] = $cb;
		}
		
		array_splice($this->_keys, $index, 0, $names);
		array_splice($this->_cbs, $index, 0, $cbs);
		
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
	 * Appends $callbacks
	 * @param array $callbacks The callbacks
	 * @return NamedQueue
	 * @throws \ErrorException if any array key is a registered name
	 * @throws \ErrorException if any array value is not a callable
	 */
	public function appendMany(Array $callbacks) {
		$keys = $this->_keys;
		$cbs = $this->_cbs;
		
		foreach ($callbacks as $name => $cb) {
			if (
				in_array($name, $keys) ||
				!is_callable($cb)
			) throw new \ErrorException();
			
			$keys[] = $name;
			$cbs[] = $cb;
		}
		
		$this->_keys = $keys;
		$this->_cbs = $cbs;
		
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
		
		array_splice($this->_keys, $index, 1, $name);
		array_splice($this->_cbs, $index, 1, $cb);
		
		return $this;
	}
	
	
	public function merge(...$queues) {
		$keys = $this->_keys;
		$cbs = $this->_cbs;
		
		foreach ($queues as $queue) {
			if (!($queue instanceof NamedQueue)) throw new \ErrorException();
			
			$qKeys = $queue->_keys;
			$qCbs = $queue->_cbs;
			
			foreach ($qKeys as $qIndex => $name) {
				$cb = $qCbs[$qIndex];
				
				$index = array_search($name, $keys);
				
				if ($index === false) {
					$keys[] = $name;
					$cbs[] = $cb;
				}
				else {
					array_splice($keys, $index, 1, $name);
					array_splice($cbs, $index, 1, $cb);
				}
			}
		}
		
		$this->_keys = $keys;
		$this->_cbs = $cbs;
		
		return $this;
	}
	
	public function filter(Array $names) {
		$refKeys = $this->_keys;
		$refCbs = $this->_cbs;
		$keys = [];
		$cbs = [];
		
		foreach ($names as $key => $name) {
			if (is_int($key)) $key = $name;
			
			$index = array_search($key, $refKeys);
			
			if ($index === false) throw new \ErrorException();
			
			$keys[] = $name;
			$cbs[] = $refCbs[$index];
		}
		
		$this->_keys = $keys;
		$this->_cbs = $cbs;
		
		return $this;
	}
}
