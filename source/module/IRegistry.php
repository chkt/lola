<?php

namespace lola\module;

use eve\inject\IInjectableIdentity;



interface IRegistry
extends IInjectableIdentity
{

	public function loadModule(string $name) : IRegistry;

	public function injectModule(string $name, array $module) : IRegistry;

	public function getQualifiedName(string $type, string $name, string $module = '') : string;
}
