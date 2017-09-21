<?php

namespace lola\error;

use eve\provide\ILocator;
use lola\common\INativeConsumer;


class NativeErrorConsumer
implements INativeConsumer
{

	private $_locator;


	public function __construct(ILocator $locator) {
		$this->_locator = $locator;
	}


	protected function _terminate() {
		exit();
	}


	public function consumeException(\Throwable $ex) {
		$this->_locator
			->locate('environment:errors')
			->handleException($ex);

		$this->_terminate();
	}

	public function consumeError(int $type, string $message, string $file, int $line) : bool {
		$recover = $this->_locator
			->locate('environment:errors')
			->handleError([
				'type' => $type,
				'message' => $message,
				'file' => $file,
				'line' => $line
			]);

		if (!$recover) $this->_terminate();

		return true;
	}

	public function consumeShutdownError() {
		$error = error_get_last();

		if (is_null($error)) return;

		$this->_locator
			->locate('environment:errors')
			->handleShutdownError($error);
	}


	public function attach() : INativeConsumer {
		set_exception_handler([$this, 'consumeException']);
		set_error_handler([$this, 'consumeError'], E_ALL | E_STRICT);
		register_shutdown_function([$this, 'consumeShutdownError']);

		return $this;
	}
}
