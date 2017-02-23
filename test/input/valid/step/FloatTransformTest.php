<?php

namespace test\input\valid\step;

use PHPUnit\Framework\TestCase;

use lola\input\valid\step\NoopValidationStep;
use lola\input\valid\step\FloatTransform;



final class FloatTransformTest
extends TestCase
{

	private function _produceStep() {
		$next = new NoopValidationStep();

		return new FloatTransform($next);
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

		$step->validate(0);
		$this->assertInternalType('float', $step->getResult());

		$step->validate(false);
		$this->assertInternalType('float', $step->getResult());

		$step->validate([]);
		$this->assertFalse($step->isValid());

		$step->validate(new \stdClass());
		$this->assertFalse($step->isValid());
	}

	public function testTransform() {
		$step = $this->_produceStep();

		$step->validate(0.0);

		$step->transform(0.0);
		$this->assertEquals(0.0, $step->getTransformedResult());
		$step->transform(1.1);
		$this->assertEquals(1.1, $step->getTransformedResult(1.1));
		$step->transform(-1.0e-8);
		$this->assertEquals(-1.0e-8, $step->getTransformedResult());
	}
}
