<?php

namespace chkt\app;


interface IApp {
	public function __construct(Array $config);
	
	
	public function& useInjector();
	
	public function& useLocator();
	
	
	public function getProperty($name);
}
