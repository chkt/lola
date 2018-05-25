<?php

namespace lola\common\access\operator;

use eve\common\base\IMethodProxy;
use eve\common\projection\IProjectable;
use eve\common\access\IItemAccessor;
use eve\common\access\ITraversableAccessor;
use eve\inject\IInjectableIdentity;
use lola\common\base\ArrayOperation;
use lola\common\projection\IFilterProjectable;
use lola\common\access\IAccessorSelector;



abstract class AItemAccessorSurrogate
extends \eve\common\access\operator\AItemAccessorSurrogate
implements IItemAccessorSurrogate
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [ 'core:baseFactory' ];
	}

	static public function getInstanceIdentity(ITraversableAccessor $config) : string {
		return IInjectableIdentity::IDENTITY_SINGLE;
	}



	private $_methodProxy;
	private $_selector;


	public function __construct(IMethodProxy $methodProxy, IAccessorSelector $selector) {
		$this->_methodProxy = $methodProxy;
		$this->_selector = $selector;
	}


	public function copy(IProjectable $source) : IItemAccessor {
		$data = $source->getProjection();

		return $this->produce($data);
	}


	public function merge(IProjectable $a, IProjectable $b) : IItemAccessor {
		$data = $this->_methodProxy->callMethod(ArrayOperation::class, 'merge', [
			$a->getProjection(),
			$b->getProjection()
		]);

		return $this->produce($data);
	}

	public function filter(IFilterProjectable $source, array $keys) : IItemAccessor {
		$data = $source->getProjection($keys);

		return $this->produce($data);
	}

	public function insert(IProjectable $target, IProjectable $source, string $key) : IItemAccessor {
		$data = $target->getProjection();

		$this->_selector
			->select($data, $key)
			->linkAll()
			->setResolvedItem($source->getProjection());

		return $this->produce($data);
	}
}
