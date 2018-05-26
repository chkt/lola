<?php

namespace lola\model\collection;

use eve\common\access\ITraversableAccessor;
use eve\common\factory\IBaseFactory;
use eve\inject\IInjector;
use lola\common\factory\AStatelessInjectorFactory;



abstract class AResourceCollectionFactory
extends AStatelessInjectorFactory
{

	const MODE_NONE = 0;
	const MODE_READ = 1;
	const MODE_PASS = 2;


	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [
			'core:baseFactory',
			'injector:'
		];
	}



	private $_baseFactory;
	private $_injector;

	private $_resource;
	private $_query;


	public function __construct(
		IBaseFactory $baseFactory,
		IInjector $injector,
		string $resourceName,
		string $queryName
	) {
		parent::__construct();

		$this->_baseFactory = $baseFactory;
		$this->_injector = $injector;

		$this->_resource = $resourceName;
		$this->_query = $queryName;
	}


	private function _produceRead(ITraversableAccessor $config) {
		$map = $config->hasKey('map') ? $config->getItem('map') : [];
		$order = $config->hasKey('order') ? $config->getItem('order') : [];
		$limit = $config->hasKey('limit') ? $config->getItem('limit') : 10;
		$offset = $config->hasKey('offset') ? $config->getItem('offset') : 0;

		$query = $this->_baseFactory->produce($this->_query, [ $map, $order ]);

		return $this->_injector
			->produce($this->_resource)
			->read($query, $offset, $limit);
	}

	private function _produceProxy(ITraversableAccessor $config) {
		if (!$config->hasKey('resource')) throw new \ErrorException();

		$resource = $config->getItem('resource');

		if (!($resource instanceof IResourceCollection)) throw new \ErrorException();

		return $resource;
	}


	protected function _produceInstance(ITraversableAccessor $config) {
		$mode = $config->hasKey('mode') ? $config->getItem('mode') : self::MODE_READ;

		if ($mode === self::MODE_PASS) $ins = $this->_produceProxy($config);
		else if ($mode === self::MODE_READ) $ins = $this->_produceRead($config);
		else throw new \ErrorException();

		return $ins;
	}
}
