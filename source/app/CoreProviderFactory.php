<?php

namespace lola\app;

use eve\common\factory\IBaseFactory;
use eve\common\factory\ISimpleFactory;
use eve\common\access\ITraversableAccessor;
use eve\common\assembly\IAssemblyHost;
use eve\driver\IInjectorDriver;
use eve\driver\InjectorDriverFactory;



final class CoreProviderFactory
extends InjectorDriverFactory
{

	protected function _produceAssembly(IBaseFactory $base, ISimpleFactory $access, ITraversableAccessor $config) : IAssemblyHost {
		return $base->produce(CoreProviderAssembly::class, [
			$base,
			$access,
			$config
		]);
	}

	protected function _produceDriver(IAssemblyHost $assembly) : IInjectorDriver {
		return $assembly
			->getItem('baseFactory')
			->produce(CoreProvider::class, [ $assembly ]);
	}
}
