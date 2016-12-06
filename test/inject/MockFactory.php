<?php

namespace test\inject;

use lola\inject\IDependencyFactory;



final class MockFactory
implements IDependencyFactory
{

	static public function getDependencyConfig(array $config) {
		return [];
	}



	private $_config;


	public function __construct() {
		$this->_config = null;
	}


	public function setConfig(array $config) {
		$this->_config = $config;

		return $this;
	}

	public function& produce() {
		return $this->_config;
	}
}
