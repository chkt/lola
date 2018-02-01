<?php

namespace lola\app;

use eve\common\access\ITraversableAccessor;
use eve\common\factory\ICoreFactory;
use eve\driver\IInjectorDriver;
use eve\driver\InjectorDriverFactory;
use eve\entity\IEntityParser;
use lola\module\EntityParser;



final class CoreProviderFactory
extends InjectorDriverFactory
{

	protected function _produceDriver(ICoreFactory $base, ITraversableAccessor $config, array & $dependencies) : IInjectorDriver {
		return $base->newInstance(CoreProvider::class, [ & $dependencies ]);
	}

	protected function _produceEntityParser(IInjectorDriver $driver, ITraversableAccessor $config) : IEntityParser {
		return $driver->getCoreFactory()->newInstance(EntityParser::class);
	}
}
