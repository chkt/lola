<?php

namespace lola\prov;

use lola\prov\IProviderResolver;



class StackProviderResolver
implements IProviderResolver
{

	const VERSION = '0.3.2';

	const RESOLVE_SINGLETON = 0;
	const RESOLVE_UNIQUE = PHP_INT_MAX;



	/**
	 * The id and index stack map
	 * @var type 
	 */
	protected $_stack = null;
	/**
	 * The next free index
	 * @var type 
	 */
	protected $_next = 0;
	
	/**
	 * The default stack location
	 * @var type 
	 */
	protected $_default = 0;


	/**
	 * @param int $default The default stack location
	 * @throws \ErrorException if $default is not an int > 0
	 */
	public function __construct($default = self::RESOLVE_SINGLETON) {
		if (!is_int($default) || $default < 0) throw new \ErrorException();

		$this->_stack = [];
		$this->_next = 0;
		
		$this->_default = $default;
	}


	/**
	 * Returns the index associated with $hash
	 * @param string $hash
	 * @return int
	 * @throws \ErrorException if $hash is not a nonempty string
	 */
	public function& resolve($hash) {
		if (!is_string($hash) || empty($hash)) throw new \ErrorException();

		$segs = explode('?', $hash);
		$num = count($segs);
		
		if ($num > 2) throw new \ErrorException();
		
		$id = $segs[0];
		$index = $this->_default;
		
		if ($num === 2) {
			if (!is_numeric($segs[1])) throw new \ErrorException();
			
			$index = (int) $segs[1];
		}
		
		if (!array_key_exists($id, $this->_stack)) $this->_stack[$id] = [];
		
		if (!array_key_exists($index, $this->_stack[$id])) $this->_stack[$id][$index] = $this->_next++;
		
		return $this->_stack[$id][$index];
	}
}
