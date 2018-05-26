<?php

namespace lola\common\access\factory;

use eve\common\factory\IBaseFactory;
use eve\common\access\ITraversableAccessor;
use lola\common\access\AccessorSelector;
use lola\common\access\operator\AItemAccessorSurrogate;
use lola\common\access\TraversableAccessor;


final class TraversableAccessorFactory
extends AItemAccessorSurrogate
{

	private $_baseFactory;
	private $_selector;


	public function __construct(IBaseFactory $baseFactory) {
		$selector = $baseFactory->produce(AccessorSelector::class);

		parent::__construct($baseFactory, $selector);

		$this->_baseFactory = $baseFactory;
		$this->_selector = $selector;
	}


	public function produce(array & $config = []) : ITraversableAccessor {
		$base = $this->_baseFactory;

		return $base->produce(TraversableAccessor::class, [
			$base,
			$this->_selector,
			& $config
		]);
	}
}
