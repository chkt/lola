<?php

namespace test\error;

use PHPUnit\Framework\TestCase;

use lola\error\ANativeErrorException;
use lola\error\NativeShutdownException;



final class NativeShutdownExceptionTest
extends TestCase
{

	private function _produceException() {
		return new NativeShutdownException(E_ERROR, '', '', 0);
	}


	public function testInheritance() {
		$ex = $this->_produceException();

		$this->assertInstanceOf(ANativeErrorException::class, $ex);
	}
}
