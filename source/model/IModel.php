<?php

namespace lola\model;

use lola\type\IProjectable;



interface IModel
extends IProjectable
{

	public function isLive();


	public function wasCreated();

	public function wasRead();


	public function deferUpdates();

	public function update();
}
