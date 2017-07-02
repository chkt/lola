<?php

namespace test\input\valid\step;

use PHPUnit\Framework\TestCase;

use lola\input\valid\ValidationException;
use lola\input\valid\step\ToArrayLength;



final class ToArrayLengthTest
extends TestCase
{

	private function _produceStep() {
		return new ToArrayLength();
	}

	private function _produceException(string $message = '', int $code = 0) {
		return new ValidationException($message, $code);
	}


	public function testGetId() {
		$step = $this->_produceStep();

		$this->assertEquals('toArrayLength', $step->getId());
	}

	public function testValidate() {
		$step = $this->_produceStep();

		$step->validate('foo');
		$this->assertTrue($step->wasValidated());
		$this->assertFalse($step->isValid());
		$this->assertEquals('foo', $step->getResult());
		$this->assertEquals($this->_produceException('toArrayLength.noArray', 1), $step->getError());

		$step->validate([]);
		$this->assertTrue($step->isValid());
		$this->assertEquals(0, $step->getResult());

		$step->validate(['foo', 'bar', 'baz']);
		$this->assertTrue($step->isValid());
		$this->assertEquals(3, $step->getResult());
	}
}
