<?php

namespace lola\model;

use lola\common\projection\IFilterProjectable;



interface IModel
extends IFilterProjectable
{

	public function isLive();


	public function wasCreated();

	public function wasRead();


	public function deferUpdates();

	public function update();
}
