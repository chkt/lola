<?php

namespace lola\common\access;



final class TreePropertyException
extends ATreeAccessorException
{

	protected function _produceMessage() : string {
		return 'ACC no property "%s!%s"';
	}
}
