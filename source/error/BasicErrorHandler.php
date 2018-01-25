<?php

namespace lola\error;

use eve\common\access\ITraversableAccessor;
use lola\log\ILogger;



final class BasicErrorHandler
implements IErrorHandler
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [ 'environment:log' ];
	}



	private $_logger;


	public function __construct(ILogger $logger) {
		$this->_logger = $logger;
	}


	public function handleException(\Throwable $ex) {
		if (!($ex instanceof NativeShutdownException)) $this->_logger->logException($ex);
	}
}
