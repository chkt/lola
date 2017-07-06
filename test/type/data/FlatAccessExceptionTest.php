<?php

namespace test\type\data;

use PHPUnit\Framework\TestCase;

use lola\type\data\IAccessException;
use lola\type\data\FlatAccessException;



final class FlatAccessExceptionTest
extends TestCase
{

	private function _produceException(string $key = 'bang') : FlatAccessException {
		return new FlatAccessException($key);
	}

	public function testInheritance() {
		$ex = $this->_produceException();

		$this->assertInstanceOf(IAccessException::class, $ex);
	}


	public function testGetMessage() {
		$ex0 = $this->_produceException();

		$this->assertEquals('ACC_NO_PROP:bang', $ex0->getMessage());

		$ex1 = $this->_produceException('foo');

		$this->assertEquals('ACC_NO_PROP:foo', $ex1->getMessage());
	}

	public function testGetMissingKey() {
		$ex = $this->_produceException();

		$this->assertEquals('bang', $ex->getMissingKey());
	}
}
