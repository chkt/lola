<?php

namespace lola\common\projection;

use eve\common\projection\IProjectable;



interface IFilterProjectable
extends IProjectable
{

	public function getProjection(array $filter = null) : array;
}
