<?php

namespace lola\app;

use eve\common\access\ITraversableAccessor;
use eve\common\factory\ICoreFactory;
use eve\driver\IInjectorDriver;



final class InjectorDriverFactory
extends \eve\driver\InjectorDriverFactory
{

	private $_references;


	public function __construct(ICoreFactory $core) {
		parent::__construct($core);

		$this->_references = null;
	}


	public function& useReferenceSource() : array {
		return $this->_references;
	}


	protected function _produceReferences(IInjectorDriver $driver, ITraversableAccessor $config) : ITraversableAccessor {
		$refs = $config->getItem('references');

		$this->_references =& $refs;

		return $driver
			->getAccessorFactory()
			->produce($refs);
	}
}
