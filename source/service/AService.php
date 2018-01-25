<?php

namespace lola\service;

use eve\common\access\ITraversableAccessor;
use eve\inject\IInjectableIdentity;



abstract class AService
implements IService
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [];
	}


	static public function getInstanceIdentity(ITraversableAccessor $config) : string {
		return IInjectableIdentity::IDENTITY_SINGLE;
	}
}
