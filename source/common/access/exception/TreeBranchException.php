<?php

namespace lola\common\access\exception;



final class TreeBranchException
extends ATreeAccessorException
{

	protected function _produceMessage() : string {
		return 'ACC no branch "%s!%s"';
	}
}
