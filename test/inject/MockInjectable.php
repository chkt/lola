<?php

namespace test\inject;

use lola\inject\IInjectable;


final class MockInjectable
implements IInjectable
{

	static public function getDependencyConfig(array $config) {
		return $config;
	}



	private $_args;


	public function __construct(...$args) {
		$this->_args = $args;
	}


	public function getArgs() {
		return $this->_args;
	}
}
