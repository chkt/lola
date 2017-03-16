<?php

namespace test\input\valid\step;

use PHPUnit\Framework\TestCase;

use lola\input\valid\AValidationTransform;
use lola\input\valid\step\ArrayPropTouch;



final class ArrayPropTouchTest
extends TestCase
{

	private function _mockTest(callable $test) {
		$ins = $this
			->getMockBuilder(AValidationTransform::class)
			->getMockForAbstractClass();

		$ins
			->expects($this->any())
			->method('_validate')
			->with($this->anything())
			->willReturnCallback($test);

		return $ins;
	}

	private function _produceTransform(string $prop, AValidationTransform $next = null) {
		return new ArrayPropTouch($prop, $next);
	}


	public function testGetId() {
		$step = $this->_produceTransform('foo');

		$this->assertEquals('arrayPropTouch.foo', $step->getId());
	}

	public function testValidate() {
		$step = $this->_produceTransform('foo');

		$step->validate('foo');
		$this->assertTrue($step->wasValidated());
		$this->assertFalse($step->isValid());
		$this->assertEquals('foo', $step->getResult());

		$step->validate([]);
		$this->assertTrue($step->wasValidated());
		$this->assertTrue($step->isValid());
		$this->assertEquals(['foo' => null], $step->getResult());
	}
}
