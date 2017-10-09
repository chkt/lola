<?php

namespace test\error;

use PHPUnit\Framework\TestCase;

use lola\error\INativeErrorException;
use lola\error\ANativeErrorException;



final class ANativeErrorExceptionTest
extends TestCase
{

	private function _mockException(int $type = E_ERROR, string $message = 'message', string $file = 'foo', int $line = 1) {
		$ex = $this
			->getMockBuilder(ANativeErrorException::class)
			->setConstructorArgs([ $type, $message, $file, $line])
			->getMockForAbstractClass();

		return $ex;
	}


	public function testInheritance() {
		$ex = $this->_mockException(E_RECOVERABLE_ERROR, 'recoverable', 'bar', 42);

		$this->assertInstanceOf(INativeErrorException::class, $ex);
		$this->assertInstanceOf(\ErrorException::class, $ex);
		$this->assertInstanceOf(\Throwable::class, $ex);

		$this->assertEquals(E_RECOVERABLE_ERROR, $ex->getSeverity());
		$this->assertEquals('recoverable', $ex->getMessage());
		$this->assertEquals(0, $ex->getCode());
		$this->assertEquals('bar', $ex->getFile());
		$this->assertEquals(42, $ex->getLine());
	}


	public function testIsRecoverable() {
		$recoverable = $this->_mockException(E_RECOVERABLE_ERROR, 'recoverable');

		$this->assertTrue($recoverable->isRecoverable());

		$unrecoverable = $this->_mockException(E_ERROR, 'unrecoverable');

		$this->assertFalse($unrecoverable->isRecoverable());
	}

	public function testIsRecovered() {
		$recoverable = $this->_mockException(E_RECOVERABLE_ERROR, 'recoverable');

		$this->assertTrue($recoverable->isRecoverable());
		$this->assertFalse($recoverable->isRecovered());
	}


	public function testRecover() {
		$recoverable = $this->_mockException(E_RECOVERABLE_ERROR, 'recovered');

		$this->assertFalse($recoverable->isRecovered());
		$this->assertSame($recoverable, $recoverable->recover());
		$this->assertTrue($recoverable->isRecovered());

		$unrecoverable = $this->_mockException(E_ERROR, 'unrecoverable');

		$this->assertFalse($unrecoverable->isRecovered());
		$this->assertSame($unrecoverable, $unrecoverable->recover());
		$this->assertFalse($unrecoverable->isRecovered());
	}
}
