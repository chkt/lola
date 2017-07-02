<?php

namespace lola\type\data;

use lola\type\data\IKeyMutator;
use lola\type\IProjectable;



interface ITreeAccessor
extends IKeyMutator, IProjectable
{

	public function isBranch(string $key) : bool;

	public function isLeaf(string $key) : bool;


	public function getBranch(string $key) : ITreeAccessor;

	public function setBranch(string $key, ITreeAccessor $branch) : ITreeAccessor;
}
