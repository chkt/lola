<?php

namespace test\input\valid\step;

use PHPUnit\Framework\TestCase;

use lola\input\valid\step\Noop;



final class NoopTest
extends TestCase
{

	private function _produceStep() {
		return new Noop();
	}


	public function testGetId() {
		$step = $this->_produceStep();

		$this->assertEquals('noop', $step->getId());
	}

	public function testValidate() {
		$step = $this->_produceStep();

		$step->validate('foo');

		$this->assertTrue($step->isValid());
		$this->assertEquals('foo', $step->getSource());
		$this->assertEquals('foo', $step->getResult());
	}
}
