<?php

namespace lola\common\projection;

use eve\common\access\ITraversableAccessor;
use eve\common\factory\ICoreFactory;
use lola\common\factory\AStatelessInjectorFactory;



abstract class AProjectorFactory
extends AStatelessInjectorFactory
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [ 'core:coreFactory' ];
	}



	private $_baseFactory;
	private $_projector;


	public function __construct(
		ICoreFactory $baseFactory,
		string $projectorName
	) {
		parent::__construct();

		$this->_baseFactory = $baseFactory;
		$this->_projector = $projectorName;
	}


	protected function _produceInstance(ITraversableAccessor $config) {
		return $this->_baseFactory->newInstance($this->_projector, [ $config->getProjection() ]);
	}
}
