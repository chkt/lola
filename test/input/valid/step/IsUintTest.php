<?php

namespace test\input\valid\step;

use PHPUnit\Framework\TestCase;

use lola\input\valid\ValidationException;
use lola\input\valid\step\IsUint;



final class UintTest
extends TestCase
{

	private function _produceStep() {
		return new IsUint();
	}

	private function _produceException(string $message, int $code) {
		return new ValidationException($message, $code);
	}


	public function testValidate() {
		$step = $this->_produceStep();

		$step->validate(0);
		$this->assertTrue($step->isValid());
		$this->assertEquals(0, $step->getSource());
		$this->assertInternalType('int', $step->getResult());
		$this->assertEquals(0, $step->getResult());
		$this->assertInternalType('int', $step->getResult());

		$step->validate(-1);
		$this->assertFalse($step->isValid());
		$this->assertEquals(-1, $step->getResult());
		$this->assertInternalType('int', $step->getResult());
		$this->assertEquals($this->_produceException('uint.negative', 2), $step->getError());

		$step->validate(0.0);
		$this->assertFalse($step->isValid());
		$this->assertEquals(0.0, $step->getResult());
		$this->assertInternalType('float', $step->getResult());
		$this->assertEquals($this->_produceException('uint.noint', 1), $step->getError());

		$step->validate('0');
		$this->assertFalse($step->isValid());
		$this->assertEquals('0', $step->getResult());
		$this->assertInternalType('string', $step->getResult());
		$this->assertEquals($this->_produceException('uint.noint', 1), $step->getError());
	}
}
