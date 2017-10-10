<?php

namespace lola\error;

use eve\common\IDriver;
use eve\inject\IInjectableIdentity;



interface IErrorDriver
extends IInjectableIdentity, IDriver
{

	public function hasHandler(IErrorHandler $handler) : bool;

	public function removeHandler(IErrorHandler $handler) : IErrorDriver;

	public function setHandler(IErrorHandler $handler) : IErrorDriver;
}
