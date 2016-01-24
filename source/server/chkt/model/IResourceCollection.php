<?php

namespace chkt\model;

use chkt\model\IResourceQuery;



interface IResourceCollection {
	
	public function isLive();
	
	public function isDirty();
	
	
	public function read(IResourceQuery $query, $limit, $offset = 0);
	
	public function update();
	
	
	public function getLength();
	
	public function& useItem($index);
}
