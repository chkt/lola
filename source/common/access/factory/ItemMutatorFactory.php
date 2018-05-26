<?php

namespace lola\common\access\factory;

use eve\common\factory\IBaseFactory;
use lola\common\access\IItemMutator;
use lola\common\access\AccessorSelector;
use lola\common\access\ItemMutator;
use lola\common\access\operator\AItemAccessorSurrogate;



final class ItemMutatorFactory
extends AItemAccessorSurrogate
{

	private $_baseFactory;
	private $_selector;


	public function __construct(IBaseFactory $base) {
		$selector = $base->produce(AccessorSelector::class);

		parent::__construct($base, $selector);

		$this->_baseFactory = $base;
		$this->_selector = $selector;
	}


	public function produce(array & $config = []) : IItemMutator {
		$base = $this->_baseFactory;

		return $base->produce(ItemMutator::class, [
			$base,
			$this->_selector,
			& $config
		]);
	}
}
