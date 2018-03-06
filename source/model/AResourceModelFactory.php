<?php

namespace lola\model;

use eve\common\access\ITraversableAccessor;
use eve\common\factory\ICoreFactory;
use eve\inject\IInjector;
use lola\common\factory\AStatelessInjectorFactory;
use lola\type\StructuredData;



abstract class AResourceModelFactory
extends AStatelessInjectorFactory
{

	const MODE_NONE = 0;
	const MODE_CREATE = 1;
	const MODE_READ = 2;
	const MODE_PASS = 3;


	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [
			'core:coreFactory',
			'injector:'
		];
	}



	private $_baseFactory;
	private $_injector;

	private $_model;
	private $_resource;
	private $_query;


	public function __construct(
		ICoreFactory $baseFactory,
		IInjector $injector,
		string $modelFactoryName,
		string $resourceName,
		string $queryName
	) {
		parent::__construct();

		$this->_baseFactory = $baseFactory;
		$this->_injector = $injector;

		$this->_model = $modelFactoryName;
		$this->_resource = $resourceName;
		$this->_query = $queryName;
	}


	private function _produceProxy(ITraversableAccessor $config) {
		if (!$config->hasKey('resource')) throw new \ErrorException();

		$resource = $config->getItem('resource');

		if (!($resource instanceof IResource)) throw new \ErrorException();

		return $resource;
	}

	private function _produceCreate(ITraversableAccessor $config) {
		$factory = $this->_injector->produce($this->_model, $config->getProjection());

		if (!($factory instanceof IModelFactory)) throw new \ErrorException();

		$data = $factory->produceModelData();

		return $this->_injector
			->produce($this->_resource)
			->create(new StructuredData($data));
	}

	private function _produceRead(ITraversableAccessor $config) {
		if (!$config->hasKey('map')) throw new \ErrorException();

		$query = $this->_baseFactory->newInstance($this->_query, [ $config->getItem('map') ]);

		return $this->_injector
			->produce($this->_resource)
			->read($query);
	}


	protected function _produceInstance(ITraversableAccessor $config) {
		$mode = $config->hasKey('mode') ? $config->getItem('mode') : self::MODE_READ;

		if ($mode === self::MODE_PASS) $ins = $this->_produceProxy($config);
		else if ($mode === self::MODE_READ) $ins = $this->_produceRead($config);
		else if ($mode === self::MODE_CREATE) $ins = $this->_produceCreate($config);
		else throw new \ErrorException();

		return $ins;
	}
}
