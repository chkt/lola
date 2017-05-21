<?php

namespace lola\type\data;

use lola\type\data\TreeAccessException;



final class TreeBranchException
extends TreeAccessException
{

	public function __construct(& $item, array $resolved, array $missing) {
		parent::__construct(
			TreeAccessException::TYPE_NO_BRANCH,
			$item,
			$missing,
			$resolved
		);
	}
}
