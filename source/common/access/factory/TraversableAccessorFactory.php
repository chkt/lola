<?php

namespace lola\common\access\factory;

use eve\common\factory\ICoreFactory;
use lola\common\access\AccessorSelector;
use lola\common\access\operator\AItemAccessorSurrogate;
use lola\common\access\TraversableAccessor;


final class TraversableAccessorFactory
extends AItemAccessorSurrogate
{

	private $_baseFactory;
	private $_selector;


	public function __construct(ICoreFactory $baseFactory) {
		$selector = $baseFactory->newInstance(AccessorSelector::class);

		parent::__construct($baseFactory, $selector);

		$this->_baseFactory = $baseFactory;
		$this->_selector = $selector;
	}


	public function produce(array & $config) {
		$base = $this->_baseFactory;

		return $base->newInstance(TraversableAccessor::class, [
			$base,
			$this->_selector,
			& $config
		]);
	}
}
