<?php

namespace chkt\inject;

use chkt\inject\IInjectable;



interface IDependencyFactory
extends IInjectable
{
	public function setConfig(Array $config);
	
	public function produce();
}
