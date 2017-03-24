<?php

namespace test\input\valid\step;

use PHPUnit\Framework\TestCase;

use lola\input\valid\IValidationTransform;
use lola\input\valid\AValidationTransform;
use lola\input\valid\ValidationException;
use lola\input\valid\step\ArrayPropAny;



final class ArrayPropAnyTest
extends TestCase
{

	private function _mockTest(callable $fn) : AValidationTransform {
		$ins = $this
			->getMockBuilder(AValidationTransform::class)
			->getMockForAbstractClass();

		$ins
			->expects($this->any())
			->method('_validate')
			->with($this->anything())
			->willReturnCallback($fn);

		return $ins;
	}


	private function _produceStep(IValidationTransform $next) {
		return new ArrayPropAny($next);
	}

	private function _produceException(string $message = '', int $code = 0) {
		return new ValidationException($message, $code);
	}


	public function testValidate() {
		$next = $this->_mockTest(function ($source) {
			if (!is_int($source)) throw new ValidationException('NOINT', 1);

			if ($source < 0) throw new ValidationException('NEGATIVE', 2);

			return (string) $source;
		});

		$step = $this->_produceStep($next);

		$step->validate('foo');
		$this->assertTrue($step->wasValidated());
		$this->assertFalse($step->isValid());
		$this->assertEquals('foo', $step->getResult());
		$this->assertEquals($this->_produceException('arrayPropAny.noArray', 1), $step->getError());

		$step->validate([
			'foo' => 'bar',
			'baz' => 'quux'
		]);
		$this->assertFalse($step->isValid());
		$this->assertEquals([
			'foo' => 'bar',
			'baz' => 'quux'
		], $step->getResult());
		$this->assertEquals($this->_produceException('arrayPropAny.notIndexed', 2), $step->getError());

		$step->validate(['foo', 'bar']);
		$this->assertFalse($step->isValid());
		$this->assertEquals($this->_produceException('arrayPropAny.noProp', 3), $step->getError());

		$step->validate(['foo', 1]);
		$this->assertTrue($step->isValid());
		$this->assertEquals(['foo', '1'], $step->getResult());
		$this->assertEquals('1', $step->useTerminalStep()->getResult());
	}
}
