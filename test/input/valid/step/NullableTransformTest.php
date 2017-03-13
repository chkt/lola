<?php

namespace test\input\valid\step;

use PHPUnit\Framework\TestCase;

use lola\input\valid\ValidationException;
use lola\input\valid\step\NullableTransform;
use lola\input\valid\step\NoopValidationStep;



final class NullableTransformTest
extends TestCase
{

	private function _produceStep() {
		$next = new NoopValidationStep();

		return new NullableTransform($next);
	}

	private function _produceException(string $message = '', int $code = 0) {
		return new ValidationException($message, $code);
	}


	public function testGetId() {
		$step = $this->_produceStep();

		$this->assertEquals('nullable', $step->getId());
	}

	public function testValidate() {
		$step = $this->_produceStep();

		$step->validate('foo');
		$this->assertTrue($step->wasValidated());
		$this->assertTrue($step->isValid());
		$this->assertFalse($step->wasTransformed());
		$this->assertFalse($step->wasRecovered());
		$this->assertEquals('foo', $step->getResult());
	}

	public function testTransform() {
		$step = $this->_produceStep();

		$step->validate('foo');
		$this->assertTrue($step->wasValidated());
		$this->assertFalse($step->wasTransformed());
		$this->assertFalse($step->wasRecovered());
		$this->assertEquals('foo', $step->getResult());

		$step->transform('foo');
		$this->assertTrue($step->wasTransformed());
		$this->assertFalse($step->wasRecovered());
		$this->assertEquals('foo', $step->getTransformedResult());
	}

	public function testRecover() {
		$step = $this->_produceStep();
		$ex = $this->_produceException('bang', 42);

		$step
			->validate('foo')
			->recover($ex);

		$this->assertTrue($step->wasValidated());
		$this->assertTrue($step->isValid());
		$this->assertFalse($step->wasTransformed());
		$this->assertTrue($step->wasRecovered());
		$this->assertNull($step->getRecoveredResult());
	}
}
