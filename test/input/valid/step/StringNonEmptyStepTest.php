<?php

namespace test\input\valid\step;

use PHPUnit\Framework\TestCase;

use lola\input\valid\step\StringNonEmptyStep;
use lola\input\valid\ValidationException;



final class StringNonEmptyStepTest
extends TestCase
{

	private function _produceStep() {
		return new StringNonEmptyStep();
	}

	private function _produceException(string $message, int $code) {
		return new ValidationException($message, $code);
	}


	public function testGetId() {
		$step = $this->_produceStep();

		$this->assertEquals('stringNonEmpty', $step->getId());
	}


	public function testValidate() {
		$step = $this->_produceStep();

		$step->validate('foo');

		$this->assertTrue($step->wasValidated());
		$this->assertTrue($step->isValid());
		$this->assertEquals('foo', $step->getResult());

		$step->validate(1);

		$this->assertTrue($step->wasValidated());
		$this->assertFalse($step->isValid());
		$this->assertEquals(1, $step->getResult());
		$this->assertEquals($this->_produceException('stringNonEmpty.nostring', 1), $step->getError());

		$step->validate('');

		$this->assertTrue($step->wasValidated());
		$this->assertFalse($step->isValid());
		$this->assertEquals('', $step->getResult());
		$this->assertEquals($this->_produceException('stringNonEmpty.empty', 2), $step->getError());
	}
}
