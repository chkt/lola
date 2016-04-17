<?php

namespace lola\prov;

use \lola\prov\IProviderResolver;
use \lola\prov\SimpleProviderResolver;



abstract class AProvider {
	
	const VERSION = '0.1.0';
	
	
	
	/**
	 * The instance factory function
	 * @var function
	 */
	protected $_factory = null;
	
	/**
	 * The created instances
	 * @var array
	 */
	protected $_ins = null;
	
	/**
	 * The provider resolver
	 * @var IProviderResolver
	 */
	protected $_resolver = null;
	
	
	/**
	 * Creates a new instance
	 * @param callable $factory
	 * @param IProviderResolver $resolver
	 */
	public function __construct(Callable $factory, IProviderResolver $resolver = null) {
		$this->_factory = $factory;
		
		$this->_ins = [];
		
		$this->_resolver = !is_null($resolver) ? $resolver : new SimpleProviderResolver();
	}
	
	
	/**
	 * Gets an instance
	 * @param string $id
	 * @return mixed
	 */
	public function get($id = '') {
		return $this->_resolver->resolve($id, $this->_ins, $this->_factory);
	}
	
	/**
	 * Returns a reference to an instance
	 * @param string $id
	 * @return mixed
	 */
	public function& using($id = '') {
		return $this->_resolver->resolve($id, $this->_ins, $this->_factory);
	}
	
	/**
	 * Sets an instance
	 * @param string $id The instance id
	 * @param mixed $ins The instance
	 * @return AProvider
	 * @throws \ErrorException if $id is not a nonempty string
	 */
	public function set($id, $ins) {
		if (
			!is_string($id) || empty($id) ||
			is_null($ins)
		) throw new \ErrorException();
			
		$this->_ins[$id] = $ins;
		
		return $this;
	}
	
	/**
	 * Resets an id
	 * @param string $id
	 * @return AProvider
	 * @throws \ErrorException if $id is not a nonempty string
	 */
	public function reset($id) {
		if (!is_string($id) || empty($id)) throw new \ErrorException();
		
		unset($this->_ins[$id]);
		
		return $this;
	}
	
	
	/**
	 * Returns a reference to the resolver
	 * @return IProviderResolver
	 */
	public function& useResolver() {
		return $this->_resolver;
	}
	
	/**
	 * Sets the resolver
	 * @param IProviderResolver $resolve
	 * @return AProvider
	 */
	public function setResolver(IProviderResolver $resolve) {
		$this->_resolver = $resolve;
		
		return $this;
	}
}
