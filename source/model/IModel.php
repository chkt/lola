<?php

namespace lola\model;



interface IModel
{
	
	const INTERFACE_VERSION = '0.2.1';
	
	
	
	public function isLive();
	
	
	public function wasCreated();
	
	public function wasRead();
	
	
	public function deferUpdates();
	
	public function update();
}
