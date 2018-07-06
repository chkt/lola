<?php

namespace lola\ctrl;

use eve\inject\IInjectableIdentity;



interface IController
extends IInjectableIdentity
{

	public function hasAction(string $action) : bool;


	public function enter(string $action, IControllerState $state) : IController;
}
