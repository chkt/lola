<?php

namespace lola\common\base;



class StringOperation
{

	static public function pathToCamel(string $path) : string {
		return lcfirst(str_replace('/', '', ucwords(strtolower($path), '/')));
	}

	static public function pathToSnake(string $path) : string {
		return str_replace('/', '_', trim(strtolower($path), '/'));
	}
}
