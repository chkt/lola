<?php

namespace lola\type;

use eve\common\access\ITraversableAccessor;
use lola\common\projection\IFilterProjectable;



interface IProjector
extends IFilterProjectable
{

	public function setSource(ITraversableAccessor $source) : IProjector;
}
