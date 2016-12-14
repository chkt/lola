<?php

namespace lola\inject;

use lola\prov\ProviderProvider;
use lola\module\EntityParser;
use lola\module\Registry;
use lola\inject\IInjectable;



class Injector
{

	const VERSION = '0.5.2';

	const TYPE_INJECTOR = 'injector';
	const TYPE_LOCATOR = 'locator';
	const TYPE_ENVIRONMENT = 'environment';
	const TYPE_CONTROLLER = 'controller';
	const TYPE_SERVICE = 'service';
	const TYPE_FACTORY = 'factory';
	const TYPE_RESOLVE = 'resolve';
	const TYPE_ARGUMENT = 'object';



	private $_locator = null;
	private $_resolve = null;


	public function __construct(
		ProviderProvider& $locator,
		array $resolve = []
	) {
		$this->_locator =& $locator;
		$this->_resolve = $resolve;
	}


	private function& _resolveLocateable(array $dep) {
		if (!array_key_exists(EntityParser::PROP_LOCATION, $dep)) throw new \ErrorException('INJ: not a locateable');

		return $this->_locator->locate(
			$dep[EntityParser::PROP_TYPE],
			$dep[EntityParser::PROP_LOCATION]
		);
	}


	private function& _resolveFactory(array $factory) {
		if (array_key_exists('factory', $factory)) {
			$config = array_key_exists('config', $factory) ? $factory['config'] : [];

			$ins =& $this->_locator->locate(
				'class',
				$factory['factory']
			);

			if (!($ins instanceof IDependencyFactory)) throw new \ErrorException('INJ: not a factory');

			$res = $ins
				->setConfig($config)
				->produce();
		}
		else if (array_key_exists('function', $factory)) {
			$deps = array_key_exists('dependencies', $factory) ? $factory['dependencies'] : [];
			$res = $this->process($factory['function'], $deps);
		}
		else throw new \ErrorException('INJ: malformed factory');

		return $res;
	}


	public function& _resolveInstance(array $dep) {
		if (
			!array_key_exists('location', $dep) ||
			!array_key_exists($dep['location'], $this->_resolve)
		) throw new \ErrorException('INJ: not resolvable');

		return $this->_resolve[$dep['location']];
	}

	private function& _resolveArgument(array $dep) {
		if (!array_key_exists('data', $dep)) throw new \ErrorException('INJ: not an argument');

		return $dep['data'];
	}


	private function& _resolveDependency(array $dep) {
		if (!array_key_exists(EntityParser::PROP_TYPE, $dep)) throw new \ErrorException('INJ: malformed dependency');

		$type = $dep[EntityParser::PROP_TYPE];

		switch ($type) {
			case self::TYPE_INJECTOR : return $this;
			case self::TYPE_LOCATOR : return $this->_locator;
			case self::TYPE_CONTROLLER :
			case self::TYPE_SERVICE :
			case self::TYPE_ENVIRONMENT : return $this->_resolveLocateable($dep);
			case self::TYPE_FACTORY : return $this->_resolveFactory($dep);
			case self::TYPE_RESOLVE : return $this->_resolveInstance($dep);
			case self::TYPE_ARGUMENT : return $this->_resolveArgument($dep);
			default : throw new \ErrorException('INJ: unknown dependency');
		}
	}


	private function _resolveDependencies(array $deps) {
		$res = [];

		foreach($deps as $dep) {
			if (is_string($dep)) $dep = EntityParser::extractType($dep);

			if (!is_array($dep)) throw new \ErrorException('INJ: invalid dependency');

			$res[] =& $this->_resolveDependency($dep);
		}

		return $res;
	}


	public function produce(string $className, array $params = []) {
		if (empty($className)) throw new \ErrorException();

		$class = new \ReflectionClass($className);

		if (!$class->implementsInterface(IInjectable::class)) throw new \ErrorException('INJ: not injectable');

		$deps = call_user_func([$className, 'getDependencyConfig'], $params);
		$args = $this->_resolveDependencies($deps);

		return $class->newInstanceArgs($args);
	}

	public function process(callable $fn, array $deps = []) {
		$args = $this->_resolveDependencies($deps);

		return $fn(...$args);
	}
}
