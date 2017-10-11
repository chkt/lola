<?php

namespace lola\provide;

use eve\provide\IProvider;



interface IConfigurableProvider
extends IProvider
{

	public function addConfiguration(string $entity, callable $fn) : IConfigurableProvider;
}
