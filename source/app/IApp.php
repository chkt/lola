<?php

namespace lola\app;


interface IApp {
	public function __construct(Array $config);


	public function& useInjector();

	public function& useLocator();

	public function& useRegistry();


	public function getProperty($name);
}
