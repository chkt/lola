<?php

namespace lola\common\access\exception;



final class TreePropertyException
extends ATreeAccessorException
{

	protected function _produceMessage() : string {
		return 'ACC no property "%s!%s"';
	}
}
