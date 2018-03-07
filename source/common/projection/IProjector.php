<?php

namespace lola\common\projection;

use eve\common\access\ITraversableAccessor;



interface IProjector
extends IFilterProjectable
{

	public function setSource(ITraversableAccessor $source) : IProjector;
}
