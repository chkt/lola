<?php

namespace lola\error;

use eve\common\IGenerateable;



interface IErrorEmitter
extends IErrorHandler, IGenerateable
{

	public function getLength() : int;

	public function hasIndex(int $index) : bool;

	public function removeIndex(int $index) : IErrorEmitter;

	public function getItemAt(int $index);

	public function insertItem(int $index, $item) : IErrorEmitter;

	public function appendItem($item) : IErrorEmitter;

	public function indexOfItem($item) : int;
}
