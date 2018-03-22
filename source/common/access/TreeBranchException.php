<?php

namespace lola\common\access;

use lola\common\access\exception\ATreeAccessorException;



final class TreeBranchException
extends ATreeAccessorException
{

	protected function _produceMessage() : string {
		return 'ACC no branch "%s!%s"';
	}
}
