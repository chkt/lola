<?php

namespace chkt\http;



class Cookie {
	
	/**
	 * The version string
	 */
	const VERSION = '0.0.5';
	
	/**
	 * The expires at end of session expiry
	 */
	const EXPIRES_SESSION =  0;
	
	/**
	 * The root path
	 */
	const PATH_ROOT = '/';
	
	
	static private $_cookie = [];
	
	
	/**
	 * The cookie states
	 * @var int[]
	 */
	private $_state = null;
	
	/**
	 * The cookie values
	 * @var string[]
	 */
	private $_value = null;
	/**
	 * The cookie timestamps
	 * @var uint[]
	 */
	private $_ts    = null;
	/**
	 * The cookie paths
	 * @var string[]
	 */
	private $_path  = null;
	
	
	
	/**
	 * Returns the cookie <code>$name</code>
	 * @param string $name The cookie name
	 * @return string
	 * @throws \ErrorException if <code>$name</code> is not a <em>nonempty</em> <code>string</code>
	 */
	static public function value($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		if (!array_key_exists($name, self::$_cookie)) self::$_cookie[$name] = filter_input(INPUT_COOKIE, $name);
		
		return self::$_cookie[$name];
	}
	
	
	
	/**
	 * Creates a new instance
	 */
	public function __construct() {
		$this->_state = [];
		
		$this->_value  = [];
		$this->_ts     = [];
		$this->_path   = [];
		$this->_domain = [];
	}
	
	
	/**
	 * Returns <code>true</code> if cookie <code>$name</code> exists, <code>false</code> otherwise
	 * @param string $name The cookie name
	 * @return bool
	 * @throws \ErrorException if <code>$name</code> is not a <em>nonempty/em> <code>String</code>
	 */
	public function has($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		if (!array_key_exists($name, $this->_state)) $this->_state[$name] = 0x0;
		
		$state =& $this->_state[$name];
		
		if ($state === 0x0) {
			$state = 0x1;
			
			$value = self::value($name);
			
			if (!is_null($value)) {
				$state |= 0x2;
				
				$this->_value[$name]  = $value;
			}
		}
		
		return $state & 0x2;
	}

	/**
	 * Returns <code>true</code> if cookie <code>$name</code> was set during execution, <code>false</code> otherwise
	 * @param string $name The cookie name
	 * @return bool
	 */
	public function wasSet($name) {
		return $this->has($name) && $this->_state[$name] & 0x4;
	}
	
	
	/**
	 * Returns the value of cookie <code>$name</code> if cookie exists, <code>null</code> otherwise
	 * @param string $name The cookie name
	 * @return string|null
	 */
	public function getValue($name) {
		return $this->has($name) ? $this->_value[$name] : null;
	}
	
	/**
	 * Returns the expiry timestamp of cookie <code>$name</code> if cookie exists, <code>null</code> otherwise
	 * @param string $name The cookie name
	 * @return int|null
	 */
	public function getExpiry($name) {
		return $this->wasSet($name) ? $this->_ts[$name] : null;
	}
	
	/**
	 * Returns the paths of cookie <code>$name</code> if cookie exists, <code>null</code> otherwise
	 * @param string $name The cookie name
	 * @return string|null
	 */
	public function getPath($name) {
		return $this->wasSet($name) ? $this->_path[$name] : null;
	}
	
	/**
	 * Returns the domain of cookie <code>$name</code> if cookie exists, <code>null</code> otherwise
	 * @param string $name The cookie name
	 * @return string|null
	 */
	public function getDomain($name) {
		return $this->wasSet($name) && !empty($this->_domain[$name]) ? $this->_domain[$name] : null;
	}
	
	
	/**
	 * Sets a cookie
	 * @param string $name    The cookie name
	 * @param string $value   The cookie value
	 * @param uint   $expires The cookie expiry
	 * @param string $path    The cookie path
	 * @return Cookie
	 * @throws \ErrorException if <code>$name</code> is not a <em>nonempty</em> <code>String</code>
	 * @throws \ErrorException if <code>$value</code> is not a <code>String</code>
	 * @throws \ErrorException if <code>$expires</code> is not a <code>uint</code>
	 * @throws \ErrorException if <code>$path</code> is not a <em>nonempty</em> <code>String</code>
	 */
	public function set($name, $value, $expires = 0, $path = '/') {
		if (
			!is_string($name) || empty($name) ||
			!is_string($value) ||
			!is_int($expires) || $expires < 0 ||
			!is_string($path)  || empty($path)
		) throw new \ErrorException();
		
		if (!$this->wasSet($name)) $this->_state[$name] = $this->_state[$name] & ~0x8 | 0x4;
		
		$this->_value[$name] = $value;
		$this->_ts[$name]    = $expires;
		$this->_path[$name]  = $path;
		
		return $this;
	}

	/**
	 * Resets a cookie
	 * @param string $name The cookie name
	 * @return Cookie
	 */
	public function reset($name) {		
		if (!$this->has($name)) return $this;
		
		$this->_state[$name] = $this->_state[$name] & ~0x4 | 0x8;
		
		unset($this->_value[$name]);
		
		if (array_key_exists($name, $this->_ts)) unset($this->_ts[$name]);
		if (array_key_exists($name, $this->_path)) unset($this->_path[$name]);
		
		return $this;
	}
	
	
	/**
	 * Sends the set cookies to the output stream
	 * @return Cookie
	 */
	public function send() {
		foreach ($this->_state as $name => $value) {
			if      ($value & 0x8) setcookie($name, null, 0);
			else if ($value & 0x4) setcookie($name, $this->_value[$name], $this->_ts[$name], $this->_path[$name]);
		}
		
		return $this;
	}
}