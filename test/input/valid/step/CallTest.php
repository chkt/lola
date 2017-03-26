<?php

namespace test\input\valid\step;

use PHPUnit\Framework\TestCase;

use lola\input\valid\ValidationException;
use lola\input\valid\step\Call;



final class CallTest
extends TestCase
{

	private function _produceStep(callable $fn = null) {
		if (is_null($fn)) $fn = function() {};

		return new Call($fn);
	}

	private function _produceException(string $message = '', int $code = 0) {
		return new ValidationException($message, $code);
	}


	public function testGetId() {
		$step = $this->_produceStep();

		$this->assertEquals('call', $step->getId());
	}

	public function testValidate() {
		$step = $this->_produceStep(function($source) {
			if (!is_string($source)) throw new ValidationException('NOSTRING', 1);

			if (!is_numeric($source)) throw new \ErrorException('NOTNUM', 2);

			return (float) $source;
		});

		$step->validate('foo');
		$this->assertTrue($step->wasValidated());
		$this->assertFalse($step->isValid());
		$this->assertEquals('foo', $step->getResult());
		$this->assertEquals($this->_produceException('call.error', 1), $step->getError());

		$step->validate(1);
		$this->assertFalse($step->isValid());
		$this->assertEquals(1, $step->getResult());
		$this->assertEquals($this->_produceException('NOSTRING', 1), $step->getError());

		$step->validate('1');
		$this->assertTrue($step->isValid());
		$this->assertEquals(1, $step->getResult());
	}
}
