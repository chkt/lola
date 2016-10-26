<?php

namespace lola\model;

use lola\model\IResourceQuery;



interface IResourceCollection {

	const STATE_NEW = 1;
	const STATE_LIVE = 2;
	const STATE_DEAD = 3;



	public function isLive();

	public function isDirty();


	public function read(IResourceQuery $query, $limit, $offset = 0);

	public function update();


	public function getLength();

	public function getIndexOf(IResourceQuery $query);

	public function& useItem($index);
}
