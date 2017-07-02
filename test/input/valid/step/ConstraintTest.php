<?php

namespace test\input\valid\step;

use PHPUnit\Framework\TestCase;

use lola\input\valid\step\Constraint;
use lola\input\valid\ValidationException;



final class ConstraintTest
extends TestCase
{

	private function _produceStep(array $constraint = []) : Constraint {
		return new Constraint($constraint);
	}

	private function _produceException(string $message, int $code) {
		return new ValidationException($message, $code);
	}


	public function testGetId() {
		$step = $this->_produceStep();

		$this->assertEquals('constraint', $step->getId());
	}


	public function testValidate() {
		$step = $this->_produceStep([
			'foo',
			'bar'
		]);

		$step->validate('foo');

		$this->assertTrue($step->wasValidated());
		$this->assertTrue($step->isValid());
		$this->assertEquals('foo', $step->getResult());

		$step->validate('baz');

		$this->assertTrue($step->wasValidated());
		$this->assertFalse($step->isValid());
		$this->assertEquals('baz', $step->getResult());
		$this->assertEquals($this->_produceException('constraint.invalid', 1), $step->getError());
	}
}
