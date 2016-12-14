<?php

namespace lola\ctrl;

use lola\prov\AProvider;

use lola\inject\IInjectable;
use lola\module\Registry;

use lola\prov\StackProviderResolver;



final class ControllerProvider
extends AProvider
implements IInjectable
{

	const VERSION = '0.5.2';


	static public function getDependencyConfig(array $config) {
		return [ 'environment:registry' ];
	}



	public function __construct(Registry& $registry) {
		parent::__construct(function($hash) use ($registry) {
			return $registry->resolve('controller', $hash);
		}, new StackProviderResolver(StackProviderResolver::RESOLVE_UNIQUE));
	}
}
