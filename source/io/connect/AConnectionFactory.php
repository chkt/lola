<?php

namespace lola\io\connect;



abstract class AConnectionFactory
implements IConnectionFactory
{

	static public function getDependencyConfig(array $config) {
		return [];
	}



	private $_instance;


	public function __construct() {
		$this->_instance = null;
	}


	abstract protected function _produceInstance() : IConnection;


	public function getConnection() : IConnection {
		if (is_null($this->_instance)) $this->_instance = $this->_produceInstance();

		return $this->_instance;
	}
}
