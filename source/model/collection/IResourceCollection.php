<?php

namespace lola\model\collection;

use lola\model\IResource;
use lola\model\IResourceQuery;



interface IResourceCollection {

	const STATE_NEW = 1;
	const STATE_LIVE = 2;
	const STATE_DEAD = 3;



	public function isLive() : bool;

	public function isDirty() : bool;


	public function getLength() : int;

	public function getIndexOf(IResourceQuery $query) : int;


	public function read(IResourceQuery $query, int $limit, int $offset = 0) : IResourceCollection;

	public function update() : IResourceCollection;


	public function& useItem(int $index) : IResource;
}
