<?php

namespace chkt\app;


interface IApp {
	public function __construct(Array $config);
	
	
	public function getProperty($name);
}