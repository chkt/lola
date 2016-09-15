<?php

namespace lola\module;

use lola\inject\IInjectable;



interface IModule
extends IInjectable
{

	public function getModuleConfig();
}
