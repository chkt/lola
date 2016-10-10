<?php

namespace lola\prov;

use lola\prov\IProviderResolver;



class SimpleProviderResolver
implements IProviderResolver
{
	
	const VERSION = '0.3.2';
	
	const DEFAULT_ID = 'default';
	
	
	
	/**
	 * The id map
	 * @var array
	 */
	protected $_map = null;
	/**
	 * The next free index
	 * @var int
	 */
	protected $_next = 0;
	
	/**
	 * The default id
	 * @var string
	 */
	protected $_default = '';
	
	
	/**
	 * @param string $default The default id
	 * @throws \ErrorException if $default is not a nonempty string
	 */
	public function __construct($default = self::DEFAULT_ID) {
		if (!is_string($default) || empty($default)) throw new \ErrorException();
		
		$this->_map = [];
		$this->_next = 0;
		
		$this->_default = $default;
	}
	
	
	/**
	 * Returns the index associated with $id
	 * @param type $id
	 * @return int
	 * @throws \ErrorException if $id is not a string
	 */
	public function& resolve($id) {
		if (!is_string($id)) throw new \ErrorException();
		
		if (empty($id)) $id = $this->_default;
		
		if (!array_key_exists($id, $this->_map)) $this->_map[$id] = $this->_next++;
		
		return $this->_map[$id];
	}
}
