<?php

namespace lola\model\map;

use eve\common\access\ITraversableAccessor;
use eve\inject\IInjector;
use lola\common\factory\AStatelessInjectorFactory;



abstract class AResourceMapFactory
extends AStatelessInjectorFactory
{

	const MODE_NONE = 0;
	const MODE_READ = 1;
	const MODE_PASS = 2;


	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [ 'injector:' ];
	}



	private $_injector;
	private $_resource;


	public function __construct(IInjector $injector, string $resourceName) {
		parent::__construct();

		$this->_injector = $injector;
		$this->_resource = $resourceName;
	}


	private function _produceProxy(ITraversableAccessor $config) : IResourceMap {
		if (!$config->hasKey('resource')) throw new \ErrorException();

		$resource = $config->getItem('resource');

		if (!($resource instanceof IResourceMap)) throw new \ErrorException();

		return $resource;
	}

	private function _produceRead() {
		return $this->_injector->produce($this->_resource);
	}


	protected function _produceInstance(ITraversableAccessor $config) {
		$mode = $config->hasKey('mode') ? $config->getItem('mode') : self::MODE_READ;

		if ($mode === self::MODE_PASS) $ins = $this->_produceProxy($config);
		else if ($mode === self::MODE_READ) $ins = $this->_produceRead();
		else throw new \ErrorException();

		return $ins;
	}
}
