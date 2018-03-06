<?php

namespace lola\provide;

use eve\common\access\ITraversableAccessor;
use eve\common\assembly\IAssemblyHost;
use eve\inject\IInjector;
use eve\provide\AProvider;



abstract class AConfigurableProvider
extends AProvider
implements IConfigurableProvider
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $config->getItem('driver')
		]];
	}



	private $_accessorFactory;
	private $_injector;

	private $_encoder;
	private $_cache;

	private $_queue;
	private $_index;


	public function __construct(IAssemblyHost $driver)
	{
		$injector = $driver->getItem('injector');

		parent::__construct($injector, $driver->getItem('coreFactory'));

		$this->_accessorFactory = $driver->getItem('accessorFactory');
		$this->_injector = $injector;

		$this->_encoder = $driver->getItem('keyEncoder');
		$this->_cache = $driver->getItem('instanceCache');

		$this->_queue = [];
		$this->_index = [];
	}


	private function _hasQueue(string $id) : bool {
		return array_key_exists($id, $this->_queue);
	}

	private function _isQueued(string $id) : bool {
		return $this->_hasQueue($id) && $this->_index[$id] !== count($this->_queue[$id]);
	}


	private function _createQueue(string $id) : IConfigurableProvider {
		$this->_queue[$id] = [];
		$this->_index[$id] = 0;

		return $this;
	}

	private function _appendQueue(string $id, callable $fn) : IConfigurableProvider {
		$this->_queue[$id][] = $fn;

		return $this;
	}

	private function _applyQueue($id) : IConfigurableProvider {
		$ins = $this->_cache->getItem($id);

		for ($i = $this->_index[$id], $l = count($this->_queue[$id]); $i < $l; $i += 1) $this->_queue[$id][$i]($ins);

		$this->_index[$id] = $l;

		return $this;
	}


	private function _getId(array $parts) : string {
		return $this->_encoder->encodeIdentity($parts['qname'], $this->_accessorFactory->produce($parts['config']));
	}


	public function addConfiguration(string $entity, callable $fn) : IConfigurableProvider {
		$parts = $this->_getParts($entity);
		$id = $this->_getId($parts);

		if (!$this->_hasQueue($id)) $this->_createQueue($id);

		$this->_appendQueue($id, $fn);

		if ($this->_cache->hasKey($id)) $this->_applyQueue($id);

		return $this;
	}


	public function getItem(string $key) {
		$parts = $this->_getParts($key);
		$id = $this->_getId($parts);

		$ins = $this->_injector->produce($parts['qname'], $parts['config']);

		if ($this->_isQueued($id)) $this->_applyQueue($id);

		return $ins;
	}
}
