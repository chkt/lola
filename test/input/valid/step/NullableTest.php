<?php

namespace test\input\valid\step;

use PHPUnit\Framework\TestCase;

use lola\input\valid\ValidationException;
use lola\input\valid\step\Nullable;
use lola\input\valid\step\IsStringNonEmpty;



final class NullableTest
extends TestCase
{

	private function _produceStep() {
		$next = new IsStringNonEmpty();

		return new Nullable($next);
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
		$this->assertFalse($step->wasRecovered());
		$this->assertEquals('foo', $step->getResult());

		$step->validate('');
		$this->assertTrue($step->wasValidated());
		$this->assertTrue($step->isValid());
		$this->assertTrue($step->wasRecovered());
		$this->assertNull($step->getResult());
	}
}
