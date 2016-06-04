<?php

namespace lola\model;



interface IModel
{
	
	public function isLive();
	
	public function deferUpdates();
	
	public function update();
}
