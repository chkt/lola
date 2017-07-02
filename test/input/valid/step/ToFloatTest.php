<?php

namespace test\input\valid\step;

use PHPUnit\Framework\TestCase;

use lola\input\valid\step\Noop;
use lola\input\valid\step\ToFloat;



final class ToFloatTest
extends TestCase
{

	private function _produceStep() {
		$next = new Noop();

		return new ToFloat($next);
	}


	public function testGetId() {
		$step = $this->_produceStep();

		$this->assertEquals('float', $step->getId());
	}

	public function testValidate() {
		$step = $this->_produceStep();

		$step->validate('0.0');
		$this->assertTrue($step->wasValidated());
		$this->assertTrue($step->isValid());
		$this->assertInternalType('float', $step->getResult());
		$this->assertEquals(0.0, $step->getResult());

		$step->validate(0);
		$this->assertInternalType('float', $step->getResult());
		$this->assertEquals(0.0, $step->getResult());

		$step->validate(false);
		$this->assertInternalType('float', $step->getResult());
		$this->assertEquals(0.0, $step->getResult());

		$step->validate([]);
		$this->assertFalse($step->isValid());

		$step->validate(new \stdClass());
		$this->assertFalse($step->isValid());
	}
}
