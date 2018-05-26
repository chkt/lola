<?php

namespace lola\common\projection;

use eve\common\access\ITraversableAccessor;
use eve\common\factory\IBaseFactory;
use lola\common\factory\AStatelessInjectorFactory;



abstract class AProjectorFactory
extends AStatelessInjectorFactory
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [ 'core:baseFactory' ];
	}



	private $_baseFactory;
	private $_projector;


	public function __construct(
		IBaseFactory $baseFactory,
		string $projectorName
	) {
		parent::__construct();

		$this->_baseFactory = $baseFactory;
		$this->_projector = $projectorName;
	}


	protected function _produceInstance(ITraversableAccessor $config) {
		return $this->_baseFactory->produce($this->_projector, [ $config->getProjection() ]);
	}
}
