<?php

namespace lola\error;

use eve\inject\IInjectable;



interface IErrorHandler
extends IInjectable
{

	public function handleException(\Throwable $ex);
}
