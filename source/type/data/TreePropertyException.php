<?php

namespace lola\type\data;

use lola\type\data\TreeAccessException;



final class TreePropertyException
extends TreeAccessException
{

	public function __construct(array& $item, array $resolved, array $missing) {
		parent::__construct(
			TreeAccessException::TYPE_NO_PROP,
			$item,
			$missing,
			$resolved
		);
	}
}
