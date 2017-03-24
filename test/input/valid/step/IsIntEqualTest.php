<?php

namespace test\input\valid\step;

use PHPUnit\Framework\TestCase;

use lola\input\valid\ValidationException;
use lola\input\valid\step\IsIntEqual;



final class IsIntEqualTest
extends TestCase
{

	private function _produceStep(int $value) {
		return new IsIntEqual($value);
	}

	private function _produceException(string $message = '', int $code = 0) {
		return new ValidationException($message, $code);
	}


	public function testValidate() {
		$step = $this->_produceStep(1);

		$step->validate('foo');
		$this->assertTrue($step->wasValidated());
		$this->assertFalse($step->isValid());
		$this->assertEquals('foo', $step->getResult());
		$this->assertEquals($this->_produceException('isIntEqual.1.noInt', 1), $step->getError());

		$step->validate(0);
		$this->assertFalse($step->isValid());
		$this->assertEquals($this->_produceException('isIntEqual.1.notEq', 2), $step->getError());

		$step->validate(1);
		$this->assertTrue($step->isValid());
		$this->assertEquals(1, $step->getResult());
	}
}
