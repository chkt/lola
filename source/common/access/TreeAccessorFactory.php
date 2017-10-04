<?php

namespace lola\common\access;

use eve\common\factory\ISimpleFactory;
use eve\common\factory\ICoreFactory;



final class TreeAccessorFactory
implements ISimpleFactory
{

	private $_fab;


	public function __construct(ICoreFactory $fab) {
		$this->_fab = $fab;
	}


	public function produce(array & $data) {
		return $this->_fab->newInstance(TreeAccessor::class, [ & $data ]);
	}
}
