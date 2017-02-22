<?php

namespace lola\type;

use lola\inject\IDependencyFactory;
use lola\inject\IInjector;



abstract class AProjectorFactory
implements IDependencyFactory
{

	private $_injector;
	private $_projector;

	private $_config;
	private $_instance;


	static public function getDependencyConfig(array $config) {
		return [ 'injector:' ];
	}



	public function __construct(
		IInjector& $injector,
		string $projectorName
	) {
		$this->_injector =& $injector;
		$this->_projector = $projectorName;

		$this->_config = null;
		$this->_instance = null;
	}


	public function setConfig(array $config) {
		$this->_config = $config;
		$this->_instance = null;

		return $this;
	}


	public function& produce() {
		if (is_null($this->_config)) throw new \ErrorException();

		if (is_null($this->_instance)) $this->_instance = $this->_injector->produce($this->_projector, $this->_config);

		return $this->_instance;
	}
}
