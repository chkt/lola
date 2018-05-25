<?php

namespace lola\common\access\factory;

use eve\common\factory\IBaseFactory;
use eve\common\access\IItemAccessor;
use lola\common\access\AccessorSelector;
use lola\common\access\ItemAccessor;
use lola\common\access\operator\AItemAccessorSurrogate;



final class ItemAccessorFactory
extends AItemAccessorSurrogate
{

	private $_baseFactory;
	private $_selector;


	public function __construct(IBaseFactory $baseFactory) {
		$selector = $baseFactory->newInstance(AccessorSelector::class);

		parent::__construct($baseFactory, $selector);

		$this->_baseFactory = $baseFactory;
		$this->_selector = $selector;
	}


	public function produce(array& $config = []) : IItemAccessor {
		return $this->_baseFactory->newInstance(ItemAccessor::class, [
			$this->_selector,
			& $config
		]);
	}
}
