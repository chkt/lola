<?php

namespace lola\app;

use eve\common\access\ITraversableAccessor;
use eve\driver\InjectorDriverAssembly;
use eve\entity\IEntityParser;


final class CoreProviderAssembly
extends InjectorDriverAssembly
{

	protected function _produceEntityParser(ITraversableAccessor $config) : IEntityParser {
		$base = $this->getItem('coreFactory');

		return $base->newInstance(\lola\module\EntityParser::class, [
			$base->newInstance(\lola\common\uri\KeyValueTokenizer::class)
		]);
	}
}
