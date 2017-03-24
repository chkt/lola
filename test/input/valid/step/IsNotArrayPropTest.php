<?php

namespace test\input\valid\step;

use PHPUnit\Framework\TestCase;

use lola\input\valid\ValidationException;
use lola\input\valid\step\IsNotArrayProp;



final class IsNotArrayPropTest
extends TestCase
{

	private function _produceStep(string $prop) {
		return new IsNotArrayProp($prop);
	}

	private function _produceException(string $message = '', int $code = 0) {
		return new ValidationException($message, $code);
	}


	public function testValidate() {
		$step = $this->_produceStep('foo');

		$step->validate('foo');
		$this->assertTrue($step->wasValidated());
		$this->assertFalse($step->isValid());
		$this->assertEquals('foo', $step->getResult());
		$this->assertEquals($this->_produceException('isNotArrayProp.foo.noArray', 1), $step->getError());

		$step->validate(['foo' => 1]);
		$this->assertFalse($step->isValid());
		$this->assertEquals($this->_produceException('isNotArrayProp.foo.exists', 2), $step->getError());

		$step->validate(['bar' => 1]);
		$this->assertTrue($step->isValid());
		$this->assertEquals(['bar' => 1], $step->getResult());
	}
}
