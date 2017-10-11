<?php

namespace lola\provide;

use eve\common\factory\ICoreFactory;
use eve\access\IItemMutator;
use eve\access\ITraversableAccessor;
use eve\inject\IInjector;
use eve\inject\IInjectableIdentity;
use eve\provide\AProvider;



abstract class AConfigurableProvider
extends AProvider
implements IConfigurableProvider
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		$res =  parent::getDependencyConfig($config);
		$res[] = [
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $config
				->getItem('driver')
				->getInstanceCache()
		];

		return $res;
	}


	private $_injector;
	private $_coreFactory;

	private $_cache;

	private $_queue;
	private $_index;


	public function __construct(
		IInjector $injector,
		ICoreFactory $coreFactory,
		IItemMutator $cache
	) {
		parent::__construct($injector, $coreFactory);

		$this->_injector = $injector;
		$this->_coreFactory = $coreFactory;

		$this->_cache = $cache;

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
		$qname = $parts['qname'];

		if (!$this->_coreFactory->hasInterface($qname, IInjectableIdentity::class)) throw new \ErrorException(sprintf('PRV not providable "%s"', $qname));

		return $this->_coreFactory->callMethod($qname, 'getInstanceIdentity', $parts['config']);
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
