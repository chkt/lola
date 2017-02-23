<?php

namespace test\input\valid\step;

use PHPUnit\Framework\TestCase;

use lola\input\valid\step\FloatNonNaNStep;
use lola\input\valid\ValidationException;



final class FloatNonNaNStepTest
extends TestCase
{

	private function _produceStep() {
		return new FloatNonNaNStep();
	}

	private function _produceException(string $message, int $code) {
		return new ValidationException($message, $code);
	}


	public function testValidate() {
		$step = $this->_produceStep();

		$step->validate(0.0);
		$this->assertTrue($step->isValid());
		$this->assertEquals(0.0, $step->getSource());
		$this->assertEquals(0.0, $step->getResult());

		$step->validate(NAN);
		$this->assertFalse($step->isValid());
		$this->assertNan($step->getResult());
		$this->assertEquals($this->_produceException('floatNonNaN.nan', 2), $step->getError());

		$step->validate('0.0');
		$this->assertFalse($step->isValid());
		$this->assertEquals('0.0', $step->getResult());
		$this->assertEquals($this->_produceException('floatNonNaN.nofloat', 1), $step->getError());
	}
}
