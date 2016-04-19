<?php

namespace lola\inject;

use lola\inject\IInjectable;



interface IDependencyFactory
extends IInjectable
{
	public function setConfig(Array $config);
	
	public function produce();
}
