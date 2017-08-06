<?php

namespace lola\type\data;

use lola\type\IProjectable;



interface ITreeAccessor
extends IKeyAccessor, IProjectable
{

	public function isBranch(string $key) : bool;

	public function isLeaf(string $key) : bool;
}
