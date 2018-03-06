<?php

namespace lola\common\factory;

use eve\common\access\ITraversableAccessor;
use eve\inject\IInjectableIdentity;
use eve\inject\IInjectableFactory;


abstract class AStatelessInjectorFactory
implements IInjectableFactory
{

	static public function getInstanceIdentity(ITraversableAccessor $config) : string {
		return IInjectableIdentity::IDENTITY_SINGLE;
	}



	private $_config;


	public function __construct() {
		$this->_config = null;
	}


	abstract protected function _produceInstance(ITraversableAccessor $config);


	public function setConfig(ITraversableAccessor $config) {
		$this->_config = $config;

		return $this;
	}

	public function getInstance() {
		if (is_null($this->_config)) throw new \ErrorException();

		return $this->_produceInstance($this->_config);
	}


	public function produce(ITraversableAccessor $config) {
		return $this
			->setConfig($config)
			->getInstance();
	}
}
