<?php

namespace lola\type\data;

use eve\common\projection\IProjectable;



interface ITreeAccessor
extends IKeyAccessor, IProjectable
{

	public function isBranch(string $key) : bool;

	public function isLeaf(string $key) : bool;
}
