<?php

namespace lola\common\access;

use lola\common\access\exception\ATreeAccessorException;



final class TreePropertyException
extends ATreeAccessorException
{

	protected function _produceMessage() : string {
		return 'ACC no property "%s!%s"';
	}
}
