<?php

namespace lola\error;

use eve\inject\IInjectableIdentity;



interface INativeErrorSource
extends IInjectableIdentity
{

	public function handleException(\Throwable $ex);

	public function handleError(int $type, string $message, string $file, int $line) : bool;

	public function handleShutdown();
}
