<?php

namespace test\input\valid\step;

use PHPUnit\Framework\TestCase;

use lola\input\valid\step\NoopValidationStep;
use lola\input\valid\step\ArrayPropTransform;



final class ArrayPropTransformTest
extends TestCase
{

	private function _produceStep(string $prop) : ArrayPropTransform {
		$next = new NoopValidationStep();

		return new ArrayPropTransform($prop, $next);
	}

	public function testGetId() {
		$step = $this->_produceStep('foo');

		$this->assertEquals('arrayProp.foo', $step->getId());
	}

	public function testValidate() {
		$step = $this->_produceStep('foo');

		$step->validate([
			'foo' => 'bar'
		]);

		$this->assertTrue($step->wasValidated());
		$this->assertTrue($step->isValid());
		$this->assertEquals('bar', $step->getResult());

		$step->transform('BAR');

		$this->assertTrue($step->wasTransformed());
		$this->assertEquals([ 'foo' => 'BAR' ], $step->getTransformedResult());
	}
}
