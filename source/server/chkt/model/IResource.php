<?php

namespace chkt\model;



interface IResource {
	
	public function isLive();
	
	public function isDirty();
	
	
	public function getData();
	
	public function setData(Array $data);
	
	
	public function create(Array $data);
	
	public function read(Array $map);
	
	public function update();
	
	public function delete();
}