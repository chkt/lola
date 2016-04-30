<?php

namespace lola\engine;



final class NoPathException
extends \Exception {
	
	const VERSION = '0.1.8';
	
	
	
	public function __construct($source = 'not provided', $target = 'not provided') {
		parent::__construct($source . '>' . $target);
	}
}
