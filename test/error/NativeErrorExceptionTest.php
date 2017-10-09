<?php

namespace test\error;

use PHPUnit\Framework\TestCase;

use lola\error\ANativeErrorException;
use lola\error\NativeErrorException;



final class NativeErrorExceptionTest
extends TestCase
{

	private function _produceException() {
		return new NativeErrorException(E_ERROR, '', '', 0);
	}


	public function testInheritance() {
		$ex = $this->_produceException();

		$this->assertInstanceOf(ANativeErrorException::class, $ex);
	}
}
