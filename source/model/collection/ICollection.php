<?php

namespace lola\model\collection;

use eve\common\projection\IProjectable;
use lola\model\IModel;
use lola\type\query\IDataQuery;



interface ICollection
extends IProjectable
{

	public function isLive() : bool;

	public function hasItems() : bool;


	public function hasItem(IDataQuery $query) : bool;

	public function& useItem(IDataQuery $query) : IModel;


	public function update() : ICollection;
}
