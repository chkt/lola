<?php

namespace test\io\http;

use lola\inject\IInjector;



final class MockInjector
implements IInjector
{

	public function produce(string $className, array $params = []) {
		return null;
	}

	public function process(callable $fn, array $deps = []) {
		return null;
	}
}
