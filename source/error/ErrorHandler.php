<?php

namespace lola\error;

use eve\access\ITraversableAccessor;
use eve\inject\IInjectableIdentity;
use lola\log\ILogger;



class ErrorHandler
implements IErrorHandler
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return ['environment:log'];
	}

	static public function getInstanceIdentity(ITraversableAccessor $config) : string {
		return IInjectableIdentity::IDENTITY_SINGLE;
	}



	private $_logger;


	public function __construct(ILogger $logger) {
		$this->_logger = $logger;
	}


	public function handleException(\Throwable $ex) {
		$this->_logger->logException($ex);
	}

	public function handleError(array $error) : bool {
		$this->_logger->logError($error);

		return (bool) ($error['type'] & self::ERROR_RECOVERABLE);
	}

	public function handleShutdownError(array $error) {}
}
