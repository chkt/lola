<?php

namespace test\ctrl;

use PHPUnit\Framework\TestCase;
use lola\ctrl\NoActionException;



final class NoActionExceptionTest
extends TestCase
{

	private function _produceException(string $action = '') {
		return new NoActionException($action);
	}


	public function testInheritance() {
		$ex = $this->_produceException();

		$this->assertInstanceOf(\Exception::class, $ex);
		$this->assertInstanceOf(\lola\ctrl\IActionException::class, $ex);
	}


	public function testGetMessage() {
		$ex = $this->_produceException('foo');

		$this->assertEquals('CTR no action "foo"', $ex->getMessage());
	}
}
