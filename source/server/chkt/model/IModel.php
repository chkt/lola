<?php

namespace chkt\model;



interface IModel {
	static public function Create(Array $data);
	
	static public function Read($id);
	
	
	public function update();
	
	public function delete();
	
	
	public function getData();
	
	public function setData(Array $data);
}