<?php

namespace lola\error;

use eve\common\IGenerateable;
use eve\inject\IInjectableIdentity;



interface IErrorEmitter
extends IErrorHandler, IInjectableIdentity, IGenerateable
{

	public function getLength() : int;

	public function hasIndex(int $index) : bool;

	public function removeIndex(int $index) : IErrorEmitter;

	public function getItemAt(int $index);

	public function insertItem(int $index, $item) : IErrorEmitter;

	public function appendItem($item) : IErrorEmitter;

	public function indexOfItem($item) : int;
}
