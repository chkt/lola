<?php

namespace lola\common\access\exception;

use lola\common\access\IAccessorSelector;



interface IAccessorException
extends \eve\common\access\exception\IAccessorException
{

	public function getSelector() : IAccessorSelector;
}
