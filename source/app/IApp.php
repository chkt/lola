<?php

namespace lola\app;

use eve\driver\IInjectorHost;
use eve\inject\IInjectable;
use lola\common\IComponentConfig;



interface IApp
extends IInjectorHost, IInjectable
{

	public function getConfig() : IComponentConfig;
}
