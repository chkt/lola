<?php

namespace lola\module;

use eve\inject\IInjectable;



interface IModule
extends IInjectable
{

	public function getModuleConfig();
}
