<?php

namespace lola\common\access;



final class TreeBranchException
extends ATreeAccessorException
{

	protected function _produceMessage() : string {
		return 'ACC no branch "%s!%s"';
	}
}
