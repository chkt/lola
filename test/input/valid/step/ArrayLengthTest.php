<?php

namespace test\input\valid\step;

use PHPUnit\Framework\TestCase;

use lola\input\valid\AValidationTransform;
use lola\input\valid\ValidationException;
use lola\input\valid\step\ArrayLength;



final class ArrayLengthTest
extends TestCase
{

	private function _produceTransform(int $length, AValidationTransform $next = null) {
		return new ArrayLength($length, $next);
	}

	private function _produceException(string $message = '', int $code = 0) {
		return new ValidationException($message, $code);
	}



	public function testGetId() {
		$step = $this->_produceTransform(0);

		$this->assertEquals('arrayLength.0', $step->getId());
	}

	public function testValidate() {
		$step = $this->_produceTransform(3);

		$step->validate('foo');
		$this->assertTrue($step->wasValidated());
		$this->assertFalse($step->isValid());
		$this->assertEquals('foo', $step->getResult());
		$this->assertEquals($this->_produceException('arrayLength.3.noarray', 1), $step->getError());

		$step->validate([]);
		$this->assertFalse($step->isValid());
		$this->assertEquals([], $step->getResult());
		$this->assertEquals($this->_produceException('arrayLength.3.noteq', 2), $step->getError());

		$step->validate(['foo', 'bar', 'baz']);
		$this->assertTrue($step->isValid());
		$this->assertEquals(['foo','bar','baz'], $step->getResult());
	}
}
