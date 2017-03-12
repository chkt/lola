<?php

namespace test\input\valid\step;

use PHPUnit\Framework\TestCase;

use lola\input\valid\ValidationException;
use lola\input\valid\step\StringEqualsStep;



final class StringEqualsStepTest
extends TestCase
{

	private function _produceStep(string $value = 'foo') : StringEqualsStep {
		return new StringEqualsStep($value);
	}

	private function _produceException(string $message = '', int $code = 0) {
		return new ValidationException($message, $code);
	}


	public function testValidate() {
		$step = $this->_produceStep();

		$step->validate('foo');
		$this->assertTrue($step->isValid());
		$this->assertEquals('foo', $step->getSource());
		$this->assertEquals('foo', $step->getResult());

		$step->validate('bar');
		$this->assertFalse($step->isValid());
		$this->assertEquals('bar', $step->getResult());
		$this->assertEquals($this->_produceException('stringEquals.foo.notequal', 2), $step->getError());

		$step->validate(1);
		$this->assertFalse($step->isValid());
		$this->assertEquals(1, $step->getResult());
		$this->assertEquals($this->_produceException('stringEquals.foo.nostring', 1), $step->getError());
	}
}
