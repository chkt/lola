<?php

namespace lola\io\http;



abstract class AHttpMessageFactory
implements IHttpMessageFactory
{

	static public function getDependencyConfig(Array $config) {
		return [];
	}



	private $_instance;


	public function __construct() {
		$this->_instance = null;
	}


	abstract protected function _produceInstance() : IHttpMessage;


	public function getMessage() : IHttpMessage {
		if (is_null($this->_instance)) $this->_instance = $this->_produceInstance();

		return $this->_instance;
	}
}
