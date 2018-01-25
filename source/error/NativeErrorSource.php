<?php

namespace lola\error;

use eve\common\access\ITraversableAccessor;
use eve\inject\IInjector;



class NativeErrorSource
implements INativeErrorSource
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [ 'injector:' ];
	}

	static public function getInstanceIdentity(ITraversableAccessor $config) : string {
		return self::IDENTITY_SINGLE;
	}



	private $_injector;


	public function __construct(IInjector $injector) {
		$this->_injector = $injector;

		set_exception_handler([$this, 'handleException']);		//TODO: should we guarantee handlers do not get rewritten?
		set_error_handler([$this, 'handleError']);
		register_shutdown_function([$this, 'handleShutdown']);
	}


	private function _getEmitter() : IErrorEmitter {
		return $this->_injector->produce(ErrorEmitter::class);		//TODO: Fixed Emitter class
	}


	protected function _terminate() {
		exit();
	}


	public function handleException(\Throwable $ex) {
		$this
			->_getEmitter()
			->handleException($ex);

		$this->_terminate();
	}

	public function handleError(int $type, string $message, string $file, int $line) : bool {
		if ((error_reporting() & $type) === 0) return true;

		$ex = new NativeErrorException($type, $message, $file, $line);

		$this
			->_getEmitter()
			->handleException($ex);

		if (!$ex->isRecovered()) $this->_terminate();

		return true;
	}

	public function handleShutdown() {
		$error = error_get_last();

		if (is_null($error)) return;

		$ex = new NativeShutdownException(
			$error['type'],
			$error['message'],
			$error['file'],
			$error['line']
		);

		$this
			->_getEmitter()
			->handleException($ex);
	}
}
