<?php

namespace lola\common\access\operator;

use eve\common\projection\IProjectable;
use eve\common\access\IItemAccessor;
use lola\common\projection\IFilterProjectable;



interface IItemAccessorComposition
extends \eve\common\access\operator\IItemAccessorComposition
{

	public function copy(IProjectable $source) : IItemAccessor;

	public function merge(IProjectable $a, IProjectable $b) : IItemAccessor;

	public function filter(IFilterProjectable $source, array $keys) : IItemAccessor;

	public function insert(IProjectable $target, IProjectable $source, string $key) : IItemAccessor;
}
