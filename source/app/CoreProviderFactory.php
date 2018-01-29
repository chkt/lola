<?php

namespace lola\app;

use eve\common\access\ITraversableAccessor;
use eve\common\factory\ICoreFactory;
use eve\driver\IInjectorDriver;



final class CoreProviderFactory
extends \eve\driver\InjectorDriverFactory
{

	protected function _produceDriver(ICoreFactory $core, ITraversableAccessor $config, array & $dependencies) : IInjectorDriver {
		return $core->newInstance(CoreProvider::class, [ & $dependencies ]);
	}
}
