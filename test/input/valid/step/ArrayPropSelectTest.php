<?php

namespace test\input\valid\step;

use PHPUnit\Framework\TestCase;

use lola\input\valid\AValidationTransform;
use lola\input\valid\ValidationException;
use lola\input\valid\step\ArrayPropSelect;



final class ArrayPropSelectTest
extends TestCase
{

	private function _mockTest(callable $test) : AValidationTransform {
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

	private function _produceStep(string $prop, AValidationTransform $next) : ArrayPropSelect {
		return new ArrayPropSelect($prop, $next);
	}

	public function testGetId() {
		$step = $this->_produceStep('foo', $this->_mockTest(function($value) {
			return $value;
		}));

		$this->assertEquals('arrayPropSelect.foo', $step->getId());
	}

	public function testValidate() {
		$next = $this->_mockTest(function($source) {
			if (!is_string($source)) throw new ValidationException('NOSTRING', 1);

			if (!is_numeric($source)) throw new ValidationException('NOTNUM', 2);

			return (int) $source;
		});
		$step = $this->_produceStep('foo', $next);

		$step->validate('foo');
		$this->assertTrue($step->wasValidated());
		$this->assertFalse($step->isValid());
		$this->assertEquals('foo', $step->getResult());

		$step->validate(['foo' => 'bar']);
		$this->assertTrue($step->wasValidated());
		$this->assertFalse($step->isValid());
		$this->assertEquals(['foo' => 'bar'], $step->getResult());

		$step->validate(['foo' => '1']);
		$this->assertTrue($step->wasValidated());
		$this->assertTrue($step->isValid());
		$this->assertEquals(['foo' => 1], $step->getResult());
	}
}
