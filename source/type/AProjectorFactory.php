<?php

namespace lola\type;

use eve\access\ITraversableAccessor;
use eve\inject\IInjector;
use lola\common\factory\AStatelessInjectorFactory;



abstract class AProjectorFactory
extends AStatelessInjectorFactory
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [ 'injector:' ];
	}



	private $_injector;
	private $_projector;


	public function __construct(
		IInjector $injector,
		string $projectorName
	) {
		parent::__construct();

		$this->_injector = $injector;
		$this->_projector = $projectorName;
	}


	protected function _produceInstance(ITraversableAccessor $config) {
		return $this->_injector->produce($this->_projector, $config->getProjection());
	}
}
