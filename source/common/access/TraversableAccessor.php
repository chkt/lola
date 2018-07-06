<?php

namespace lola\common\access;

use eve\common\base\IMethodProxy;
use eve\common\projection\IProjectable;
use eve\common\access\ITraversableAccessor;
use lola\common\base\ArrayOperation;
use lola\common\projection\IFilterProjectable;



class TraversableAccessor
extends ItemAccessor
implements ITraversableAccessor, IFilterProjectable
{

	private $_methodProxy;


	public function __construct(
		IMethodProxy $proxy,
		IAccessorSelector $selector,
		array& $data = []
	) {
		parent::__construct($selector, $data);

		$this->_methodProxy = $proxy;
	}


	final protected function _getMethodProxy() : IMethodProxy {
		return $this->_methodProxy;
	}


	public function isEqual(IProjectable $b) : bool {
		return $this->_useData() === $b->getProjection();
	}


	public function iterate() : \Generator {
		$gen = $this->_methodProxy->callMethod(ArrayOperation::class, 'iterate', [ $this->_useData() ]);

		foreach ($gen as $key => & $value) yield $key => $value;
	}


	public function getProjection(array $filter = null) : array {
		if (is_null($filter)) return $this->_useData();

		$selector = $this->_getSelector();
		$res = [];

		foreach ($filter as $key) {
			$value = $this->getItem($key);

			$selector
				->select($res, $key)
				->linkAll()
				->setResolvedItem($value);
		}

		return $res;
	}
}
