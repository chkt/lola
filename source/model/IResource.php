<?php

namespace lola\model;

use lola\model\IResourceQuery;



interface IResource {
	
	public function isLive();
	
	public function isDirty();
	
	
	public function getData();
	
	public function setData(Array $data);
	
	
	public function create(Array $data);
	
	public function read(IResourceQuery $query);
	
	public function update();
	
	public function delete();
}
