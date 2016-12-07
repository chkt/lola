<?php

namespace lola\prov;

use lola\prov\IProvider;

use lola\prov\IProviderResolver;
use lola\prov\SimpleProviderResolver;

use lola\module\EntityParser;



abstract class AProvider
implements IProvider
{

	const VERSION = '0.3.2';



	/**
	 * The instance factory function
	 * @var function
	 */
	protected $_factory = null;


	/**
	 * The instances
	 * @var array
	 */
	protected $_ins = null;
	/**
	 * The queued configuration callbacks
	 * @var array
	 */
	protected $_config = null;

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
		$this->_config = [];

		$this->_resolver = !is_null($resolver) ? $resolver : new SimpleProviderResolver();
	}


	/**
	 * Configures $ins with the config represented by $id
	 * @param string $id The id string
	 * @param mixed $ins The instance
	 */
	private function _configureId($id, & $ins) {
		$index = $this->_resolver->resolve($id);

		if (array_key_exists($index, $this->_config)) {
			$args = [ & $ins ];

			foreach ($this->_config[$index] as $fn) call_user_func_array($fn, $args);
		}
	}

	/**
	 * Configures $ins as the entity represented by $id
	 * @param string $id The id string
	 * @param mixed $ins The instance
	 */
	public function _configureEntity($id, & $ins) {
		$entity = EntityParser::parse($id);

		$this->_configureId($entity['name'], $ins);

		if (!empty($entity['id'])) $this->_configureId($id, $ins);
	}


	/**
	 * Returns an instance represented by $id
	 * @param string $id
	 * @return mixed
	 */
	private function& _produce($id) {
		$ins = call_user_func($this->_factory, $id);

		$this->_configureEntity($id, $ins);

		return $ins;
	}


	/**
	 * Returns a reference to an instance
	 * @param string $id
	 * @return mixed
	 */
	public function& using(string $id) {
		$index = $this->_resolver->resolve($id);

		if (!array_key_exists($index, $this->_ins)) $this->_ins[$index] = $this->_produce($id);

		return $this->_ins[$index];
	}

	/**
	 * Sets an instance
	 * @param string $id The instance id
	 * @param mixed $ins The instance
	 * @return AProvider
	 */
	public function set(string $id, $ins) : IProvider {
		$index = $this->_resolver->resolve($id);

		$this->_ins[$index] =& $ins;

		return $this;
	}

	/**
	 * Resets an id
	 * @param string $id
	 * @return AProvider
	 */
	public function reset(string $id) : IProvider {
		$index = $this->_resolver->resolve($id);

		unset($this->_ins[$index]);

		return $this;
	}


	/**
	 * Configures an id after creation
	 * @param string $id
	 * @param callable $fn
	 * @return AProvider
	 * @throws \ErrorException if $fn is not a callable
	 */
	public function configure(string $id, callable $fn) : IProvider {
		if (!is_callable($fn)) throw new \ErrorException();

		$index = $this->_resolver->resolve($id);

		if (!array_key_exists($index, $this->_config)) $this->_config[$index] = [];

		$this->_config[$index][] = $fn;

		if (array_key_exists($index, $this->_ins)) $this->_configureEntity($id, $this->_ins);

		return $this;
	}

	/**
	 * Clear the configuration for $id
	 * @param string $id
	 * @return AProvider
	 */
	public function clearConfig(string $id) : IProvider {
		$index = $this->_resolver->resolve($id);

		if (array_key_exists($index, $this->_config)) unset($this->_config[$index]);

		return $this;
	}
}
