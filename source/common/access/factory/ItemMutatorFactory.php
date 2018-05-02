<?php

namespace lola\common\access\factory;

use eve\common\factory\ICoreFactory;
use lola\common\access\AccessorSelector;
use lola\common\access\ItemMutator;
use lola\common\access\operator\AItemAccessorSurrogate;



final class ItemMutatorFactory
extends AItemAccessorSurrogate
{

	private $_baseFactory;
	private $_selector;


	public function __construct(ICoreFactory $base) {
		$selector = $base->newInstance(AccessorSelector::class);

		parent::__construct($base, $selector);

		$this->_baseFactory = $base;
		$this->_selector = $selector;
	}


	public function produce(array & $config) {
		$base = $this->_baseFactory;

		return $base->newInstance(ItemMutator::class, [
			$base,
			$this->_selector,
			& $config
		]);
	}
}
