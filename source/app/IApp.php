<?php

namespace lola\app;

use eve\driver\IInjectorHost;
use eve\inject\IInjectableIdentity;
use lola\common\IComponentConfig;



interface IApp
extends IInjectorHost, IInjectableIdentity
{

	public function getConfig() : IComponentConfig;
}
