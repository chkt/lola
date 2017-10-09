<?php

namespace lola\error;



interface INativeErrorException
extends \Throwable
{

	const ERROR_CAPTURED = 0;
	const ERROR_SHUTDOWN = 1;

	const ERROR_RECOVERABLE =
		E_WARNING |
		E_NOTICE |
		E_CORE_WARNING |
		E_COMPILE_WARNING |
		E_USER_WARNING |
		E_USER_NOTICE |
		E_STRICT |
		E_RECOVERABLE_ERROR |
		E_DEPRECATED |
		E_USER_DEPRECATED;


	public function isRecoverable() : bool;

	public function isRecovered() : bool;


	public function recover() : INativeErrorException;
}
