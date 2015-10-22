<?php

namespace chkt\type;



trait TInjectable {
	
	static private $_tInjectableAll = [];
	
	
	private $_tInjectableIns = [];
	
	
	
	/**
	 * Creates a new instance and injects <code>$deps</code>
	 * @param Array $deps The injected
	 * @param array $args The constructor arguments
	 * @return TInjectable
	 */
	static public function InjectNew(Array $deps, Array $args) {
		$ins = new self(...$args);
		
		return $ins->setInjecteds($deps);
	}
	
	/**
	 * Calls a <em>factory</em> and injects <code>$deps</code>
	 * @param String $method The factory method
	 * @param Array $deps The injected
	 * @param Array $args The constructor arguments
	 * @return TInjectable
	 * @throws \ErrorException if <code>$method</code> is not a <em>nonempty</em> <code>String</code>
	 * @throws \ErrorException if <code>$method</code> is not a <em>factory</em>
	 */
	static public function InjectCall($method, Array $deps, Array $args) {
		$class = new \ReflectionClass(self);
		
		if (!$class->hasMethod($method)) throw new \ErrorException();
		
		$ins = call_user_func_array([$class, $method], $args);
		
		if (!($ins instanceof self)) throw new \ErrorException();
		
		return $ins->setInjecteds($deps);
	}
	
	

	/**
	 * <code>true</code> if injected referenced by <code>$name</code> exists, <code>false</code> otherwise
	 * @param String $name The name
	 * @return Boolean
	 * @throws \ErrorException if <code>$name</code> is not a <em>nonempty</em> <code>String</code>
	 */
	static public function hasInjectedAll($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		return array_key_exists($name, self::$_tInjectableAll);
	}
	
	/**
	 * Sets injection <code>$name</code>
	 * @param String $name     The name
	 * @param *      $instance The injection
	 * @throws \ErrorException if <code>$name</code> is not a <em>nonempty</em> <code>String</code>
	 */
	static public function setInjectedAll($name, $instance) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		self::$_tInjectableAll[$name] = $instance;
	}
	
	
	/**
	 * REVIEW required to allow access to injections by static methods - find a better solution
	 * Returns an alias to the injection referenced by <code>$name</code>
	 * @param string $name The name
	 * @return mixed
	 * @throws \ErrorException if <code>$name</code> is not a <em>nonempty</em> <code>String</code>
	 */
	static protected function &_useInjectedAll($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		$res = array_key_exists($name, self::$_tInjectableAll) ? self::$_tInjectableAll[$name] : null;
		
		return $res;
	}
	
	
	/**
	 * Returns <code>true</code> if the injection referenced by <code>$name</code> exists, <code>false</code> otherwise
	 * @param String $name The name
	 * @return Boolean
	 * @throws \ErrorException if <code>$name</code> is not a <em>nonempty</em> <code>String</code>
	 */
	public function hasInjected($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		return array_key_exists($name, $this->_tInjectableIns) || array_key_exists($name, self::$_tInjectableAll);
	}
	
	
	/**
	 * Sets injection <code>$name</code>
	 * @param String $name     The name
	 * @param *      $instance The injection
	 * @returns TInjectable
	 * @throws \ErrorException if <code>$name</code> is not a <em>nonempty</em> <code>String</code>
	 * @throws \ErrorException if <code>$name</code> is a registered injection
	 */
	public function setInjected($name, $instance) {
		if (!is_string($name) || empty($name) || array_key_exists($name, $this->_tInjectableIns)) throw new \ErrorException();
		
		$this->_tInjectableIns[$name] = $instance;
		
		return $this;
	}
	
	/**
	 * Sets multiple injections
	 * @param array $dict The injections
	 * @returns TInjectable
	 */
	public function setInjecteds(Array $dict) {
		foreach ($dict as $name => $instance) $this->setInjected($name, $instance);
		
		return $this;
	}
	
	
	/**
	 * Returns an <em>alias</em> to the injection referenced by <code>$name</code>
	 * @param String $name The name
	 * @return *
	 * @throws \ErrorException if <code>$name</code> is not a <em>nonempty</em> <code>String</code>
	 */
	protected function &_useInjected($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		if (array_key_exists($name, $this->_tInjectableIns)) return $this->_tInjectableIns[$name];
		
		if (array_key_exists($name, self::$_tInjectableAll)) {
			$this->_tInjectableIns[$name] =& self::$_tInjectableAll[$name];
			
			return $this->_tInjectableIns[$name];
		}
		
		return null;
	}
	
	/**
	 * Returns an <code>Array</code> of <em>aliases</em> to the injections referenced by <code>$names</code>
	 * @param Array $names The names
	 * @return Array
	 */
	protected function _useInjecteds(Array $names) {
		$res = [];
		
		foreach($names as $name) $res[$name] =& $this->useInjection($name);
		
		return $res;
	}
}