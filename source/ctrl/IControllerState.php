<?php

namespace lola\ctrl;

use eve\inject\IInjectable;



interface IControllerState
extends IInjectable		//TODO: turn IControllerState into pure property holder
{

	public function getCtrl();

	public function getAction();


	public function setVars(array $vars);
}
