<?php

namespace lola\common\access;

use eve\common\projection\IProjectable;
use lola\common\access\operator\IItemAccessorComposition;



interface IItemMutator
extends \eve\common\access\IItemMutator, IItemAccessorComposition
{

	public function mergeAssign(IProjectable $b) : IItemMutator;

	public function filterSelf(array $keys) : IItemMutator;

	public function insertAssign(IProjectable $source, string $key) : IItemMutator;

	public function selectSelf(string $key) : IItemMutator;
}
