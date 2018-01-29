<?php

namespace lola\app;

use eve\common\access\ITraversableAccessor;
use eve\driver\InjectorDriver;
use eve\inject\IInjectableIdentity;
use eve\provide\IProvider;



class CoreProvider
extends InjectorDriver
implements IInjectableIdentity, IProvider
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		throw new \ErrorException('INJ cannot inject CoreProvider');
	}

	static public function getInstanceIdentity(ITraversableAccessor $config) : string {
		return IInjectableIdentity::IDENTITY_SINGLE;
	}
}
