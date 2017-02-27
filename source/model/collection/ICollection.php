<?php

namespace lola\model\collection;

use lola\model\IModel;
use lola\model\IResourceQuery;



interface ICollection
{

	public function isLive() : bool;


	public function hasItem(IResourceQuery $query) : bool;

	public function& useItem(IResourceQuery $query) : IModel;


	public function update() : ICollection;
}
