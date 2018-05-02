<?php

namespace lola\common\access;



interface IAccessorSelector
{

	public function isResolved() : bool;

	public function hasAccessFailure() : bool;

	public function hasBranchFailure() : bool;


	public function getPath(int $index0 = 0, int $indexN = null) : string;

	public function getPathLength() : int;

	public function getResolvedLength() : int;


	public function getResolvedItem();

	public function setResolvedItem($item) : IAccessorSelector;


	public function select(array& $source, string $key) : IAccessorSelector;


	public function linkTo(int $index) : IAccessorSelector;

	public function linkAll() : IAccessorSelector;

	public function unlinkAt(int $index) : IAccessorSelector;

	public function unlinkRecursive() : IAccessorSelector;
}
