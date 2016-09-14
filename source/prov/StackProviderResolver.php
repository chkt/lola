<?php

namespace lola\prov;

use lola\prov\IProviderResolver;



class StackProviderResolver implements IProviderResolver {

	const VERSION = '0.1.0';

	const RESOLVE_SINGLETON = 0;
	const RESOLVE_UNIQUE = PHP_INT_MAX;



	private $_default = 0;


	public function __construct($default = self::RESOLVE_SINGLETON) {
		if (!is_int($default) || $default < 0) throw new \ErrorException();

		$this->_default = $default;
	}


	public function& resolve($hash, Array& $instances, Callable $factory) {
		if (!is_string($hash) || empty($hash)) throw new \ErrorException();

		$segs = explode('@', $hash);
		$num = count($segs);

		if ($num > 2) throw new \ErrorException();

		$id = $segs[0];
		$pos = $this->_default;

		if ($num === 2) {
			if (!is_numeric($segs[1])) throw new \ErrorException();

			$pos = (int) $segs[1];
		}

		if (!array_key_exists($id, $instances)) $instances[$id] = [];

		$stack =& $instances[$id];
		$len = count($stack);

		if ($pos > $len - 1) {
			$pos = $len;
			$stack[] = call_user_func($factory, $id);
		}

		return $stack[$pos];
	}
}
