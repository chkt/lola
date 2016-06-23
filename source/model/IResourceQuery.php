<?php

namespace lola\model;



interface IResourceQuery 
{
	public function getRequirements();
	
	public function getOrder();
	
	
	public function getQuery();
	
	public function getSorting();
}
