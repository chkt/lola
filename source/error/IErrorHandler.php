<?php

namespace lola\error;

use eve\inject\IInjectableIdentity;



interface IErrorHandler
extends IInjectableIdentity
{

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



	public function handleException(\Throwable $ex);

	public function handleError(array $error);

	public function handleShutdownError(array $error);
}
